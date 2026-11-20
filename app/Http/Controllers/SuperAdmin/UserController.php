<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Super Admin user management: invite, search/filter, activate/deactivate.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->when($request->query('q'), function ($q, $term) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->query('role'), fn ($q, $role) => $q->role($role))
            ->latest()
            ->get();

        return view('super-admin.users', [
            'users' => $users,
            'roles' => RoleEnum::values(),
            'search' => $request->query('q'),
            'roleFilter' => $request->query('role'),
            'page' => 'pengguna',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::in(RoleEnum::values())],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => str()->password(16),
            'is_active' => true,
        ]);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('superadmin.users.index')
            ->with('status', "Undangan untuk {$validated['email']} terkirim sebagai {$validated['role']}.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak dapat menonaktifkan akun sendiri.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', "Akun {$user->name} ".($user->is_active ? 'diaktifkan' : 'dinonaktifkan').'.');
    }
}
