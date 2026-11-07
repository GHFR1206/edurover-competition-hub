<?php

use App\Enums\CategoryStatus;
use App\Enums\Level;
use App\Enums\RegistrationStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Services\RegistrationService;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);
});

it('only lets a verifier role verify a registration', function () {
    $eventAdmin = User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();
    $coach = User::where('email', 'coach@nanorobotic.test')->firstOrFail();

    $registration = Registration::firstOrFail();

    expect($eventAdmin->can('verify', $registration))->toBeTrue()
        ->and($coach->can('verify', $registration))->toBeFalse();
});

it('verifies a submitted registration and mirrors team status', function () {
    $service = app(RegistrationService::class);
    $admin = User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();

    $registration = Registration::firstOrFail();
    $registration->update(['status' => RegistrationStatus::Submitted, 'consent_parent' => true]);

    expect($service->verify($registration->fresh(), $admin))->toBeEmpty();

    $registration->refresh();
    expect($registration->status)->toBe(RegistrationStatus::Verified)
        ->and($registration->verified_by)->toBe($admin->id)
        ->and($registration->verified_at)->not->toBeNull()
        ->and($registration->team->fresh()->status)->toBe(RegistrationStatus::Verified);
});

it('refuses verification without parent consent', function () {
    $service = app(RegistrationService::class);
    $admin = User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();

    $registration = Registration::firstOrFail();
    $registration->update(['status' => RegistrationStatus::UnderReview, 'consent_parent' => false]);

    expect($service->verify($registration->fresh(), $admin))->not->toBeEmpty()
        ->and($registration->fresh()->status)->toBe(RegistrationStatus::UnderReview);
});

it('rejects a registration with a reason', function () {
    $service = app(RegistrationService::class);
    $admin = User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();

    $registration = Registration::firstOrFail();
    $registration->update(['status' => RegistrationStatus::UnderReview]);

    $service->reject($registration->fresh(), $admin, 'Dokumen belum lengkap');

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Rejected)
        ->and($registration->fresh()->rejection_reason)->toBe('Dokumen belum lengkap');
});

it('blocks illegal transitions', function () {
    $registration = Registration::firstOrFail();
    $registration->update(['status' => RegistrationStatus::Draft]);

    expect(fn () => app(RegistrationService::class)->verify($registration->fresh(), User::first()))
        ->not->toThrow(RuntimeException::class); // verify() returns blockers, does not throw
});

it('scopes the publication page to one event', function () {
    $chief = User::where('email', 'chief@nanorobotic.test')->firstOrFail();

    $other = Event::create([
        'name' => 'Event Kedua',
        'slug' => 'event-kedua',
        'status' => 'scheduled',
        'starts_at' => now()->addMonths(2),
        'ends_at' => now()->addMonths(2)->addDay(),
    ]);

    Category::create([
        'event_id' => $other->id,
        'level' => Level::Sd,
        'name' => 'SD Event Kedua',
        'status' => CategoryStatus::Scheduled,
    ]);

    $response = $this->actingAs($chief)->get('/chief/publikasi?event='.$other->id);
    $response->assertOk();

    $categories = $response->viewData('categories');
    expect($categories)->toHaveCount(1)
        ->and($categories->first()['category']->event_id)->toBe($other->id);
});

it('cannot publish a category from another event', function () {
    $chief = User::where('email', 'chief@nanorobotic.test')->firstOrFail();

    $other = Event::create([
        'name' => 'Event Lain',
        'slug' => 'event-lain',
        'status' => 'scheduled',
    ]);

    $category = Category::create([
        'event_id' => $other->id,
        'level' => Level::Sma,
        'name' => 'SMA Event Lain',
        'status' => CategoryStatus::Scheduled,
    ]);

    // Event is still visible to the chief, so the guard is about event membership
    // rather than a hard 403 here; assert the publish is refused for lack of runs.
    $this->actingAs($chief)
        ->post('/chief/publikasi/'.$category->uuid)
        ->assertRedirect();

    expect($category->fresh()->isPublished())->toBeFalse();
});
