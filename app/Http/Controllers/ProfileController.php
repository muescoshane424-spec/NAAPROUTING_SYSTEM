<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage, Log};
use Illuminate\Support\Str;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        try {
            $user = User::where('email', session('user_email'))->first();

            if (!$user) {
                return redirect()->route('home')->with('error', 'Please log in again.');
            }

            return view('profile', compact('user'));

        } catch (\Exception $e) {
            Log::error('Profile Load Error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Failed to load profile.');
        }
    }

    public function updateInfo(Request $request)
    {
        try {
            $user = User::where('email', session('user_email'))->first();

            if (!$user) {
                return back()->with('error', 'User not found.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]);

            $user->update($validated);

            session([
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);

            return back()->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            Log::error('Profile Update Info Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update profile.');
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|min:8|confirmed'
            ]);

            $user = User::where('email', session('user_email'))->first();

            if (!$user) {
                return back()->with('error', 'User not found.');
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return back()->with('success', 'Password changed successfully!');

        } catch (\Exception $e) {
            Log::error('Password Update Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to change password.');
        }
    }

    public function updateSignature(Request $request)
    {
        try {
            $user = User::where('email', session('user_email'))->first();

            if (!$user) {
                return back()->with('error', 'Session expired.');
            }

            $path = null;

            // FILE UPLOAD
            if ($request->hasFile('sig_file')) {
                $path = $request->file('sig_file')->store('signatures', 'public');
            }

            // BASE64 SIGNATURE
            elseif ($request->signature_data) {

                $imageName = 'sig_' . Str::random(10) . '.png';

                $data = $request->signature_data;
                $data = str_replace('data:image/png;base64,', '', $data);
                $data = str_replace(' ', '+', $data);

                Storage::disk('public')->put(
                    'signatures/' . $imageName,
                    base64_decode($data)
                );

                $path = 'signatures/' . $imageName;
            }

            if ($path) {

                // delete old signature safely
                if ($user->signature) {
                    Storage::disk('public')->delete($user->signature);
                }

                $user->update(['signature' => $path]);

                return back()->with('success', 'Digital signature updated!');
            }

            return back()->with('error', 'No signature data received.');

        } catch (\Exception $e) {
            Log::error('Signature Update Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update signature.');
        }
    }
}