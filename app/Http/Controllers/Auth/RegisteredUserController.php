<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'roles' => Role::where('guard_name', 'web')
                ->where('name', '!=', 'Admin')
                ->orderBy('name')
                ->get()
                ->map(fn ($role) => [
                    'value' => $role->name,
                    'label' => $role->name === 'Patient' ? 'User or Patient' : $role->name,
                ])
                ->values(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'email'             => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'registration_type' => 'required|string|in:Patient,Doctor,Pharmacy,Hospital',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);

        $patient = Patient::where('email', $request->email)->update(['user_id' => $user->id]);

        $role = Role::where('name', $request->registration_type)
            ->where('guard_name', 'web')
            ->firstOrFail();
        $user->syncRoles([$role]);

        event(new Registered($user));

        Auth::guard('web')->login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
