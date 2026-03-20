<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(12);
        return view('users', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => bcrypt($data['password'])]);

        ActivityLog::create([ 'user' => session('user_email', 'admin'), 'action' => 'Created user', 'document_id' => null, 'ip' => $request->ip(), 'meta' => ['created_user_id' => $user->id] ]);

        return redirect()->route('users.index')->with('success', 'User created.');
    }
}
