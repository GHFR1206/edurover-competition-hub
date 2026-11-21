<?php

use App\Enums\RunStatus;
use App\Http\Middleware\EnsureArenaDevice;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Event;
use App\Models\Run;
use App\Models\Team;
use App\Models\User;
use App\Services\StandingsService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->event = Event::factory()->create(['status' => 'running']);
    $this->category = Category::factory()->create(['event_id' => $this->event->id]);
    $this->arena = Arena::factory()->create(['event_id' => $this->event->id]);
    $this->team = Team::factory()->create(['category_id' => $this->category->id]);

    $this->run = Run::factory()->create([
        'event_id' => $this->event->id,
        'category_id' => $this->category->id,
        'arena_id' => $this->arena->id,
        'team_id' => $this->team->id,
        'status' => RunStatus::Scheduled,
    ]);

    // Service account owning the arena's device token.
    $this->device = User::factory()->create(['is_active' => true]);
    $this->arena->update(['api_user_id' => $this->device->id]);

    $this->token = $this->device->createToken(
        $this->arena->code,
        [EnsureArenaDevice::ABILITY],
    )->plainTextToken;

    $this->api = fn (string $method, string $uri, array $payload = []) => $this
        ->withHeader('Authorization', 'Bearer '.$this->token)
        ->json($method, '/api/v1'.$uri, $payload);
});

/* ------------------------------------------------------------------ auth */

it('rejects requests without a token', function () {
    $this->getJson('/api/v1/arena/current-run')->assertUnauthorized();
});

it('rejects a token without the arena timing ability', function () {
    $plain = $this->device->createToken('wrong', ['something:else'])->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$plain)
        ->getJson('/api/v1/arena/current-run')
        ->assertForbidden();
});

it('rejects a token whose account is not linked to an arena', function () {
    $stranger = User::factory()->create();
    $plain = $stranger->createToken('orphan', [EnsureArenaDevice::ABILITY])->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$plain)
        ->getJson('/api/v1/arena/current-run')
        ->assertForbidden();
});

it('rejects a revoked token', function () {
    $this->device->tokens()->delete();

    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/v1/arena/current-run')
        ->assertUnauthorized();
});

it('does not accept a browser session as an arena device', function () {
    // A logged-in staff member must not be able to act as a device.
    $staff = User::factory()->create();

    $this->actingAs($staff)
        ->getJson('/api/v1/arena/current-run')
        ->assertForbidden();
});

/* ----------------------------------------------------------- arena scope */

it('never lets one arena touch another arena run', function () {
    $otherArena = Arena::factory()->create(['event_id' => $this->event->id]);
    $otherRun = Run::factory()->create([
        'event_id' => $this->event->id,
        'category_id' => $this->category->id,
        'arena_id' => $otherArena->id,
        'team_id' => $this->team->id,
        'attempt_no' => 2,
    ]);

    ($this->api)('post', "/runs/{$otherRun->uuid}/start", ['started_ms' => 0])
        ->assertNotFound();

    ($this->api)('post', "/runs/{$otherRun->uuid}/finish", ['finished_ms' => 10_000])
        ->assertNotFound();

    expect($otherRun->fresh()->official_time_ms)->toBeNull();
});

it('lists only its own arena current run', function () {
    $response = ($this->api)('get', '/arena/current-run')->assertOk();

    expect($response->json('run.id'))->toBe($this->run->uuid)
        ->and($response->json('arena.code'))->toBe($this->arena->code);
});

/* ------------------------------------------------------------- timing flow */

it('records start, laps and finish and settles the run', function () {
    ($this->api)('post', "/runs/{$this->run->uuid}/start", ['started_ms' => 0])
        ->assertOk()
        ->assertJson(['accepted' => true]);

    ($this->api)('post', "/runs/{$this->run->uuid}/laps", [
        'laps' => [
            ['lap_no' => 1, 'lap_ms' => 12_000],
            ['lap_no' => 2, 'lap_ms' => 13_500],
        ],
    ])->assertOk()->assertJson(['laps_stored' => 2]);

    ($this->api)('post', "/runs/{$this->run->uuid}/finish", ['finished_ms' => 26_000])
        ->assertOk()
        ->assertJsonPath('run.official_time_ms', 26_000)
        ->assertJsonPath('run.lap_count', 2);

    $run = $this->run->fresh();

    expect($run->status)->toBe(RunStatus::Finished)
        ->and($run->official_time_ms)->toBe(26_000)
        ->and($run->duration_seconds)->toBe(26)
        ->and($run->timing_source)->toBe('api')
        ->and($run->laps)->toHaveCount(2)
        ->and($run->laps->last()->cumulative_ms)->toBe(25_500);
});

it('accepts a one-shot timing payload', function () {
    ($this->api)('post', "/runs/{$this->run->uuid}/timing", [
        'started_ms' => 0,
        'finished_ms' => 41_250,
        'laps' => [['lap_ms' => 20_000], ['lap_ms' => 21_250]],
    ])->assertOk()
        ->assertJsonPath('run.official_time_ms', 41_250)
        ->assertJsonPath('run.lap_count', 2);
});

it('is idempotent when the device replays its buffer', function () {
    ($this->api)('post', "/runs/{$this->run->uuid}/finish", ['finished_ms' => 30_000])
        ->assertOk()
        ->assertJsonPath('first_seen', true);

    // Same payload again: the official result must not change.
    ($this->api)('post', "/runs/{$this->run->uuid}/finish", ['finished_ms' => 30_000])
        ->assertOk()
        ->assertJsonPath('first_seen', false)
        ->assertJsonPath('run.official_time_ms', 30_000);

    // A conflicting later reading never overwrites the first one.
    ($this->api)('post', "/runs/{$this->run->uuid}/finish", ['finished_ms' => 12_000])
        ->assertOk();

    expect($this->run->fresh()->official_time_ms)->toBe(30_000);
});

it('keeps lap numbers unique on retry', function () {
    $payload = ['laps' => [['lap_no' => 1, 'lap_ms' => 9_000]]];

    ($this->api)('post', "/runs/{$this->run->uuid}/laps", $payload)->assertOk();
    ($this->api)('post', "/runs/{$this->run->uuid}/laps", $payload)->assertOk();

    expect($this->run->fresh()->laps)->toHaveCount(1);
});

it('rejects an invalid timing payload', function () {
    ($this->api)('post', "/runs/{$this->run->uuid}/finish", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('finished_ms');

    ($this->api)('post', "/runs/{$this->run->uuid}/finish", ['finished_ms' => 0])
        ->assertUnprocessable();

    ($this->api)('post', "/runs/{$this->run->uuid}/laps", ['laps' => []])
        ->assertUnprocessable();

    ($this->api)('post', "/runs/{$this->run->uuid}/laps", ['laps' => [['lap_ms' => -5]]])
        ->assertUnprocessable();
});

it('rejects a finish that is not after the recorded start', function () {
    ($this->api)('post', "/runs/{$this->run->uuid}/start", ['started_ms' => 5_000])->assertOk();

    ($this->api)('post', "/runs/{$this->run->uuid}/finish", ['finished_ms' => 4_000])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Waktu finish harus lebih besar dari waktu start.');
});

it('refuses timing for a disqualified run', function () {
    $this->run->update(['status' => RunStatus::Dq]);

    ($this->api)('post', "/runs/{$this->run->uuid}/finish", ['finished_ms' => 30_000])
        ->assertUnprocessable();
});

it('exposes the stored timing for reconciliation', function () {
    ($this->api)('post', "/runs/{$this->run->uuid}/finish", [
        'finished_ms' => 22_500,
        'laps' => [['lap_no' => 1, 'lap_ms' => 22_500]],
    ])->assertOk();

    ($this->api)('get', "/runs/{$this->run->uuid}/timing")
        ->assertOk()
        ->assertJsonPath('run.official_time_ms', 22_500)
        ->assertJsonPath('run.laps.0.lap_ms', 22_500);
});

it('writes an audit trail entry for accepted timing', function () {
    ($this->api)('post', "/runs/{$this->run->uuid}/finish", ['finished_ms' => 18_000])->assertOk();

    expect($this->run->fresh()->auditLogs()->where('action', 'updated')->exists())->toBeTrue();
});

/* ------------------------------------------------- time-based standings */

it('ranks a time-mode category by the fastest official run', function () {
    $this->category->update(['scoring_mode' => 'time']);

    $slowTeam = Team::factory()->create(['category_id' => $this->category->id]);

    $slowRun = Run::factory()->create([
        'event_id' => $this->event->id,
        'category_id' => $this->category->id,
        'arena_id' => $this->arena->id,
        'team_id' => $slowTeam->id,
        'status' => RunStatus::Finished,
        'official_time_ms' => 75_000,
    ]);

    $this->run->update([
        'status' => RunStatus::Finished,
        'official_time_ms' => 61_000,
    ]);

    $standings = app(StandingsService::class)->forCategory($this->category->fresh());

    expect($standings->first()['team']->id)->toBe($this->team->id)
        ->and($standings->first()['time_ms'])->toBe(61_000)
        ->and($standings->last()['team']->id)->toBe($slowTeam->id)
        ->and($standings->last()['time_ms'])->toBe(75_000);
});

/* ------------------------------------------------------- token management */

it('lets an event admin issue and revoke an arena token', function () {
    $admin = User::factory()->create();
    $admin->assignRole('event_admin');

    $this->actingAs($admin)
        ->get(route('eventadmin.tokens.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->post(route('eventadmin.tokens.store', $this->arena))
        ->assertRedirect(route('eventadmin.tokens.index'))
        ->assertSessionHas('plain_token');

    $this->arena->refresh();

    expect($this->arena->api_user_id)->not->toBeNull()
        ->and($this->arena->token_issued_at)->not->toBeNull();

    $plain = session('plain_token');
    expect(PersonalAccessToken::findToken($plain))->not->toBeNull();

    $this->actingAs($admin)
        ->delete(route('eventadmin.tokens.destroy', $this->arena))
        ->assertRedirect(route('eventadmin.tokens.index'));

    expect($this->arena->apiUser->tokens()->count())->toBe(0);
});

it('rotating an arena token invalidates the previous one', function () {
    $admin = User::factory()->create();
    $admin->assignRole('event_admin');

    $this->actingAs($admin)->post(route('eventadmin.tokens.store', $this->arena));
    $first = session('plain_token');

    $this->actingAs($admin)->post(route('eventadmin.tokens.store', $this->arena));
    $second = session('plain_token');

    expect($second)->not->toBe($first)
        ->and(PersonalAccessToken::findToken($first))->toBeNull()
        ->and(PersonalAccessToken::findToken($second))->not->toBeNull();
});

it('blocks a non event admin from issuing tokens', function () {
    $coach = User::factory()->create();
    $coach->assignRole('coach');

    $this->actingAs($coach)
        ->post(route('eventadmin.tokens.store', $this->arena))
        ->assertForbidden();
});
