<?php

use App\Enums\CategoryStatus;
use App\Enums\RegistrationStatus;
use App\Models\Arena;
use App\Models\ArenaAssignment;
use App\Models\Category;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);

    $this->user = fn (string $role) => User::where('email', "{$role}@nanorobotic.test")->firstOrFail();
});

/** Open the demo event for registration and return its SD category. */
function openCategory(): Category
{
    $event = Event::firstOrFail();
    $event->update(['status' => 'running']);

    $category = Category::where('event_id', $event->id)->orderBy('level')->firstOrFail();
    $category->update(['status' => CategoryStatus::Registration]);

    return $category;
}

/* ------------------------------------------------------- Coach team signup */

it('lets a coach open the team registration form', function () {
    openCategory();

    $this->actingAs(($this->user)('coach'))
        ->get('/coach/tim/baru')
        ->assertOk()
        ->assertSee('Identitas tim')
        ->assertSee('Anggota tim');
});

it('lets a coach register a team with participants', function () {
    $category = openCategory();
    $coach = ($this->user)('coach');

    $this->actingAs($coach)->post('/coach/tim', [
        'category_id' => $category->id,
        'name' => 'Tim Uji Coba',
        'short_name' => 'UJI',
        'members' => [
            ['name' => 'Ani', 'nisn' => '0012345678', 'grade' => '8A', 'role' => 'leader'],
            ['name' => 'Budi', 'nisn' => '0012345679', 'grade' => '8A', 'role' => 'member'],
            ['name' => '', 'nisn' => '', 'grade' => '', 'role' => 'member'],
        ],
    ])->assertRedirect();

    $team = Team::where('name', 'Tim Uji Coba')->firstOrFail();

    expect($team->school_id)->toBe($coach->coachProfile->school_id)
        ->and($team->status)->toBe(RegistrationStatus::Draft)
        ->and($team->participants()->count())->toBe(2)
        ->and($team->registrations()->count())->toBe(1);
});

it('rejects a team whose member count violates the category rules', function () {
    $category = openCategory();
    $category->update(['min_team_members' => 2, 'max_team_members' => 3]);

    $this->actingAs(($this->user)('coach'))->post('/coach/tim', [
        'category_id' => $category->id,
        'name' => 'Tim Terlalu Kecil',
        'members' => [['name' => 'Ani', 'role' => 'leader']],
    ])->assertSessionHasErrors('members');

    expect(Team::where('name', 'Tim Terlalu Kecil')->exists())->toBeFalse();
});

it('rejects a duplicate NISN across teams', function () {
    $category = openCategory();
    $existing = Participant::whereNotNull('nisn_hash')->firstOrFail();

    // Re-use an existing participant's NISN by reading the hash and posting a
    // fresh NISN that collides is not possible without the plain value, so we
    // register the same team twice with the same NISN instead.
    $payload = fn (string $team) => [
        'category_id' => $category->id,
        'name' => $team,
        'members' => [['name' => 'Ani', 'nisn' => '0099999999', 'role' => 'leader']],
    ];

    $coach = ($this->user)('coach');

    $this->actingAs($coach)->post('/coach/tim', $payload('Tim Satu'))->assertRedirect();
    $this->actingAs($coach)->post('/coach/tim', $payload('Tim Dua'))->assertSessionHasErrors('members');

    expect($existing->nisn_hash)->not->toBe(Participant::hashNisn('0099999999'))
        ->and(Team::where('name', 'Tim Dua')->exists())->toBeFalse();
});

it('submits a coach registration for verification with consent', function () {
    openCategory();
    $coach = ($this->user)('coach');
    $team = Team::where('school_id', $coach->coachProfile->school_id)->firstOrFail();

    // Force the team back into an editable state.
    $team->registrations()->update(['status' => RegistrationStatus::Draft->value]);

    $this->actingAs($coach)
        ->post("/coach/tim/{$team->uuid}/ajukan", [
            'consent_parent' => '1',
            'consent_media' => '1',
        ])
        ->assertRedirect();

    $registration = $team->registrations()->latest()->first();

    expect($registration->status)->toBe(RegistrationStatus::Submitted)
        ->and($registration->consent_parent)->toBeTrue()
        ->and($registration->consent_media)->toBeTrue();
});

it('blocks a coach from editing another school\'s team', function () {
    $coach = ($this->user)('coach');
    $foreign = Team::where('school_id', '!=', $coach->coachProfile->school_id)->firstOrFail();

    $this->actingAs($coach)->get("/coach/tim/{$foreign->uuid}")->assertForbidden();
    $this->actingAs($coach)->post("/coach/tim/{$foreign->uuid}/anggota", [
        'name' => 'Penyusup',
    ])->assertForbidden();
});

/* ------------------------------------------------ Event admin assignments */

it('lets an event admin assign an operator to an arena', function () {
    $arena = Arena::firstOrFail();
    $operator = ($this->user)('operator');

    ArenaAssignment::where('user_id', $operator->id)->delete();

    $this->actingAs(($this->user)('eventadmin'))
        ->post('/event-admin/staff', [
            'arena_id' => $arena->id,
            'user_id' => $operator->id,
            'role' => 'operator',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(ArenaAssignment::where([
        'arena_id' => $arena->id,
        'user_id' => $operator->id,
        'role' => 'operator',
    ])->exists())->toBeTrue();
});

it('refuses to assign a user who lacks the chosen role', function () {
    $arena = Arena::firstOrFail();
    $coach = ($this->user)('coach');

    $this->actingAs(($this->user)('eventadmin'))
        ->post('/event-admin/staff', [
            'arena_id' => $arena->id,
            'user_id' => $coach->id,
            'role' => 'judge',
        ])
        ->assertStatus(422);
});

it('toggles and removes an arena assignment', function () {
    $assignment = ArenaAssignment::firstOrFail();
    $admin = ($this->user)('eventadmin');

    $this->actingAs($admin)
        ->patch("/event-admin/staff/{$assignment->uuid}/toggle")
        ->assertRedirect();

    expect($assignment->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->delete("/event-admin/staff/{$assignment->uuid}")
        ->assertRedirect();

    expect(ArenaAssignment::find($assignment->id))->toBeNull();
});

/* ------------------------------------------------ Email verification gate */

it('sends unverified users to the verification notice when the setting is on', function () {
    Setting::put('require_email_verification', true);

    $user = User::factory()->unverified()->create();
    $user->syncRoles(['public']);

    $this->actingAs($user)->get('/publik/dasbor')->assertRedirect('/verify-email');
});

it('lets unverified users through when the setting is off', function () {
    Setting::put('require_email_verification', false);

    $user = User::factory()->unverified()->create();
    $user->syncRoles(['public']);

    $this->actingAs($user)->get('/publik/dasbor')->assertOk();
});

it('keeps the profile page reachable for unverified users', function () {
    Setting::put('require_email_verification', true);

    $user = User::factory()->unverified()->create();
    $user->syncRoles(['public']);

    $this->actingAs($user)->get('/profile')->assertOk();
});

it('marks a new registration unverified when verification is required', function () {
    Setting::put('require_email_verification', true);

    $this->post('/register', [
        'name' => 'Rani',
        'school' => 'SMP Baru',
        'email' => 'rani@baru.test',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertRedirect();

    expect(User::where('email', 'rani@baru.test')->firstOrFail()->email_verified_at)->toBeNull();
});

/* ------------------------------------------------ Account menu everywhere */

it('shows profile and logout actions on every role layout', function () {
    $pages = [
        'coach' => '/coach/dashboard',
        'juri' => '/juri/dashboard',
        'operator' => '/operator/dashboard',
        'chief' => '/chief/dashboard',
        'eventadmin' => '/event-admin/dashboard',
        'admin' => '/super-admin/dashboard',
    ];

    foreach ($pages as $role => $url) {
        $this->actingAs(($this->user)($role))
            ->get($url)
            ->assertOk()
            ->assertSee('rr-account', false)
            ->assertSee('Profil &amp; keamanan', false)
            ->assertSee('Keluar', false);
    }
});
