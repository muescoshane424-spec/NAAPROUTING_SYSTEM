<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Document, User, Office, ActivityLog};

class DashboardController extends Controller
{
    public function index()
    {
        // Security Gate: Ensure only ADMIN enters
        if (session('user_role') !== 'ADMIN') {
            return redirect()->route('home')->with('error', 'Unauthorized Access.');
        }

        // 1. KPI Stats
        $stats = [
            'total'       => Document::count(),
            'in_transit'  => Document::where('status', 'In Transit')->count(),
            'completed'   => Document::where('status', 'Completed')->count(),
            'total_users' => User::count(),
        ];

        // 2. 7-Day Activity Flow (Line Chart)
        $flowData = [];
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('D');
            $flowData[] = Document::whereDate('created_at', $date->toDateString())->count();
        }

        // 3. Office Load (Bar Chart)
        $offices = Office::withCount('documents')
            ->orderBy('documents_count', 'desc')
            ->take(5)
            ->get();

        // 4. Recent Logs
        $recentLogs = ActivityLog::latest()->take(10)->get();

        return view('dashboard', compact('stats', 'flowData', 'days', 'offices', 'recentLogs'));
    }
}