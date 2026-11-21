<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration.
     *
     * Registering is the school's entry point into the competition, so the
     * account is created as a Coach together with its School record. Without
     * that link the coach dashboard would have nothing to show.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            // New self-registered accounts must confirm their email address.
            // In local/dev the mailer is `log`, so the link is written to
            // storage/logs/laravel.log instead of being delivered.
            if (Setting::flag('require_email_verification', true)) {
                $user->forceFill(['email_verified_at' => null])->save();
            } else {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->syncRoles([Role::findOrCreate(RoleEnum::Coach->value, 'web')->name]);

            $school = School::firstOrCreate(
                ['name' => $request->school],
                [
                    'contact_name' => $user->name,
                    'contact_email' => $user->email,
                ],
            );

            Coach::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
