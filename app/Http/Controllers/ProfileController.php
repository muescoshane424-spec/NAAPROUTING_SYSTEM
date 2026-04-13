<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage};
use Illuminate\Support\Str;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        // Safety check: Try to get user from session email
        $user = User::where('email', session('user_email'))->first();

        // If user is null (session expired or not found), redirect to login
        // This prevents the "Attempt to read property email on null" error
        if (!$user) {
            return redirect()->route('home')->with('error', 'Please log in again.');
        }

        return view('profile', compact('user'));
    }

    public function updateInfo(Request $request)
    {
        $user = User::where('email', session('user_email'))->first();
        
        if (!$user) return back()->with('error', 'User not found.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        // Update session to reflect new data immediately
        session(['user_name' => $user->name, 'user_email' => $user->email]);
        
        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::where('email', session('user_email'))->first();
        
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
            return back()->with('success', 'Password changed successfully!');
        }

        return back()->with('error', 'Action failed.');
    }

    public function updateSignature(Request $request)
    {
        $user = User::where('email', session('user_email'))->first();
        $path = null;

        if (!$user) return back()->with('error', 'Session expired.');

        // 1. Handle File Upload
        if ($request->hasFile('sig_file')) {
            $path = $request->file('sig_file')->store('signatures', 'public');
        } 
        // 2. Handle Base64 Canvas Data
        elseif ($request->signature_data) {
            $imageName = 'sig_' . Str::random(10) . '.png';
            $data = $request->signature_data;
            // Clean the base64 string
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = str_replace(' ', '+', $data);
            
            Storage::disk('public')->put('signatures/' . $imageName, base64_decode($data));
            $path = 'signatures/' . $imageName;
        }

        if ($path) {
            // Delete old signature if it exists
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature);
            }

            $user->update(['signature' => $path]);
            return back()->with('success', 'Digital signature updated!');
        }

        return back()->with('error', 'No signature data received.');
    }
}