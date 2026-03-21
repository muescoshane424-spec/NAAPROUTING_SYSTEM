<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\LoginHistory;
use App\Models\Document;
use App\Models\User;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// -------------------------
// Public Routes (Guest)
// -------------------------
Route::get('/', function () {
    return view('login');
})->name('home');

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (!empty(trim($credentials['email'] ?? '')) && !empty(trim($credentials['password'] ?? ''))) {
        session(['authenticated' => true, 'user_email' => $credentials['email']]);

        if (Schema::hasTable('login_histories')) {
            LoginHistory::create([
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

// -------------------------
// Authenticated Routes
// -------------------------
Route::middleware(['auth.session'])->group(function () {

    Route::get('/logout', function () {
        $email = session('user_email', 'guest');
        $lastLogin = LoginHistory::where('email', $email)->latest()->first();
        if ($lastLogin && !$lastLogin->logout_time) {
            $lastLogin->update(['logout_time' => now()]);
        }
        session()->flush();
        return redirect()->route('home');
    })->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/store', [DocumentController::class, 'store'])->name('documents.store');

    // Routing
    Route::get('/routing', [RoutingController::class, 'index'])->name('routing.index');
    Route::post('/routing/{document}', [RoutingController::class, 'routeDocument'])->name('routing.route');

    // QR Scanner
    Route::get('/qr-scan', [QRController::class, 'index'])->name('qr.scan');
    Route::post('/qr-scan', [QRController::class, 'scan'])->name('qr.scan.submit');
    Route::get('/scan', function () {
        return view('scan');
    })->name('scan');

    // Track documents by tracking_id (public-safe)
    Route::get('/track/{tracking_id}', function ($tracking_id) {
        $document = Document::where('tracking_id', $tracking_id)->first();
        if ($document) {
            $document->update(['status' => 'Scanned at Station']);
            return "Document {$tracking_id} has been updated!";
        }
        return "Document not found.";
    })->name('track.update');

    // Offices
    Route::get('/offices', [OfficeController::class, 'index'])->name('offices.index');
    Route::post('/offices', [OfficeController::class, 'store'])->name('offices.store');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');

    // Activity Log
    Route::get('/activity', function () {
        $logs = \App\Models\ActivityLog::latest()->paginate(15);
        return view('activity', compact('logs'));
    })->name('activity.index');

    // Reports
    Route::get('/reports', function () {
        $byOffice = Document::select('current_office_id', \DB::raw('count(*) as total'))
            ->groupBy('current_office_id')
            ->with('currentOffice')
            ->get();

        $docTime = Document::select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return view('reports', compact('byOffice', 'docTime'));
    })->name('reports.index');

    // Document Tracking
    Route::get('/track', function () {
        $documents = Document::with(['originOffice', 'currentOffice', 'destinationOffice', 'routings'])->latest()->paginate(20);
        return view('track', compact('documents'));
    })->name('track.index');

    Route::get('/track-detail/{document}', function (Document $document) {
        $document->load(['originOffice', 'currentOffice', 'destinationOffice', 'routings.fromOffice', 'routings.toOffice']);
        return view('track-detail', compact('document'));
    })->name('track.detail');

    // Login History
    Route::get('/login-history', function (Request $request) {
        $query = LoginHistory::query();
        if ($request->search) $query->where('email', 'like', '%'.$request->search.'%');
        if ($request->from) $query->whereDate('login_time', '>=', $request->from);
        if ($request->to) $query->whereDate('login_time', '<=', $request->to);

        $logins = $query->latest('login_time')->paginate(20);
        return view('login-history', compact('logins'));
    })->name('login-history');

    // Profile
    Route::get('/profile', function () {
        $user = User::where('email', session('user_email'))->first() ?? (object) [
            'name' => session('user_email', 'Guest'),
            'email' => session('user_email', ''),
            'role' => 'guest',
            'status' => 'active',
        ];
        return view('profile', compact('user'));
    })->name('profile');

    Route::post('/profile', function (Request $request) {
        $user = User::where('email', session('user_email'))->firstOrFail();
        $user->update($request->only('name', 'phone'));

        if ($request->hasFile('avatar')) {
            $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);
        }

        if ($request->hasFile('signature')) {
            $user->update(['signature' => $request->file('signature')->store('signatures', 'public')]);
        }

        return back()->with('success', 'Profile updated!');
    })->name('profile.update');

    Route::post('/profile/password', function (Request $request) {
        $request->validate(['password' => 'required|min:8|confirmed']);
        $user = User::where('email', session('user_email'))->firstOrFail();
        $user->update(['password' => bcrypt($request->password)]);
        return back()->with('success', 'Password updated!');
    })->name('profile.password');
});