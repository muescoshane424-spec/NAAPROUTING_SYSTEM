<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// -------------------------
// Login
// -------------------------
Route::get('/', function () {
    return view('login');
})->name('home');

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    // Basic placeholder auth (accept any non-empty values)
    if (trim($credentials['email'] ?? '') !== '' && trim($credentials['password'] ?? '') !== '') {
        session(['authenticated' => true, 'user_email' => $credentials['email']]);
        return redirect()->route('dashboard');
    }

    return back()->with('error', 'Invalid credentials.')->withInput();
})->name('login.submit');

Route::get('/logout', function () {
    session()->flush();
    return redirect()->route('home');
})->name('logout');

// Middleware helper
$authenticated = function () {
    if (!session('authenticated', false)) {
        return redirect()->route('home');
    }
    return null;
};

// -------------------------
// Dashboard
// -------------------------
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// -------------------------
// Documents
// -------------------------
Route::get('/documents', [App\Http\Controllers\DocumentController::class, 'index'])->name('documents.index');
Route::get('/documents/create', [App\Http\Controllers\DocumentController::class, 'create'])->name('documents.create');
Route::post('/documents/store', [App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');

// -------------------------
// Routing
// -------------------------
Route::get('/routing', [App\Http\Controllers\RoutingController::class, 'index'])->name('routing.index');
Route::post('/routing/{document}', [App\Http\Controllers\RoutingController::class, 'routeDocument'])->name('routing.route');

// -------------------------
// QR Scanner
// -------------------------
Route::get('/qr-scan', [App\Http\Controllers\QRController::class, 'index'])->name('qr.scan');
Route::post('/qr-scan', [App\Http\Controllers\QRController::class, 'scan'])->name('qr.scan.submit');

// -------------------------
// Offices
// -------------------------
Route::get('/offices', [App\Http\Controllers\OfficeController::class, 'index'])->name('offices.index');
Route::post('/offices', [App\Http\Controllers\OfficeController::class, 'store'])->name('offices.store');

// -------------------------
// Users
// -------------------------
Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');

// -------------------------
// Activity Log
// -------------------------
Route::get('/activity', function () {
    $logs = App\Models\ActivityLog::latest()->paginate(15);
    return view('activity', compact('logs')); 
})->name('activity.index');

// -------------------------
// Reports
// -------------------------
Route::get('/reports', function () {
    $byOffice = App\Models\Document::select('current_office_id', \DB::raw('count(*) as total'))->groupBy('current_office_id')->with('currentOffice')->get();
    $docTime = App\Models\Document::select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(*) as total'))->groupBy('date')->orderBy('date', 'desc')->limit(10)->get();
    return view('reports', compact('byOffice', 'docTime')); 
})->name('reports.index');