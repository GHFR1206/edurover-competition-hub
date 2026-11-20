<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Printable certificates. The certificate page is print-to-PDF friendly
 * (no binary PDF dependency). A public verification page looks up a
 * certificate by its opaque QR token.
 */
class CertificateController extends Controller
{
    public function show(Request $request, Certificate $certificate): View
    {
        $user = $request->user();

        $ownsTeam = $user->coachProfile?->school_id
            && $user->coachProfile->school_id === $certificate->team?->school_id;

        $isStaff = $user->hasAnyPermission(['export.certificates', 'audit.view', 'results.view.all']);

        abort_unless($ownsTeam || $isStaff, 403);

        $certificate->load(['team.school', 'category.event']);

        return view('certificates.show', ['certificate' => $certificate]);
    }

    /** Public verification by opaque token (no login required). */
    public function verify(string $token): View
    {
        $certificate = Certificate::with(['team.school', 'category.event'])
            ->where('qr_token', $token)
            ->first();

        return view('certificates.verify', ['certificate' => $certificate]);
    }
}
