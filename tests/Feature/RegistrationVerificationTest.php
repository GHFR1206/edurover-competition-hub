<?php

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use App\Services\RegistrationService;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(DemoSeeder::class);
});

/** Move a registration to a given state without going through the UI. */
function registrationIn(RegistrationStatus $status): Registration
{
    $registration = Registration::firstOrFail();
    $registration->update(['status' => $status, 'consent_parent' => true]);

    return $registration->fresh();
}

it('labels every registration status in Indonesian', function () {
    expect(RegistrationStatus::Draft->label())->toBe('Draft')
        ->and(RegistrationStatus::Submitted->label())->toBe('Diajukan')
        ->and(RegistrationStatus::UnderReview->label())->toBe('Ditinjau')
        ->and(RegistrationStatus::Verified->label())->toBe('Terverifikasi')
        ->and(RegistrationStatus::Rejected->label())->toBe('Ditolak')
        ->and(RegistrationStatus::Revision->label())->toBe('Perlu Revisi')
        ->and(RegistrationStatus::Withdrawn->label())->toBe('Dibatalkan');
});

it('does not call an entry verified while it is still under review', function () {
    $registration = registrationIn(RegistrationStatus::UnderReview);

    // "Diverifikasi" used to be the label for under_review, which made the
    // committee page claim an entry was verified before anyone approved it.
    expect($registration->status->label())->not->toBe('Terverifikasi')
        ->and($registration->status->label())->toBe('Ditinjau');
});

it('sends an entry back for revision instead of marking it rejected', function () {
    $service = app(RegistrationService::class);
    $admin = User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();

    $registration = registrationIn(RegistrationStatus::Submitted);

    expect($service->requestRevision($registration, $admin, 'Surat belum ditandatangani'))->toBeEmpty();

    $registration->refresh();
    expect($registration->status)->toBe(RegistrationStatus::Revision)
        ->and($registration->rejection_reason)->toBe('Surat belum ditandatangani')
        ->and($registration->verified_by)->toBe($admin->id)
        ->and($registration->team->fresh()->status)->toBe(RegistrationStatus::Revision);
});

it('lets the coach fix and resubmit an entry that needs revision', function () {
    $service = app(RegistrationService::class);
    $admin = User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();
    $coach = User::where('email', 'coach@nanorobotic.test')->firstOrFail();

    $registration = registrationIn(RegistrationStatus::Submitted);
    $service->requestRevision($registration, $admin, 'Dokumen kurang');

    // The status must advertise itself as editable, otherwise the coach page
    // hides the form and the team is stuck forever.
    expect($registration->fresh()->status->isEditableByCoach())->toBeTrue();

    $this->actingAs($coach)
        ->post("/coach/tim/{$registration->team->uuid}/ajukan", [
            'consent_parent' => 1,
            'consent_media' => 1,
        ])
        ->assertRedirect();

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Submitted)
        ->and($registration->fresh()->rejection_reason)->toBeNull();
});

it('shows the resubmit form to the coach after a revision request', function () {
    $service = app(RegistrationService::class);
    $admin = User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();
    $coach = User::where('email', 'coach@nanorobotic.test')->firstOrFail();

    $registration = registrationIn(RegistrationStatus::Submitted);
    $service->requestRevision($registration, $admin, 'Unggah ulang surat');

    $this->actingAs($coach)
        ->get("/coach/tim/{$registration->team->uuid}")
        ->assertOk()
        ->assertSee('Perlu Revisi')
        ->assertSee('Unggah ulang surat')
        ->assertSee('Ajukan ulang');
});

it('refuses to request revision on an already verified entry', function () {
    $service = app(RegistrationService::class);
    $admin = User::where('email', 'eventadmin@nanorobotic.test')->firstOrFail();

    $registration = registrationIn(RegistrationStatus::Verified);

    $blockers = $service->requestRevision($registration, $admin, 'Terlambat');

    expect($blockers)->not->toBeEmpty()
        ->and($registration->fresh()->status)->toBe(RegistrationStatus::Verified);
});

it('hides the decision buttons on the committee page once an entry is settled', function () {
    $admin = User::where('email', 'admin@nanorobotic.test')->firstOrFail();
    $registration = registrationIn(RegistrationStatus::Verified);

    $this->actingAs($admin)
        ->get("/super-admin/verifikasi/{$registration->uuid}")
        ->assertOk()
        ->assertDontSee('Minta revisi')
        ->assertDontSee('>Tolak<', false);
});

it('shows the decision buttons while an entry is awaiting a decision', function () {
    $admin = User::where('email', 'admin@nanorobotic.test')->firstOrFail();
    $registration = registrationIn(RegistrationStatus::UnderReview);

    $this->actingAs($admin)
        ->get("/super-admin/verifikasi/{$registration->uuid}")
        ->assertOk()
        ->assertSee('Setujui')
        ->assertSee('Minta revisi');
});

it('requires a reason to request a revision', function () {
    $admin = User::where('email', 'admin@nanorobotic.test')->firstOrFail();
    $registration = registrationIn(RegistrationStatus::Submitted);

    $this->actingAs($admin)
        ->patch("/super-admin/verifikasi/{$registration->uuid}/revisi", [])
        ->assertSessionHasErrors('reason');

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Submitted);
});
