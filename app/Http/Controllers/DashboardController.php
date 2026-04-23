<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Document, User, Office, ActivityLog, Department};
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            $isAdmin = $user?->role === 'ADMIN';
            $userId = $user?->id;

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
                $recentDocs = null;
                $departmentName = null;

            } else {

                $departmentId = $user?->department_id;

                $departmentName = Department::find($departmentId)?->name ?? 'My Department';

                $stats = [
                    'total' => Document::where('uploaded_by', $userId)->count(),
                    'in_transit' => Document::where('uploaded_by', $userId)
                        ->where('status', 'In Transit')
                        ->count(),
                    'completed' => Document::where('uploaded_by', $userId)
                        ->where('status', 'Completed')
                        ->count(),
                    'department_users' => User::where('department_id', $departmentId)->count(),
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

            // CHART DATA (last 7 days)
            $flowData = [];
            $days = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);

                $days[] = $date->format('D');

                $flowData[] = Document::whereDate('created_at', $date->toDateString())
                    ->when(!$isAdmin, function ($query) use ($userId) {
                        return $query->where('uploaded_by', $userId);
                    })
                    ->count();
            }

            $totalDocuments = $stats['total'] ?? 0;

            return view('dashboard', compact(
                'stats',
                'flowData',
                'days',
                'offices',
                'recentLogs',
                'recentDocs',
                'departmentName',
                'isAdmin',
                'totalDocuments'
            ));

        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());

            return back()->with('error', 'Unable to load dashboard. Please try again.');
        }
    }

    public function notifications(Request $request)
    {
        try {
            $user = auth()->user();
            $isAdmin = $user?->role === 'ADMIN';

            $notifications = ActivityLog::latest()
                ->when(!$isAdmin, function ($query) use ($user) {
                    return $query->where('user', $user?->name);
                })
                ->take(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'title'   => $log->action,
                        'message' => $log->action,
                        'time'    => $log->created_at->diffForHumans(),
                        'details' => json_decode($log->meta ?? '', true),
                    ];
                });

            $unreadCount = $notifications->count();

            return response()->json([
                'count' => $unreadCount,
                'items' => $notifications,
            ]);

        } catch (\Exception $e) {
            Log::error('Notification Error: ' . $e->getMessage());

            return response()->json([
                'count' => 0,
                'items' => [],
                'error' => 'Failed to load notifications'
            ], 500);
        }
    }
}