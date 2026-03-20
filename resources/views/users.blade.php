@extends('layouts.app')

@section('title','User Management')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">User Management</h1>
<p class="text-gray-300">Add, edit, and deactivate admin/staff users.</p>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert alert-warning">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="tables-grid">
    <div class="table-card">
        <div class="table-header">
            <h3>All Users</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Add New User</h3>
        </div>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary">Create User</button>
        </form>
    </div>
</div>
@endsection
