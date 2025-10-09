<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserVerification;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserAccountController extends Controller
{
    public function index() {
        return view('admin.dashboard');
    }

    public function staffindex() {
        return view('staff.dashboard');
    }

    public function useraccount(Request $request) {
        $unverifiedUsers = UserVerification::paginate(9, ['*'], 'page_unverified');
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
        $userAccounts = $query->orderByRaw("CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END")
                              ->orderByDesc('created_at')
                              ->paginate(9);

        // compact -> takes the string passed into a key-value pair
        return view('admin.useraccount', compact('unverifiedUsers', 'userAccounts'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:255',
            'phone_number' => 'required|string|max:11',
            'address' => 'required|string|max:255',
            'role' => 'required|string',
            'email_verified_at' => now(),
        ]);

        // save password in a hash
        $validated['password'] = bcrypt($validated['password']);
        $validated['status'] = 'Active'; 

        $user = User::create($validated);

        ActivityLogService::userCreated($user, Auth::user());

        return redirect()->route('admin.useraccount')->with([
            'message' => 'User Created!',
            'type' => 'success',
        ]);
    }

    public function approve($id) {
        $unverified = UserVerification::findOrFail($id);
        $adminUser = Auth::user();

        // check if a user with the same email already exists
        if (User::where('email', $unverified->email)->exists()) {
            ActivityLogService::userApprovalFailed($unverified, $adminUser, 'Email already exists');
            return back()->with([
                'message' => 'This email is already registered and cannot be approved again.',
                'type' => 'error',
            ]);
        }
        
        $user = User::create([
            'first_name' => $unverified->first_name,
            'last_name' => $unverified->last_name,
            'email' => $unverified->email,
            'password' => $unverified->password,
            'role' => 'Customer',
            'status' => 'Active',
            'is_first_login' => false,
        ]);
        
            ActivityLogService::userApproved($unverified, $user, $adminUser);
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
        $adminUser = Auth::user();

        // Log the decline action before deletion
        ActivityLogService::userVerificationDeclined($unverified, $adminUser);

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
        $adminUser = Auth::user();

        // Store old data for logging
        $oldData = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        // Log the user update
        ActivityLogService::userUpdated($user, $adminUser, $oldData, $user->fresh()->toArray());

        return redirect()->route('admin.useraccount')->with([
            'message' => 'User updated',
            'type' => 'success',
        ]);
    }

    public function delete($id) {
        $user = User::findorFail($id);
        $adminUser = Auth::user();

        $user->update([
            'status' => 'Inactive'
        ]);

        // Log the user deactivation
        ActivityLogService::userDeactivated($user, $adminUser);
        
        return back()->with([
            'message' => 'User status has been inactive.',
            'type' => 'success',
        ]);
    }

    public function restore($id)
    {
        $user = User::findOrFail($id);
        $adminUser = Auth::user();

        if ($user->status === 'Inactive') {
            $user->status = 'Active';
            $user->save();

            // Log the user reactivation
            ActivityLogService::userReactivated($user, $adminUser);
        }

        return back()->with([
            'message' => 'User account has been reactivated.',
            'type' => 'success',
        ]);
    }


    public function dashboard() {
        return view ('dashboard.dashboard');
    }
}
