<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\{Hash, Auth, Storage};

class UserController extends Controller
{
    /**
     * Handle user login with role-based session enforcement.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username or email is required.',
            'password.required' => 'Password is required.',
        ]);

        $input = $request->input('username');
        $password = $request->input('password');
        
        // Try to find user by username or email
        $user = User::where('username', $input)
                    ->orWhere('email', $input)
                    ->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            session([
                'authenticated' => true,
                'user_id'       => $user->id,
                'user_name'     => $user->name,
                'user_email'    => $user->email,
                'user_role'     => $user->role,
                'department_id' => $user->department_id,
            ]);

            return redirect()->route('dashboard')->with('success', 'Welcome back, '. $user->name .'.');
        }

        return back()->with('error', 'Invalid credentials. Please try again.')->withInput($request->only('username'));
    }

    /**
     * Redirect the named register route to the user management page.
     */
    public function redirectToUsers()
    {
        return redirect()->route('users.index');
    }

    /**
     * Display a listing of the users (Admin View).
     */
    public function index()
    {
        $this->authorizeAdmin();

        $this->ensureDefaultDepartments();

        $users = User::with('department')->latest()->get();
        $departments = Department::all(); 
        
        return view('users', compact('users', 'departments'));
    }

    /**
     * Show the user registration page.
     */
    public function create()
    {
        $this->authorizeAdmin();

        $this->ensureDefaultDepartments();

        $departments = Department::all();
        return view('users.create', compact('departments'));
    }

    protected function ensureDefaultDepartments(): void
    {
        $defaultDepartments = ['ILAS', 'INET', 'ICS'];

        foreach ($defaultDepartments as $name) {
            Department::firstOrCreate([
                'name' => $name,
            ], [
                'description' => null,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:8',
            'role'          => 'required|in:ADMIN,USER,HEAD,staff',
            'department_id' => 'required|exists:departments,id',
        ]);

        User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'role'          => $validated['role'],
            'department_id' => $validated['department_id'],
            'password'      => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'User account created successfully!');
    }

    /**
     * Update user details and roles.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password'      => 'nullable|string|min:8',
            'role'          => 'required|in:ADMIN,USER,HEAD,staff',
            'department_id' => 'required|exists:departments,id',
        ]);

        $user->fill([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'role'          => $validated['role'],
            'department_id' => $validated['department_id'],
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
        $this->authorizeAdmin();

        $user = User::findOrFail($id);
        
        if (Auth::id() == $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User removed from the system.');
    }

    protected function authorizeAdmin()
    {
        if (session('user_role') !== 'ADMIN') {
            abort(403, 'Administrator privileges are required to perform this action.');
        }
    }
}