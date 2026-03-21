<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Office;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->get('authenticated', false)) {
            return redirect()->route('home');
        }

        // Real Stats for KPI cards
        $stats = [
            'total' => Document::count(),
            'in_transit' => Document::where('status', 'in_transit')->count(),
            'completed' => Document::where('status', 'completed')->count(),
            'pending' => Document::where('status', 'pending')->count(),
        ];

        // Weekly Trends for the Line Chart
        $weeklyTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyTrends[] = [
                'label' => $date->format('D'),
                'count' => Document::whereDate('created_at', $date->toDateString())->count()
            ];
        }

        // Office Performance for the Horizontal Bar Chart
        $officePerformance = Office::select('offices.name')
            ->join('documents', 'offices.id', '=', 'documents.current_office_id')
            ->selectRaw('COUNT(documents.id) as doc_count')
            ->groupBy('offices.id', 'offices.name')
            ->orderBy('doc_count', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'weeklyTrends', 'officePerformance'));
    }
}