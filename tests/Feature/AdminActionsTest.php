<?php

use App\Enums\CategoryStatus;
use App\Enums\Level;
use App\Enums\RegistrationStatus;
use App\Enums\RunStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\HeatSlot;
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

it('reschedules a not-yet-started session', function () {
    $admin = ($this->eventAdmin)();
    $event = Event::with(['categories', 'arenas'])->latest('starts_at')->firstOrFail();
    $arena = $event->arenas->firstOrFail();
    $otherArena = $event->arenas->where('id', '!=', $arena->id)->first();
    $team = $event->categories->first()->teams()->firstOrFail();

    $when = now()->addWeek()->setTime(11, 0);
    $this->actingAs($admin)->post('/event-admin/jadwal', [
        'team_id' => $team->id,
        'arena_id' => $arena->id,
        'scheduled_at' => $when->toDateTimeString(),
    ]);

    $run = Run::where('team_id', $team->id)->where('scheduled_at', $when)->firstOrFail();

    $newTime = $when->copy()->addHours(2);
    $targetArena = $otherArena ?? $arena;

    $this->actingAs($admin)->patch("/event-admin/jadwal/{$run->uuid}", [
        'arena_id' => $targetArena->id,
        'scheduled_at' => $newTime->toDateTimeString(),
        'duration' => 20,
    ])->assertRedirect('/event-admin/jadwal');

    expect($run->fresh()->scheduled_at->equalTo($newTime))->toBeTrue()
        ->and($run->fresh()->arena_id)->toBe($targetArena->id);
});

it('refuses to reschedule a session that already started', function () {
    $admin = ($this->eventAdmin)();
    $run = Run::firstOrFail();
    $run->update(['status' => RunStatus::Running]);

    $this->actingAs($admin)->patch("/event-admin/jadwal/{$run->uuid}", [
        'arena_id' => $run->arena_id,
        'scheduled_at' => now()->addDay()->toDateTimeString(),
    ])->assertSessionHasErrors('scheduled_at');
});

it('cancels a scheduled session and prunes its empty heat slot', function () {
    $admin = ($this->eventAdmin)();
    $event = Event::with(['categories', 'arenas'])->latest('starts_at')->firstOrFail();
    $arena = $event->arenas->firstOrFail();
    $team = $event->categories->first()->teams()->firstOrFail();

    $when = now()->addWeek()->setTime(14, 0);
    $this->actingAs($admin)->post('/event-admin/jadwal', [
        'team_id' => $team->id,
        'arena_id' => $arena->id,
        'scheduled_at' => $when->toDateTimeString(),
    ]);

    $run = Run::where('team_id', $team->id)->where('scheduled_at', $when)->firstOrFail();
    $slotId = $run->heat_slot_id;

    $this->actingAs($admin)->delete("/event-admin/jadwal/{$run->uuid}")
        ->assertRedirect('/event-admin/jadwal');

    expect(Run::find($run->id))->toBeNull()
        ->and(HeatSlot::find($slotId))->toBeNull();
});

it('changes a user role and creates a coach profile when promoted', function () {
    $admin = ($this->superAdmin)();
    $user = User::where('email', 'publik@nanorobotic.test')->firstOrFail();

    $this->actingAs($admin)->patch("/super-admin/pengguna/{$user->id}/peran", [
        'role' => 'coach',
        'school' => 'SMP Peran Baru',
    ])->assertRedirect('/super-admin/pengguna');

    $user->refresh();

    expect($user->hasRole('coach'))->toBeTrue()
        ->and($user->hasRole('public'))->toBeFalse()
        ->and($user->coachProfile?->school?->name)->toBe('SMP Peran Baru');
});

it('requires a school when promoting a user to coach', function () {
    $admin = ($this->superAdmin)();
    $user = User::where('email', 'publik@nanorobotic.test')->firstOrFail();

    $this->actingAs($admin)->patch("/super-admin/pengguna/{$user->id}/peran", [
        'role' => 'coach',
    ])->assertSessionHasErrors('school');

    expect($user->fresh()->hasRole('coach'))->toBeFalse();
});

it('lets an event admin move a category through its pre-publication lifecycle', function () {
    $admin = ($this->eventAdmin)();
    $category = Category::firstOrFail();

    $this->actingAs($admin)->patch("/event-admin/setup/kategori/{$category->uuid}", [
        'status' => CategoryStatus::Running->value,
    ])->assertRedirect();

    expect($category->fresh()->status)->toBe(CategoryStatus::Running);
});

it('refuses publishing a category from the event admin setup page', function () {
    $admin = ($this->eventAdmin)();
    $category = Category::firstOrFail();

    $this->actingAs($admin)->patch("/event-admin/setup/kategori/{$category->uuid}", [
        'status' => CategoryStatus::Published->value,
    ])->assertForbidden();

    expect($category->fresh()->status)->not->toBe(CategoryStatus::Published);
});
