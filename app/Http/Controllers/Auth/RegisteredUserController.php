<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;


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
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone_number' => ['required','string','min:11','max:11']
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'role' => 'Customer',
            'status' => 'Active',
            'is_first_login' => false,
        ]);

        event(new Registered($user));

        // comment this out if: the newly registered user will automatically log in and will not be waiting for admin approval
        // if commented out, change route to dashboard or landing page
        Auth::login($user); 

        return redirect()->route('home');

        // consider functionality: making the id one-time-view
        // add new column in the user_verifications table
        // insert the following on the appropriate places:
        // function:
            // $table->timestamp('viewed_at')->nullable();
        // controller:
            // if ($user->viewed_at) {
            // abort(403, 'This ID has already been reviewed.');
            // }

            // $user->update(['viewed_at' => now()]);
        // display logic:
            // @if (!$unverifieduser->viewed_at)
            //     <img src="{{ asset('storage/' . $unverifieduser->id_uploaded) }}" alt="Valid ID">
            // @else
            //     <span class="text-red-600">ID already reviewed</span>
            // @endif
    }
}
