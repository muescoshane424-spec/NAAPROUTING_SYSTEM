<?php

use Illuminate\Support\Facades\Route;

// Root redirects to login
Route::get('/', function () {
    return view('login'); // your login.blade.php
})->name('login');

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard'); // create a dashboard.blade.php
})->name('dashboard');

// Documents
Route::get('/documents', function () {
    return view('documents.index'); // views/documents/index.blade.php
})->name('documents.index');
Route::get('/documents/create', function () {
    return view('documents.create'); // views/documents/create.blade.php
})->name('documents.create');
Route::post('/documents/store', function () {
    return redirect()->route('documents.index'); // just redirect for now
})->name('documents.store');

// Routing
Route::get('/routing', function () {
    return view('routing'); // views/routing.blade.php
})->name('routing.index');

// QR Scanner
Route::get('/qr-scan', function () {
    return view('qr_scan'); // views/qr.blade.php
})->name('qr.scan');

// Offices
Route::get('/offices', function () {
    return view('offices'); // views/offices.blade.php
})->name('offices.index');

// Users
Route::get('/users', function () {
    return view('users'); // views/users.blade.php
})->name('users.index');

// Activity Log
Route::get('/activity', function () {
    return view('activity'); // views/activity.blade.php
})->name('activity.index');

// Reports
Route::get('/reports', function () {
    return view('reports'); // views/reports.blade.php
})->name('reports.index');