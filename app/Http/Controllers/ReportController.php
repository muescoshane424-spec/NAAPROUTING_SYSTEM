<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Office;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();

        // Summary Stats
        $summary = [
            'total_processed' => Document::count(),
            'avg_time' => round(Document::whereNotNull('updated_at')
                            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, updated_at)')), 1) ?? 0,
            'most_active' => Office::withCount('documents')
                            ->orderBy('documents_count', 'desc')
                            ->first()->name ?? 'N/A',
            'qr_scans' => DB::table('activity_logs')->count() 
        ];

        // Chart Data: Offices
        $offices = Office::withCount('documents')->get();
        $officeNames = $offices->pluck('name')->toArray();
        $processingTimes = $offices->pluck('documents_count')->toArray();

        // Chart Data: Scan History (Last 5 days)
        $scans = DB::table('activity_logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')->orderBy('date', 'desc')->take(5)->get()->reverse();

        $days = $scans->map(fn($s) => date('D', strtotime($s->date)))->toArray();
        $scanCounts = $scans->pluck('count')->toArray();

        // Weekly Flow Data (Example Static Data for Charting)
        $flowLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $flowData = [12, 19, 3, 5, 2, 3, 9]; 

        return view('reports', compact('officeNames', 'processingTimes', 'days', 'scanCounts', 'flowLabels', 'flowData', 'summary'));
    }

    public function exportCSV()
    {
        $this->authorizeAdmin();

        $fileName = 'system_report_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        return response()->stream(function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Metric', 'Value', 'Date']);
            fputcsv($file, ['Total Documents', Document::count(), date('Y-m-d')]);
            fputcsv($file, []); 
            fputcsv($file, ['Doc ID', 'Title', 'Status', 'Office']);

            // Chunking prevents memory overload for large datasets
            Document::with('office')->chunk(100, function($docs) use ($file) {
                foreach ($docs as $doc) {
                    fputcsv($file, [
                        $doc->id, 
                        $doc->title, 
                        $doc->status, 
                        // Fixes ERR_INVALID_RESPONSE if office is missing
                        $doc->office->name ?? 'Unassigned' 
                    ]);
                }
            });
            fclose($file);
        }, 200, $headers);
    }

    protected function authorizeAdmin()
    {
        if (session('user_role') !== 'ADMIN') {
            abort(403, 'Administrator privileges are required to access this page.');
        }
    }
}
