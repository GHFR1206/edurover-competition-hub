<?php

namespace App\Http\Controllers;

use App\Enums\Role as RoleEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Single entry point after login. Each role is redirected to its own dashboard.
 */
class RoleDashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->isRole(RoleEnum::Coach) => redirect()->route('coach.dashboard'),
            $user->isRole(RoleEnum::Judge) => redirect()->route('judge.dashboard'),
            $user->isRole(RoleEnum::ArenaOperator) => redirect()->route('operator.dashboard'),
            $user->isRole(RoleEnum::ChiefJudge) => redirect()->route('chief.dashboard'),
            $user->isRole(RoleEnum::EventAdmin) => redirect()->route('eventadmin.dashboard'),
            $user->isRole(RoleEnum::SuperAdmin) => redirect()->route('superadmin.dashboard'),
            // Spectators — and any account without a competition role — land on
            // the public dashboard instead of a placeholder page.
            default => redirect()->route('public.dashboard'),
        };
    }
}
