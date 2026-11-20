<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super Admin summary dashboard: platform-wide counters and recent audit.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('super-admin.dashboard', [
            'events' => Event::withCount(['categories', 'arenas'])->latest('starts_at')->take(5)->get(),
            'userCount' => User::count(),
            'teamCount' => Team::count(),
            'pendingCount' => Registration::whereIn('status', ['submitted', 'under_review'])->count(),
            'eventCount' => Event::count(),
            'recentAudit' => AuditLog::latest('created_at')->take(8)->get(),
            'system' => [
                ['label' => 'Database', 'value' => 'MySQL 8.0', 'tone' => 'ok'],
                ['label' => 'Queue', 'value' => 'database', 'tone' => 'ok'],
                ['label' => 'Realtime (Reverb)', 'value' => 'aktif', 'tone' => 'ok'],
                ['label' => 'Storage', 'value' => 'local', 'tone' => 'warn'],
            ],
            'page' => 'ringkasan',
        ]);
    }
}
