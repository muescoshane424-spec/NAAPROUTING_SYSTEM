@extends('layouts.app')

@section('title','Office Management')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Office Management</h1>
<p class="text-gray-300">Manage office locations, departments, and status.</p>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="tables-grid">
    <div class="table-card">
        <div class="table-header">
            <h3>All Offices</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Head</th>
                        <th>Contact</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offices as $office)
                    <tr>
                        <td>{{ $office->name }}</td>
                        <td>{{ $office->department ?? 'N/A' }}</td>
                        <td>{{ $office->head ?? 'N/A' }}</td>
                        <td>{{ $office->contact ?? 'N/A' }}</td>
                        <td><span class="badge {{ $office->status == 'active' ? 'success' : 'warning' }}">{{ ucfirst($office->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $offices->links() }}
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Add New Office</h3>
        </div>
        <form method="POST" action="{{ route('offices.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Office Name</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" name="department" id="department">
            </div>
            <div class="form-group">
                <label for="head">Head</label>
                <input type="text" name="head" id="head">
            </div>
            <div class="form-group">
                <label for="contact">Contact</label>
                <input type="text" name="contact" id="contact">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Office</button>
        </form>
    </div>
</div>
@endsection
