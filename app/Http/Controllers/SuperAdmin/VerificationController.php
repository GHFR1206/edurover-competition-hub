<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/**
 * Super Admin team verification queue. Approval requires parent consent and
 * at least one member; rejection and revision both require a reason.
 */
class VerificationController extends Controller
{
    public function __construct(private readonly RegistrationService $registrations) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $registrations = Registration::with(['team.school', 'team.participants', 'category', 'coach', 'event'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('submitted_at')
            ->get();

        return view('super-admin.verification', [
            'registrations' => $registrations,
            'activeStatus' => $status,
            'counts' => [
                'all' => Registration::count(),
                'submitted' => Registration::where('status', RegistrationStatus::Submitted->value)->count(),
                'under_review' => Registration::where('status', RegistrationStatus::UnderReview->value)->count(),
                'verified' => Registration::where('status', RegistrationStatus::Verified->value)->count(),
                'rejected' => Registration::where('status', RegistrationStatus::Rejected->value)->count(),
            ],
            'page' => 'verifikasi',
        ]);
    }

    public function show(Registration $registration): View
    {
        $registration->load(['team.participants', 'team.school', 'category', 'coach', 'event', 'team.runs']);

        return view('super-admin.verification-show', [
            'registration' => $registration,
            'blockers' => $this->registrations->verifyBlockers($registration),
            'page' => 'verifikasi',
        ]);
    }

    public function approve(Request $request, Registration $registration): RedirectResponse
    {
        $blockers = $this->registrations->verify($registration, $request->user());

        if ($blockers !== []) {
            return back()->withErrors(['verify' => $blockers]);
        }

        return redirect()->route('superadmin.verification.index')
            ->with('status', "Tim {$registration->team?->name} diverifikasi.");
    }

    public function reject(Request $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        try {
            $this->registrations->reject($registration, $request->user(), $validated['reason']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()->route('superadmin.verification.index')
            ->with('status', 'Pendaftaran ditolak dengan alasan tercatat.');
    }

    public function requestRevision(Request $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        // Revision keeps the entry in under_review with a recorded note.
        $registration->update([
            'status' => RegistrationStatus::UnderReview,
            'rejection_reason' => $validated['reason'],
        ]);

        return redirect()->route('superadmin.verification.index')
            ->with('status', 'Permintaan revisi dikirim ke coach.');
    }
}
