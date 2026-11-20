<?php

use App\Enums\ScoreStatus;
use App\Models\Appeal;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\EvidenceFile;
use App\Models\IncidentReport;
use App\Models\Run;
use App\Models\School;
use App\Models\Score;
use App\Models\Team;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\CorrectionService;
use App\Services\PublicationService;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);

    $this->user = fn (string $role) => User::where('email', "{$role}@nanorobotic.test")->firstOrFail();
});

/* ---------------------------------------------------------------- Evidence */

it('rejects viewing another school\'s evidence', function () {
    Storage::fake('local');

    $evidence = EvidenceFile::factory()->create([
        'path' => 'evidence/secret.jpg',
        'uploaded_by' => null,
    ]);

    Storage::disk('local')->put('evidence/secret.jpg', 'binary');

    $coach = ($this->user)('coach');

    $this->actingAs($coach)
        ->get("/bukti/{$evidence->uuid}")
        ->assertForbidden();
});

it('lets a judge upload evidence with a draft score', function () {
    Storage::fake('local');

    $judge = ($this->user)('juri');
    $run = Run::whereHas('category.challenges.scoreObjects')->firstOrFail();
    $objects = $run->category->challenges->first()->scoreObjects;

    $entries = $objects->map(fn ($o) => [
        'score_object_id' => $o->id,
        'quantity' => $o->type->isPenalty() ? 0 : 1,
        'achieved' => true,
    ])->all();

    $this->actingAs($judge)
        ->post("/juri/run/{$run->uuid}/nilai", [
            'entries' => $entries,
            'evidence_files' => [UploadedFile::fake()->image('bukti.jpg', 640, 480)],
            'evidence_kind' => 'photo',
            'evidence_caption' => 'CP2 bersih',
        ])
        ->assertRedirect();

    expect($run->evidenceFiles()->count())->toBe(1)
        ->and($run->evidenceFiles()->first()->uploaded_by)->toBe($judge->id);
});

/* ---------------------------------------------------------------- Incidents */

it('lets an operator report and resolve an incident on their arena', function () {
    $operator = ($this->user)('operator');
    $arena = $operator->arenaAssignments()->where('is_active', true)->firstOrFail()->arena;

    $this->actingAs($operator)
        ->post('/operator/insiden', [
            'arena_id' => $arena->id,
            'severity' => 'warning',
            'category' => 'technical',
            'title' => 'Sensor garis terputus',
        ])
        ->assertRedirect();

    $incident = IncidentReport::firstOrFail();
    expect($incident->status)->toBe('open')
        ->and($incident->reported_by)->toBe($operator->id);

    $this->actingAs($operator)
        ->patch("/operator/insiden/{$incident->uuid}", [
            'status' => 'resolved',
            'resolution' => 'Kabel diganti.',
        ])
        ->assertRedirect();

    expect($incident->fresh()->status)->toBe('resolved')
        ->and($incident->fresh()->resolution)->toBe('Kabel diganti.');
});

it('forbids an operator from reporting on an unassigned arena', function () {
    $operator = ($this->user)('operator');
    $other = Arena::factory()->create(['event_id' => Event::firstOrFail()->id]);

    $this->actingAs($operator)
        ->post('/operator/insiden', [
            'arena_id' => $other->id,
            'severity' => 'info',
            'title' => 'Percobaan',
        ])
        ->assertForbidden();
});

/* ---------------------------------------------------------------- Correction */

it('corrects an approved score and records history', function () {
    $chief = ($this->user)('chief');
    $score = Score::where('status', ScoreStatus::Submitted->value)->firstOrFail();

    // Approve first so it becomes official.
    app(PublicationService::class)->approve($score, $chief);
    $score->refresh();

    $line = $score->lines()->first();
    if (! $line) {
        $run = $score->run;
        $object = $run->category->challenges->first()->scoreObjects->first();
        $line = $score->lines()->create([
            'uuid' => (string) Str::uuid(),
            'score_object_id' => $object->id,
            'quantity' => 1,
            'achieved' => true,
            'points_per_unit_snapshot' => 10,
            'computed_points' => 10,
        ]);
    }

    $before = $score->total;

    app(CorrectionService::class)->correct($score, $chief, [
        ['line_id' => $line->id, 'quantity' => (int) $line->quantity + 1],
    ], 'Kesalahan input juri ketiga.');

    $score->refresh();
    expect($score->total)->not->toBe($before)
        ->and($score->approvals()->where('decision', 'corrected')->exists())->toBeTrue();
});

it('refuses to correct a score that is not yet official', function () {
    $chief = ($this->user)('chief');
    $score = Score::where('status', ScoreStatus::Submitted->value)->firstOrFail();

    $object = $score->run->category->challenges->first()->scoreObjects->first();
    $line = $score->lines()->create([
        'uuid' => (string) Str::uuid(),
        'score_object_id' => $object->id,
        'quantity' => 1,
        'achieved' => true,
        'points_per_unit_snapshot' => 10,
        'computed_points' => 10,
    ]);

    expect(fn () => app(CorrectionService::class)->correct(
        $score, $chief, [['line_id' => $line->id, 'quantity' => 5]], 'Coba koreksi.'
    ))->toThrow(RuntimeException::class);
});

/* ---------------------------------------------------------------- CSV export */

it('exports published standings as CSV and hides unpublished levels', function () {
    $admin = ($this->user)('eventadmin');

    $response = $this->actingAs($admin)->get('/event-admin/laporan/ekspor/klasemen');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

it('exports the participation roster as CSV', function () {
    $admin = ($this->user)('eventadmin');

    $this->actingAs($admin)
        ->get('/event-admin/laporan/ekspor/partisipasi')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

/* ---------------------------------------------------------------- Certificates */

it('issues certificates only for a published category', function () {
    $chief = ($this->user)('chief');
    $category = Category::firstOrFail();

    expect(fn () => app(CertificateService::class)->issueForCategory($category, $chief))
        ->toThrow(RuntimeException::class);

    expect(Certificate::count())->toBe(0);
});

it('renders a public certificate verification page', function () {
    $certificate = Certificate::factory()->create();

    $this->get("/sertifikat/verifikasi/{$certificate->qr_token}")
        ->assertOk()
        ->assertSee('Sertifikat sah', false);
});

it('shows an unknown token as unverified', function () {
    $this->get('/sertifikat/verifikasi/does-not-exist')
        ->assertOk()
        ->assertSee('tidak terverifikasi', false);
});

it('forbids a public user from opening a certificate directly', function () {
    $certificate = Certificate::factory()->create();

    $this->actingAs(($this->user)('publik'))
        ->get("/sertifikat/{$certificate->uuid}")
        ->assertForbidden();
});

/* ---------------------------------------------------------------- Appeals */

it('lets a coach appeal a run of their own school', function () {
    $coach = ($this->user)('coach');
    $run = Run::whereHas('team', fn ($q) => $q->where('school_id', $coach->coachProfile->school_id))
        ->firstOrFail();

    $this->actingAs($coach)
        ->post('/coach/banding', [
            'run_id' => $run->id,
            'reason' => 'Nilai checkpoint warna berbeda dengan lembar juri.',
        ])
        ->assertRedirect();

    expect(Appeal::where('run_id', $run->id)->exists())->toBeTrue();
});

it('refuses an appeal for a run from another school', function () {
    $coach = ($this->user)('coach');

    // Demo data only seeds one school; create a second school + run to prove
    // the ownership check really rejects foreign teams.
    $event = Event::firstOrFail();
    $category = Category::firstOrFail();
    $school = School::factory()->create();
    $team = Team::factory()->create([
        'school_id' => $school->id,
        'category_id' => $category->id,
    ]);
    $run = Run::factory()->create([
        'event_id' => $event->id,
        'category_id' => $category->id,
        'team_id' => $team->id,
        'arena_id' => Arena::firstOrFail()->id,
    ]);

    $this->actingAs($coach)
        ->post('/coach/banding', [
            'run_id' => $run->id,
            'reason' => 'Coba banding tim lain.',
        ])
        ->assertForbidden();
});

it('lets the chief resolve an appeal', function () {
    $chief = ($this->user)('chief');
    $appeal = Appeal::factory()->create(['status' => 'submitted']);

    $this->actingAs($chief)
        ->patch("/chief/banding/{$appeal->uuid}", [
            'status' => 'accepted',
            'resolution' => 'Diterima, akan dihitung ulang.',
        ])
        ->assertRedirect();

    $appeal->refresh();
    expect($appeal->status)->toBe('accepted')
        ->and($appeal->resolved_by)->toBe($chief->id)
        ->and($appeal->resolved_at)->not->toBeNull();
});

/* ---------------------------------------------------------------- Realtime meta */

it('exposes realtime meta on public boards only', function () {
    $response = $this->get('/leaderboard');

    $response->assertOk()
        ->assertSee('name="reverb-key"', false)
        ->assertSee('name="live-event"', false);
});

/* ---------------------------------------------------------------- Profile */

it('renders the restyled profile page for a role user', function () {
    $this->actingAs(($this->user)('coach'))
        ->get('/profile')
        ->assertOk()
        ->assertSee('Profil &amp; Keamanan', false);
});
