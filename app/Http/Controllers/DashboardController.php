<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Document, User, Office, ActivityLog, Department};

class DashboardController extends Controller
{
    public function index()
    {
        $isAdmin = session('user_role') === 'ADMIN';

        if ($isAdmin) {
            $stats = [
                'total'       => Document::count(),
                'in_transit'  => Document::where('status', 'In Transit')->count(),
                'completed'   => Document::where('status', 'Completed')->count(),
                'total_users' => User::count(),
            ];

            $offices = Office::withCount('documents')
                ->orderBy('documents_count', 'desc')
                ->take(5)
                ->get();

            $recentLogs = ActivityLog::latest()->take(10)->get();
            $departmentName = null;
            $recentDocs = null;
        } else {
            $userId = session('user_id');
            $departmentId = session('department_id');
            $departmentName = Department::find($departmentId)->name ?? 'My Department';

            $stats = [
                'total'           => Document::where('uploaded_by', $userId)->count(),
                'in_transit'      => Document::where('uploaded_by', $userId)->where('status', 'In Transit')->count(),
                'completed'       => Document::where('uploaded_by', $userId)->where('status', 'Completed')->count(),
                'department_users'=> User::where('department_id', $departmentId)->count(),
            ];

            $offices = Office::withCount('documents')
                ->orderBy('documents_count', 'desc')
                ->take(5)
                ->get();

            $recentDocs = Document::where('uploaded_by', $userId)
                ->latest()
                ->take(10)
                ->get();
            $recentLogs = null;
        }

        $flowData = [];
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('D');
            $flowData[] = Document::whereDate('created_at', $date->toDateString())
                ->when(!$isAdmin, fn($query) => $query->where('uploaded_by', session('user_id')))
                ->count();
        }

        $totalDocuments = $stats['total'] ?? 0;

        return view('dashboard', compact('stats', 'flowData', 'days', 'offices', 'recentLogs', 'recentDocs', 'departmentName', 'isAdmin', 'totalDocuments'));
    }

    public function notifications(Request $request)
    {
        $isAdmin = session('user_role') === 'ADMIN';

        $notifications = ActivityLog::latest()
            ->when(!$isAdmin, fn($query) => $query->where('user', session('user_name')))
            ->take(10)
            ->get()
            ->map(fn($log) => [
                'title' => $log->action,
                'time' => $log->created_at->diffForHumans(),
                'details' => $log->meta ? json_decode($log->meta, true) : null,
            ]);

        $unreadCount = $notifications->count();

        return response()->json([
            'count' => $unreadCount,
            'items' => $notifications,
        ]);
    }
}