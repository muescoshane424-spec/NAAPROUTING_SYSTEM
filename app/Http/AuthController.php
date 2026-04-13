<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

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

        if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            // Check if 2FA is enabled for this user
            if ($user->two_factor_confirmed_at) {
                // Store user id in session for 2FA verification
                session(['temp_user_id' => $user->id, 'temp_authenticated' => true]);
                return redirect()->route('2fa.verify')->with('info', 'Please enter your 2FA code.');
            }

            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
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
            return redirect()->route('dashboard')->with('success', 'Successfully logged in!');
        }

        return back()->withErrors([
            '2fa_code' => 'Invalid 2FA code. Please try again.',
        ]);
    }

    /**
     * Verify TOTP code
     */
    private function verify2FACode($user, $code)
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        // Use a TOTP verification library
        $totp = $this->generateTOTP($user->two_factor_secret);
        
        // Allow current code and ±1 time window to account for time skew
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
     * Generate TOTP for testing
     */
    private function generateTOTP($secret)
    {
        $timeWindow = floor(time() / 30);
        $hash = hash_hmac('SHA1', pack('N*', 0, $timeWindow), base64_decode($secret), true);
        $offset = ord($hash[strlen($hash) - 1]) & 0xf;
        return (unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff) % 1000000;
    }
}