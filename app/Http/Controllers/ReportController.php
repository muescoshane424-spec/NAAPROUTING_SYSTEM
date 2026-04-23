<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Office;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index()
    {
        try {
            $this->authorizeAdmin();

            // SUMMARY STATS
            $summary = [
                'total_processed' => Document::count(),

                'avg_time' => round(
                    Document::whereNotNull('updated_at')
                        ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, updated_at)')),
                    1
                ) ?? 0,

                'most_active' => Office::withCount('documents')
                    ->orderBy('documents_count', 'desc')
                    ->first()?->name ?? 'N/A',

                'qr_scans' => DB::table('activity_logs')->count()
            ];

            // OFFICE CHART DATA
            $offices = Office::withCount('documents')->get();
            $officeNames = $offices->pluck('name')->toArray();
            $processingTimes = $offices->pluck('documents_count')->toArray();

            // SCAN HISTORY (LAST 5 DAYS)
            $scans = DB::table('activity_logs')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->take(5)
                ->get()
                ->reverse();

            $days = $scans->map(function ($s) {
                return date('D', strtotime($s->date));
            })->toArray();

            $scanCounts = $scans->pluck('count')->toArray();

            // WEEKLY FLOW DATA (STATIC)
            $flowLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $flowData = [12, 19, 3, 5, 2, 3, 9];

            return view('reports', compact(
                'officeNames',
                'processingTimes',
                'days',
                'scanCounts',
                'flowLabels',
                'flowData',
                'summary'
            ));

        } catch (\Exception $e) {
            Log::error('Report Index Error: ' . $e->getMessage());

            return back()->with('error', 'Failed to load report data.');
        }
    }

    public function exportCSV()
    {
        try {
            $this->authorizeAdmin();

            $fileName = 'system_report_' . date('Y-m-d') . '.csv';

            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0",
            ];

            return response()->stream(function () {

                $file = fopen('php://output', 'w');

                fputcsv($file, ['Metric', 'Value', 'Date']);
                fputcsv($file, ['Total Documents', Document::count(), date('Y-m-d')]);
                fputcsv($file, []);
                fputcsv($file, ['Doc ID', 'Title', 'Status', 'Office']);

                Document::with('office')->chunk(100, function ($docs) use ($file) {
                    foreach ($docs as $doc) {
                        fputcsv($file, [
                            $doc->id,
                            $doc->title,
                            $doc->status,
                            $doc->office?->name ?? 'Unassigned'
                        ]);
                    }
                });

                fclose($file);

            }, 200, $headers);

        } catch (\Exception $e) {
            Log::error('CSV Export Error: ' . $e->getMessage());

            return back()->with('error', 'Failed to export CSV file.');
        }
    }

    protected function authorizeAdmin()
    {
        if (session('user_role') !== 'ADMIN') {
            abort(403, 'Administrator privileges are required to access this page.');
        }
    }
}