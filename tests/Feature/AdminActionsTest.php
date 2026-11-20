<?php

use App\Enums\CategoryStatus;
use App\Enums\Level;
use App\Enums\RegistrationStatus;
use App\Enums\RunStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Run;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);

    $this->eventAdmin = fn () => User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();
    $this->superAdmin = fn () => User::where('email', 'admin@nanorobotic.test')->firstOrFail();
});

/* ---------------------------------------------------------------- Event Admin */

it('creates an event as a draft', function () {
    $response = $this->actingAs(($this->eventAdmin)())->post('/event-admin/event', [
        'name' => 'RoboRace Festival Pelajar',
        'venue' => 'Gedung Robotika',
        'starts_at' => now()->addMonth()->toDateString(),
        'ends_at' => now()->addMonth()->addDay()->toDateString(),
    ]);

    $response->assertRedirect('/event-admin/event');

    $event = Event::where('name', 'RoboRace Festival Pelajar')->firstOrFail();
    expect($event->status)->toBe('draft')
        ->and($event->slug)->not->toBeEmpty()
        ->and($event->created_by)->toBe(($this->eventAdmin)()->id);
});

it('refuses to open registration for an event without setup', function () {
    $event = Event::create([
        'name' => 'Event Kosong',
        'slug' => 'event-kosong',
        'status' => 'draft',
        'starts_at' => now()->addMonth(),
        'ends_at' => now()->addMonth()->addDay(),
    ]);

    $this->actingAs(($this->eventAdmin)())
        ->patch("/event-admin/event/{$event->uuid}/advance")
        ->assertSessionHasErrors('advance');

    expect($event->fresh()->status)->toBe('draft');
});

it('advances an event through the one-way flow', function () {
    $event = Event::create([
        'name' => 'Event Setup',
        'slug' => 'event-setup',
        'status' => 'draft',
        'starts_at' => now()->addMonth(),
        'ends_at' => now()->addMonth()->addDay(),
    ]);
    Category::create([
        'event_id' => $event->id,
        'level' => Level::Sd,
        'name' => 'SD Setup',
        'status' => CategoryStatus::Scheduled,
    ]);

    $admin = ($this->eventAdmin)();

    $this->actingAs($admin)->patch("/event-admin/event/{$event->uuid}/advance");
    expect($event->fresh()->status)->toBe('scheduled');

    $this->actingAs($admin)->patch("/event-admin/event/{$event->uuid}/advance");
    expect($event->fresh()->status)->toBe('running');

    $this->actingAs($admin)->patch("/event-admin/event/{$event->uuid}/advance");
    expect($event->fresh()->status)->toBe('finished');

    // Terminal state is refused.
    $this->actingAs($admin)
        ->patch("/event-admin/event/{$event->uuid}/advance")
        ->assertSessionHasErrors('advance');
    expect($event->fresh()->status)->toBe('finished');
});

it('schedules a run and rejects arena or team conflicts', function () {
    $admin = ($this->eventAdmin)();
    $event = Event::with(['categories', 'arenas'])->latest('starts_at')->firstOrFail();
    $team = $event->categories->first()->teams()->first() ?? Team::firstOrFail();
    $arena = $event->arenas->firstOrFail();

    $when = now()->addWeek()->setTime(9, 0)->toDateTimeString();

    $this->actingAs($admin)->post('/event-admin/jadwal', [
        'team_id' => $team->id,
        'arena_id' => $arena->id,
        'scheduled_at' => $when,
    ])->assertRedirect('/event-admin/jadwal');

    $run = Run::where('team_id', $team->id)->where('scheduled_at', $when)->firstOrFail();
    expect($run->status)->toBe(RunStatus::Scheduled)
        ->and($run->heat_slot_id)->not->toBeNull();

    // Same arena at the same time conflicts.
    $this->actingAs($admin)->post('/event-admin/jadwal', [
        'team_id' => Team::where('id', '!=', $team->id)->firstOrFail()->id,
        'arena_id' => $arena->id,
        'scheduled_at' => $when,
    ])->assertSessionHasErrors('scheduled_at');
});

it('forbids non-admins from event admin actions', function () {
    $coach = User::where('email', 'coach@nanorobotic.test')->firstOrFail();

    $this->actingAs($coach)->post('/event-admin/event', [
        'name' => 'Nope',
        'starts_at' => now()->addMonth()->toDateString(),
        'ends_at' => now()->addMonth()->addDay()->toDateString(),
    ])->assertForbidden();
});

/* ---------------------------------------------------------------- Super Admin */

it('invites a user with a role', function () {
    $this->actingAs(($this->superAdmin)())->post('/super-admin/pengguna', [
        'name' => 'Juri Baru',
        'email' => 'juri.baru@nanorobotic.test',
        'role' => 'judge',
    ])->assertRedirect('/super-admin/pengguna');

    $user = User::where('email', 'juri.baru@nanorobotic.test')->firstOrFail();

    expect($user->hasRole('judge'))->toBeTrue()
        ->and($user->is_active)->toBeTrue();
});

it('toggles a user active state but not its own account', function () {
    $admin = ($this->superAdmin)();
    $target = User::where('email', 'coach@nanorobotic.test')->firstOrFail();

    $this->actingAs($admin)->patch("/super-admin/pengguna/{$target->id}/toggle");
    expect($target->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->patch("/super-admin/pengguna/{$target->id}/toggle");
    expect($target->fresh()->is_active)->toBeTrue();

    $this->actingAs($admin)->patch("/super-admin/pengguna/{$admin->id}/toggle")->assertForbidden();
    expect($admin->fresh()->is_active)->toBeTrue();
});

it('approves a registration that meets the requirements', function () {
    $admin = ($this->superAdmin)();
    $registration = Registration::firstOrFail();
    $registration->update([
        'status' => RegistrationStatus::Submitted,
        'consent_parent' => true,
    ]);

    $this->actingAs($admin)
        ->patch("/super-admin/verifikasi/{$registration->uuid}/setujui")
        ->assertRedirect('/super-admin/verifikasi');

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Verified)
        ->and($registration->fresh()->team->status)->toBe(RegistrationStatus::Verified);
});

it('blocks approval without parent consent', function () {
    $admin = ($this->superAdmin)();
    $registration = Registration::firstOrFail();
    $registration->update([
        'status' => RegistrationStatus::Submitted,
        'consent_parent' => false,
    ]);

    $this->actingAs($admin)
        ->patch("/super-admin/verifikasi/{$registration->uuid}/setujui")
        ->assertSessionHasErrors('verify');

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Submitted);
});

it('rejects a registration with a recorded reason', function () {
    $admin = ($this->superAdmin)();
    $registration = Registration::firstOrFail();
    $registration->update(['status' => RegistrationStatus::UnderReview]);

    $this->actingAs($admin)->patch("/super-admin/verifikasi/{$registration->uuid}/tolak", [
        'reason' => 'Dokumen belum lengkap',
    ])->assertRedirect('/super-admin/verifikasi');

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Rejected)
        ->and($registration->fresh()->rejection_reason)->toBe('Dokumen belum lengkap');
});

it('requires a reason to reject', function () {
    $admin = ($this->superAdmin)();
    $registration = Registration::firstOrFail();
    $registration->update(['status' => RegistrationStatus::UnderReview]);

    $this->actingAs($admin)
        ->patch("/super-admin/verifikasi/{$registration->uuid}/tolak", [])
        ->assertSessionHasErrors('reason');

    expect($registration->fresh()->status)->toBe(RegistrationStatus::UnderReview);
});

it('updates platform settings', function () {
    $this->actingAs(($this->superAdmin)())->patch('/super-admin/pengaturan', [
        'require_email_verification' => '0',
        'scoring_method' => 'Telemetri',
        'robot_service_name' => 'API robot EduRover v2',
    ])->assertRedirect('/super-admin/pengaturan');

    expect(Setting::get('require_email_verification'))->toBeFalse()
        ->and(Setting::get('scoring_method'))->toBe('Telemetri')
        ->and(Setting::get('robot_service_name'))->toBe('API robot EduRover v2');
});

it('validates the scoring method', function () {
    $this->actingAs(($this->superAdmin)())->patch('/super-admin/pengaturan', [
        'require_email_verification' => '1',
        'scoring_method' => 'Nuklir',
        'robot_service_name' => 'API robot',
    ])->assertSessionHasErrors('scoring_method');
});
