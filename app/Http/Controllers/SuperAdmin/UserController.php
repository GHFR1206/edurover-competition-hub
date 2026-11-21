<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Super Admin user management: invite, search/filter, activate/deactivate and
 * change the platform role of an existing account.
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

    /**
     * Replace the account's role. Only one platform role is allowed per user,
     * so the previous role is removed in the same transaction. Promoting to
     * Coach also needs a school: the profile is created when it is missing.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak dapat mengubah peran akun sendiri.');

        $validated = $request->validate([
            'role' => ['required', Rule::in(RoleEnum::values())],
            'school' => ['nullable', 'string', 'max:255', 'required_if:role,'.RoleEnum::Coach->value],
        ], [], ['school' => 'nama sekolah']);

        if ($validated['role'] === RoleEnum::Coach->value && ! $user->coachProfile) {
            DB::transaction(function () use ($user, $validated) {
                $school = School::firstOrCreate(
                    ['name' => $validated['school']],
                    ['contact_name' => $user->name, 'contact_email' => $user->email],
                );

                Coach::create([
                    'user_id' => $user->id,
                    'school_id' => $school->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            });
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('superadmin.users.index')
            ->with('status', "Peran {$user->name} diubah menjadi {$validated['role']}.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak dapat menonaktifkan akun sendiri.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', "Akun {$user->name} ".($user->is_active ? 'diaktifkan' : 'dinonaktifkan').'.');
    }
}
