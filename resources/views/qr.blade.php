@extends('layouts.app')

@section('title','QR Scanner')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">QR Scanner</h1>

@if(isset($status))
@if($status == 'found')
<div class="alert alert-success">Document found and scanned: {{ $document->title }}</div>
@else
<div class="alert alert-warning">Document not found with that QR code.</div>
@endif
@endif

<div class="panels">
    <article class="panel">
        <h2>Scan QR Code</h2>
        <form method="POST" action="{{ route('qr.scan') }}">
            @csrf
            <div class="form-group">
                <label for="qr">QR Code</label>
                <input type="text" name="qr" id="qr" placeholder="Enter QR code" required>
            </div>
            <button type="submit" class="btn btn-primary">Scan</button>
        </form>
    </article>

    <article class="panel">
        <h2>Recent Documents</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>QR Code</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                <tr>
                    <td>{{ $doc->title }}</td>
                    <td>{{ $doc->qr_code }}</td>
                    <td><span class="badge {{ $doc->status == 'completed' ? 'success' : 'info' }}">{{ ucfirst($doc->status) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </article>
</div>

@endsection