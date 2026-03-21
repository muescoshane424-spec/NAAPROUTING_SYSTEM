<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
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
        
        // Log login history (if table exists)
        if (Schema::hasTable('login_histories')) {
            App\Models\LoginHistory::create([
                'email' => $credentials['email'],
                'ip_address' => $request->ip(),
                'device' => $request->userAgent(),
                'user_agent' => $request->header('User-Agent'),
                'login_time' => now(),
            ]);
        }

        return redirect()->route('dashboard');
    }

    return back()->with('error', 'Invalid credentials.')->withInput();
})->name('login.submit');

Route::get('/logout', function () {
    // Log logout time
    $email = session('user_email', 'guest');
    $lastLogin = App\Models\LoginHistory::where('email', $email)->latest()->first();
    if ($lastLogin && !$lastLogin->logout_time) {
        $lastLogin->update(['logout_time' => now()]);
    }
    
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

// -------------------------
// Document Tracking
// -------------------------
Route::get('/track', function (Request $request) {
    $documents = App\Models\Document::with(['originOffice', 'currentOffice', 'destinationOffice', 'routings'])->latest()->paginate(20);
    return view('track', compact('documents'));
})->name('track.index');

Route::get('/track/{document}', function (App\Models\Document $document) {
    $document->load(['originOffice', 'currentOffice', 'destinationOffice', 'routings' => function ($q) {
        $q->with(['fromOffice', 'toOffice'])->latest();
    }]);
    return view('track-detail', compact('document'));
})->name('track.detail');

// -------------------------
// Login History
// -------------------------
Route::get('/login-history', function (Request $request) {
    $query = App\Models\LoginHistory::query();
    if ($request->search) {
        $query->where('email', 'like', '%'.$request->search.'%');
    }
    if ($request->from) {
        $query->whereDate('login_time', '>=', $request->from);
    }
    if ($request->to) {
        $query->whereDate('login_time', '<=', $request->to);
    }
    $logins = $query->latest('login_time')->paginate(20);
    return view('login-history', compact('logins'));
})->name('login-history');

// -------------------------
// User Profile
// -------------------------
Route::get('/profile', function () {
    $user = App\Models\User::where('email', session('user_email', ''))->first() ?? (object) [
        'name' => session('user_email', 'Guest'),
        'email' => session('user_email', ''),
        'role' => 'guest',
        'status' => 'active',
    ];
    return view('profile', compact('user'));
})->name('profile');

Route::post('/profile', function (Request $request) {
    $email = session('user_email', '');
    $user = App\Models\User::where('email', $email)->firstOrFail();
    $user->update($request->only('name', 'phone'));
    if ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);
    }
    if ($request->hasFile('signature')) {
        $path = $request->file('signature')->store('signatures', 'public');
        $user->update(['signature' => $path]);
    }
    return back()->with('success', 'Profile updated!');
})->name('profile.update');

Route::post('/profile/password', function (Request $request) {
    $request->validate(['password' => 'required|min:8|confirmed']);
    $email = session('user_email', '');
    $user = App\Models\User::where('email', $email)->firstOrFail();
    $user->update(['password' => bcrypt($request->password)]);
    return back()->with('success', 'Password updated!');
})->name('profile.password');