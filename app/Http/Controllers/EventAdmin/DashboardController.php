<?php

namespace App\Http\Controllers\EventAdmin;

use App\Enums\CategoryStatus;
use App\Http\Controllers\Controller;
use App\Models\Arena;
use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Run;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Event Admin landing dashboard. Event-scoped counters only.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $event = Event::latest('starts_at')->first();

        $categories = $event ? $event->categories : collect();
        $categoryIds = $categories->pluck('id');

        return view('event-admin.dashboard', [
            'event' => $event,
            'teamCount' => Team::whereIn('category_id', $categoryIds)->count(),
            'arenaCount' => $event ? Arena::where('event_id', $event->id)->count() : 0,
            'pendingRegistrations' => Registration::whereIn('category_id', $categoryIds)
                ->whereIn('status', ['submitted', 'under_review'])->count(),
            'runCount' => Run::whereIn('category_id', $categoryIds)->count(),
            'judgeCount' => User::role('judge')->count(),
            'categoryCount' => $categories->count(),
            'page' => 'events',
        ]);
    }
}
