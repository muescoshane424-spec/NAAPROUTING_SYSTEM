@extends('layouts.app')

@section('title','Reports')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Reports</h1>
<p class="text-gray-300">Processing time, office activity, delays, and scan frequency.</p>

<div class="tables-grid">
    <div class="table-card">
        <div class="table-header">
            <h3>Documents by Office</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Total Documents</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = $byOffice->sum('total'); @endphp
                    @foreach($byOffice as $office)
                    <tr>
                        <td>{{ $office->currentOffice ? $office->currentOffice->name : 'Unknown' }}</td>
                        <td>{{ $office->total }}</td>
                        <td>{{ $total > 0 ? round(($office->total / $total) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Daily Document Creation</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Documents Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($docTime as $day)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($day->date)->format('M j, Y') }}</td>
                        <td>{{ $day->total }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-6">
    <button onclick="window.print()" class="btn btn-primary">Print Report</button>
    <button onclick="exportToCSV()" class="btn btn-primary ml-2">Export CSV</button>
</div>

<script>
function exportToCSV() {
    // Simple CSV export
    let csv = 'Date,Documents Created\n';
    @foreach($docTime as $day)
    csv += '{{ $day->date }},{{ $day->total }}\n';
    @endforeach

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'document-report.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
