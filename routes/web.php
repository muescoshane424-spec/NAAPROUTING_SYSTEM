<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController, DocumentController, RoutingController,
    QRController, OfficeController, UserController, ReportController,
    SettingsController, ProfileController
};

// --- Public Routes ---
// The 'guest' logic: Ensure users aren't redirected back here if they are already logged in
Route::get('/', function() {
    if (session()->has('user_id')) {
        return redirect()->route('dashboard');
    }
    return view('login');
})->name('home');

Route::post('/login', [UserController::class, 'login'])->name('login.submit');

// --- Authenticated Admin Routes ---
// Removed 'role:ADMIN' middleware to fix "Page Expired" / Redirect loops
Route::middleware(['auth.session'])->group(function () {

    Route::get('/logout', function () {
        session()->flush();
        Auth::logout(); // Ensure Laravel Auth is also cleared
        return redirect()->route('home')->with('success', 'Logged out successfully.');
    })->name('logout');

    // --- Core Admin Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Resource Management (Users & Offices) ---
    Route::resource('users', UserController::class);
    Route::resource('offices', OfficeController::class);

    // --- Document Management & Tracking ---
    Route::resource('documents', DocumentController::class);
    Route::get('/track', [DocumentController::class, 'trackIndex'])->name('track.index');
    Route::get('/track/{id}', [DocumentController::class, 'show'])->name('track.detail'); 
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