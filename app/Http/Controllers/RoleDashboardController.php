<?php

namespace App\Http\Controllers;

use App\Enums\Role as RoleEnum;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Single entry point after login. Each role is redirected to its own dashboard.
 */
class RoleDashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->isRole(RoleEnum::Coach) => redirect()->route('coach.dashboard'),
            $user->isRole(RoleEnum::Judge) => redirect()->route('judge.dashboard'),
            $user->isRole(RoleEnum::ArenaOperator) => redirect()->route('operator.dashboard'),
            $user->isRole(RoleEnum::ChiefJudge) => redirect()->route('chief.dashboard'),
            default => view('dashboard', [
                'event' => Event::with('categories')->latest('starts_at')->first(),
            ]),
        };
    }
}
