@extends('layouts.app')

@section('title','QR Scanner')

@section('head')
<script src="https://unpkg.com/html5-qrcode"></script>
@endsection

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">QR Code Scanner</h1>

@if(isset($status))
@if($status == 'found')
<div class="alert alert-success">
    <strong>✓ Document Found!</strong>
    <p>{{ $document->title }}</p>
    <p>Current Location: {{ $document->currentOffice?->name ?? 'Unknown' }}</p>
    <p>Status: {{ ucfirst($document->status) }}</p>
</div>
@else
<div class="alert alert-warning">✗ Document not found with that QR code.</div>
@endif
@endif

<div class="charts-grid">
    <div class="chart-card large">
        <div class="chart-header">
            <h3>Live QR Scanner</h3>
        </div>
        <div id="qr-reader" style="width: 100%; height: 400px; border-radius: 12px; overflow: hidden;"></div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3>Manual Entry</h3>
        </div>
        <form method="POST" action="{{ route('qr.scan.submit') }}">
            @csrf
            <div class="form-group">
                <label for="qr">QR Code / Document ID</label>
                <input type="text" name="qr" id="qr" placeholder="Scan or enter QR code" autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3>Recent Scans</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Last Scanned</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                    <tr>
                        <td>{{ $doc->title }}</td>
                        <td><span class="badge {{ $doc->status == 'completed' ? 'success' : 'info' }}">{{ ucfirst($doc->status) }}</span></td>
                        <td>{{ $doc->updated_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function onScanSuccess(decodedText, decodedResult) {
    console.log(`Code matched = ${decodedText}`, decodedResult);
    document.getElementById('qr').value = decodedText;
    // Auto-submit the form
    document.querySelector('form').submit();
}

function onScanError(errorMessage) {
    // Error handling
}

const html5QrcodeScanner = new Html5QrcodeScanner(
    "qr-reader",
    { fps: 10, qrbox: 250 },
    false
);

html5QrcodeScanner.render(onScanSuccess, onScanError);
</script>
@endsection