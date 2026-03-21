<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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

        // Basic stats
        $totalDocs = Document::count();
        $inTransit = Document::where('status', 'in_transit')->count();
        $completed = Document::where('status', 'completed')->count();
        $pending = Document::where('status', 'pending')->count();

        $stats = [
            'total' => $totalDocs,
            'in_transit' => $inTransit,
            'completed' => $completed,
            'pending' => $pending,
            'completion_rate' => $totalDocs > 0 ? round(($completed / $totalDocs) * 100, 1) : 0,
            'avg_processing_days' => $this->getAvgProcessingDays(),
        ];

        // Documents by office
        $docsByOffice = Office::withCount(['originDocuments', 'currentDocuments'])->get()->map(function($office) {
            return [
                'name' => $office->name,
                'origin' => $office->origin_documents_count,
                'current' => $office->current_documents_count,
            ];
        });

        // Monthly, weekly, and daily trends
        $monthlyTrends = $this->getMonthlyTrends();
        $weeklyTrends = $this->getWeeklyTrends();
        $dailyTrends = $this->getDailyTrends();

        // Status distribution
        $statusDistribution = [
            'completed' => $completed,
            'in_transit' => $inTransit,
            'pending' => $pending,
        ];

        // Priority distribution
        $priorityData = Document::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Recent activity with user breakdown
        $activity = ActivityLog::with('document')->latest()->limit(10)->get();

        // Top performing offices
        $officePerformance = Office::select('offices.name')
            ->join('documents', 'offices.id', '=', 'documents.current_office_id')
            ->selectRaw('offices.name, COUNT(documents.id) as doc_count')
            ->groupBy('offices.id', 'offices.name')
            ->orderBy('doc_count', 'desc')
            ->limit(5)
            ->get();

        // Document flow timeline (simulated hourly data for today)
        $flowTimeline = $this->getFlowTimeline();

        // KPIs
        $kpis = [
            'total_processed_today' => Document::whereDate('updated_at', today())->count(),
            'avg_completion_time' => $stats['avg_processing_days'] . ' days',
            'system_uptime' => '99.9%', // simulated
            'active_routes' => $inTransit,
        ];

        // Simulated system health metrics for a real-time status chart
        $systemHealth = [
            ['label' => 'CPU', 'value' => 78],
            ['label' => 'Memory', 'value' => 64],
            ['label' => 'Disk', 'value' => 82],
            ['label' => 'Network', 'value' => 46],
        ];

        return view('dashboard', compact(
            'stats', 'docsByOffice', 'monthlyTrends', 'weeklyTrends', 'dailyTrends', 'statusDistribution',
            'priorityData', 'activity', 'officePerformance', 'flowTimeline', 'kpis', 'systemHealth'
        ));
    }

    private function getAvgProcessingDays()
    {
        $completed = Document::where('status', 'completed')
            ->whereNotNull('created_at')
            ->get();

        if ($completed->isEmpty()) return 0;

        $totalDays = $completed->sum(function($doc) {
            return $doc->created_at->diffInDays($doc->updated_at);
        });

        return round($totalDays / $completed->count(), 1);
    }

    private function getMonthlyTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('M Y');
            $count = Document::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $trends[] = ['month' => $month, 'count' => $count];
        }
        return $trends;
    }

    private function getWeeklyTrends()
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trends[] = [
                'label' => $date->format('D'),
                'count' => Document::whereDate('created_at', $date->toDateString())->count(),
            ];
        }
        return $trends;
    }

    private function getDailyTrends()
    {
        $trends = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $trends[] = [
                'label' => sprintf('%02d:00', $hour),
                'count' => Document::whereDate('created_at', today())
                    ->whereHour('created_at', $hour)
                    ->count(),
            ];
        }
        return $trends;
    }

    private function getFlowTimeline()
    {
        $timeline = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $timeline[] = [
                'hour' => sprintf('%02d:00', $hour),
                'processed' => Document::whereDate('updated_at', today())
                    ->whereHour('updated_at', $hour)
                    ->count(),
            ];
        }
        return $timeline;
    }
}
