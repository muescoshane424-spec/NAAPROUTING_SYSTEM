<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Office;
use Illuminate\Support\Facades\{Hash, Auth, Storage};

class UserController extends Controller
{
    /**
     * Handle user login with Admin-Only enforcement.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is an ADMIN (Case-sensitive check)
            if ($user->role !== 'ADMIN') {
                Auth::logout();
                session()->flush();
                return back()->with('error', 'Access denied. Administrator privileges required.');
            }

            session([
                'user_id'    => $user->id,
                'user_name'  => $user->name,
                'user_email' => $user->email,
                'user_role'  => $user->role
            ]);

            return redirect()->route('dashboard')->with('success', 'Logged in as Admin.');
        }

        return back()->with('error', 'Invalid credentials. Please try again.')->withInput($request->only('email'));
    }

    /**
     * Display a listing of the users (Admin View).
     */
    public function index()
    {
        // Now 'with(office)' will work because we defined it in the Model
        $users = User::with('office')->latest()->get();
        $offices = Office::all(); 
        
        return view('users', compact('users', 'offices'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:8',
            'role'      => 'required|in:ADMIN,USER,HEAD',
            'office_id' => 'required|exists:offices,id',
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'office_id' => $validated['office_id'],
            'password'  => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'User account created successfully!');
    }

    /**
     * Update user details and roles.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password'  => 'nullable|string|min:8',
            'role'      => 'required|in:ADMIN,USER,HEAD',
            'office_id' => 'required|exists:offices,id',
        ]);

        $user->fill([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'office_id' => $validated['office_id'],
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if (Auth::id() == $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User removed from the system.');
    }
}