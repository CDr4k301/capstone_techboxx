<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserAccountController extends Controller
{

    public function useraccount(Request $request) {
        $unverifiedUsers = UserVerification::all();
        $search = $request->input('search');

        // exclude the authenticated user first
        $authId = Auth::user()->id;
        $query = User::where('id', '!=', $authId);

        // Apply search filter if needed
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get the results and sort by latest created
        $userAccounts = $query->orderByDesc('created_at')->get();

        // compact -> takes the string passed into a key-value pair
        return view('admin-staff.useraccount', compact('unverifiedUsers', 'userAccounts'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:255',
            'role' => 'required|string',
        ]);

        // save password in a hash
        $validated['password'] = bcrypt($validated['password']);
        $validated['status'] = 'Active'; 

        User::create($validated);

        return redirect()->route('useraccount')->with([
            'message' => 'User Created!',
            'type' => 'success',
        ]);
    }

    public function approve($id) {
        $unverified = UserVerification::findOrFail($id);

        // check if a user with the same email already exists
        if (User::where('email', $unverified->email)->exists()) {
            return back()->with([
                'message' => 'This email is already registered and cannot be approved again.',
                'type' => 'error',
            ]);
        }
        
        User::create([
            'first_name' => $unverified->first_name,
            'last_name' => $unverified->last_name,
            'email' => $unverified->email,
            'password' => $unverified->password,
            'role' => 'Customer',
            'status' => 'Active'
        ]);
            // optionally sent email or notifiation here

            // delete the unverified record and id
            $unverified->delete();

            if ($unverified->id_uploaded) {
                Storage::disk('public')->delete($unverified->id_uploaded);
            }

            return back()->with([
                'message' => 'User has been approved and added.',
                'type' => 'success',    
            ]);
    }

    public function decline($id) {
        $unverified = UserVerification::findOrFail($id);

        $unverified->delete();
        if ($unverified->id_uploaded) {
            Storage::disk('public')->delete($unverified->id_uploaded);
        }

        return back()->with([
            'message' => 'User has been declined and deleted.',
            'type' => 'error',
        ]);
    }

    public function update(Request $request, $id) {
        $user = User::findOrFail($id);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        return redirect()->route('useraccount')->with([
            'message' => 'User updated',
            'type' => 'success',
        ]);
    }

    public function delete($id) {
        $user = User::findorFail($id);

        $user->delete();
        
        return back()->with([
            'message' => 'User has been deleted.',
            'type' => 'success',
        ]);
    }

    public function dashboard() {
        return view ('dashboard.dashboard');
    }
}
