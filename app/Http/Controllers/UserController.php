<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\{Hash, Auth, Storage};

class UserController extends Controller
{
    /**
     * Handle user login with role-based session enforcement and 2FA support.
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
            // Check if 2FA is enabled
            if ($user->two_factor_confirmed_at) {
                // Store user id in session for 2FA verification
                session(['temp_user_id' => $user->id, 'temp_authenticated' => true]);
                return redirect()->route('2fa.verify')->with('info', 'Please enter your 2FA code.');
            }

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
     * Show 2FA verification screen
     */
    public function show2FAVerify()
    {
        if (!session('temp_authenticated')) {
            return redirect()->route('login');
        }

        return view('2fa-verify');
    }

    /**
     * Verify 2FA code
     */
    public function verify2FA(Request $request)
    {
        $request->validate([
            '2fa_code' => 'required|numeric|digits:6',
        ]);

        $userId = session('temp_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', '2FA session expired.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        // Verify the TOTP code
        if ($this->verify2FACode($user, $request->input('2fa_code'))) {
            Auth::login($user);
            $request->session()->forget(['temp_user_id', 'temp_authenticated']);
            $request->session()->regenerate();

            session([
                'authenticated' => true,
                'user_id'       => $user->id,
                'user_name'     => $user->name,
                'user_email'    => $user->email,
                'user_role'     => $user->role,
                'department_id' => $user->department_id,
            ]);

            return redirect()->route('dashboard')->with('success', 'Successfully logged in!');
        }

        return back()->withErrors([
            '2fa_code' => 'Invalid 2FA code. Please try again.',
        ]);
    }

    /**
     * Verify TOTP code against user's secret
     */
    private function verify2FACode($user, $code)
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        // Verify using TOTP algorithm
        for ($i = -1; $i <= 1; $i++) {
            $timeWindow = floor(time() / 30) + $i;
            $hash = hash_hmac('SHA1', pack('N*', 0, $timeWindow), base64_decode($user->two_factor_secret), true);
            $offset = ord($hash[strlen($hash) - 1]) & 0xf;
            $code_check = (unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff) % 1000000;
            
            if ($code_check == $code) {
                return true;
            }
        }

        return false;
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
            'password'      => 'required|string|min:12|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]+$/',
            'role'          => 'required|in:ADMIN,USER',
            'department_id' => 'required|exists:departments,id',
        ], [
            'password.min' => 'Password must be at least 12 characters long.',
            'password.regex' => 'Password must contain uppercase, lowercase, numbers, and special characters.',
            'role.in' => 'Role must be either ADMIN or USER.',
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
            'password'      => 'nullable|string|min:12|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]+$/',
            'role'          => 'required|in:ADMIN,USER',
            'department_id' => 'required|exists:departments,id',
        ], [
            'password.min' => 'Password must be at least 12 characters long.',
            'password.regex' => 'Password must contain uppercase, lowercase, numbers, and special characters.',
            'role.in' => 'Role must be either ADMIN or USER.',
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