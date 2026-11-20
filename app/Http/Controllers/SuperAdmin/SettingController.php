<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Super Admin platform settings: account security and the default scoring
 * method. Events already running keep their locked ruleset.
 */
class SettingController extends Controller
{
    private const DEFAULTS = [
        'require_email_verification' => true,
        'scoring_method' => 'Hybrid',
        'robot_service_name' => 'API robot EduRover',
    ];

    public function index(): View
    {
        return view('super-admin.settings', [
            'settings' => [
                'require_email_verification' => Setting::get('require_email_verification', self::DEFAULTS['require_email_verification']),
                'scoring_method' => Setting::get('scoring_method', self::DEFAULTS['scoring_method']),
                'robot_service_name' => Setting::get('robot_service_name', self::DEFAULTS['robot_service_name']),
            ],
            'page' => 'pengaturan',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'require_email_verification' => ['required', 'boolean'],
            'scoring_method' => ['required', Rule::in(['Manual', 'Hybrid', 'Telemetri'])],
            'robot_service_name' => ['required', 'string', 'max:255'],
        ]);

        Setting::put('require_email_verification', (bool) $validated['require_email_verification']);
        Setting::put('scoring_method', $validated['scoring_method']);
        Setting::put('robot_service_name', $validated['robot_service_name']);

        return redirect()->route('superadmin.settings.index')
            ->with('status', 'Pengaturan berhasil disimpan.');
    }
}
