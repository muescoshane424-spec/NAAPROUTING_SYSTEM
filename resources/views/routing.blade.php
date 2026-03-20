@extends('layouts.app')

@section('title','Document Routing')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Document Routing</h1>
<p class="text-gray-300">Route documents between offices and track their progress.</p>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panels">
    <article class="panel">
        <h2>Documents in Transit</h2>
        <table class="w-full">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Current Office</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                <tr>
                    <td>{{ $doc->title }}</td>
                    <td>{{ $doc->currentOffice->name ?? 'N/A' }}</td>
                    <td><span class="badge {{ $doc->status == 'completed' ? 'success' : ($doc->status == 'in_transit' ? 'info' : 'warn') }}">{{ ucfirst(str_replace('_', ' ', $doc->status)) }}</span></td>
                    <td>
                        @if($doc->status == 'in_transit')
                        <button onclick="openRouteModal({{ $doc->id }}, '{{ $doc->title }}')" class="btn btn-primary">Route</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </article>
</div>

<!-- Route Modal -->
<div id="routeModal" class="modal hidden">
    <div class="modal-content">
        <span class="close" onclick="closeRouteModal()">&times;</span>
        <h2>Route Document</h2>
        <form id="routeForm" method="POST" action="{{ route('routing.route') }}">
            @csrf
            <input type="hidden" name="document_id" id="documentId">
            <div class="form-group">
                <label for="next_office_id">Next Office</label>
                <select name="next_office_id" id="next_office_id" required>
                    @foreach($offices as $office)
                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Route Document</button>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openRouteModal(docId, title) {
    document.getElementById('routeModal').classList.remove('hidden');
    document.getElementById('documentId').value = docId;
    document.querySelector('#routeModal h2').textContent = 'Route: ' + title;
}

function closeRouteModal() {
    document.getElementById('routeModal').classList.add('hidden');
}
</script>
@endsection
