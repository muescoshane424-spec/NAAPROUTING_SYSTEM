<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;

// --- Public Routes ---
// The 'guest' logic: Ensure users aren't redirected back here if they are already logged in
Route::get('/', function() {
    if (session()->has('user_id')) {
        return redirect()->route('dashboard');
    }
    return view('login');
})->name('home');

Route::get('/login', function() {
    if (session()->has('user_id')) {
        return redirect()->route('dashboard');
    }
    return view('login');
})->name('login');

Route::post('/login', [UserController::class, 'login'])->name('login.submit');

// 2FA Routes
Route::get('/2fa/verify', [UserController::class, 'show2FAVerify'])->name('2fa.verify');
Route::post('/2fa/verify', [UserController::class, 'verify2FA'])->name('2fa.verify.submit');

// --- Authenticated Admin Routes ---
Route::middleware([\App\Http\Middleware\EnsureAuthenticated::class])->group(function () {

    Route::get('/logout', function () {
        session()->flush();
        Auth::logout(); // Ensure Laravel Auth is also cleared
        return redirect()->route('home')->with('success', 'Logged out successfully.');
    })->name('logout');

    Route::get('/register', [UserController::class, 'create'])->name('register');

    // --- Core Admin Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/notifications', [DashboardController::class, 'notifications'])->name('api.notifications');

    // --- Resource Management (Users & Offices) ---
    Route::resource('users', UserController::class);
    Route::resource('offices', OfficeController::class);

    // --- API Endpoints for Departments ---
    Route::get('/api/departments/{name}/offices', [OfficeController::class, 'getDepartmentOffices']);
    Route::get('/api/departments/{name}/users', [OfficeController::class, 'getDepartmentUsers']);
    Route::post('/api/departments/rename', [OfficeController::class, 'renameDepartment']);
    Route::get('/api/offices/{id}/staff', [OfficeController::class, 'getOfficeStaff']);
    Route::get('/api/departments/{department}/staff', [OfficeController::class, 'getDepartmentStaff']);

    // --- Document Management & Tracking ---
    Route::resource('documents', DocumentController::class);
    Route::get('/track', [DocumentController::class, 'trackIndex'])->name('track.index');
    Route::get('/track/{id}', [DocumentController::class, 'show'])->name('track.detail');
    Route::get('/api/documents/{id}/status', [DocumentController::class, 'checkStatus'])->name('documents.status');
    Route::get('/activity', [DocumentController::class, 'activityIndex'])->name('activity.index');

    // --- Routing & QR System ---
    Route::get('/routing', [RoutingController::class, 'index'])->name('routing.index');
    Route::post('/routing/update/{id}', [RoutingController::class, 'routeDocument'])->name('routing.route');
    Route::get('/scan-qr', [QRController::class, 'index'])->name('qr.index');
    Route::post('/scan-qr/process', [QRController::class, 'scan'])->name('qr.scan');
    Route::post('/scan-qr/store', [QRController::class, 'store'])->name('qr.store');

    // --- Reports & Analytics ---
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'exportCSV'])->name('reports.export');

    // --- System Settings ---
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/update', [SettingsController::class, 'update'])->name('settings.update');

    // --- Profile Management ---
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'updateInfo'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature');
});