@extends('layouts.app')

@section('title','New Document Routing')

@section('content')
<div class="container-fluid">

    <h2 class="mb-4 text-white">New Document Routing</h2>

    <div class="row g-4">

        <!-- Document Information -->
        <div class="col-md-6">
            <div class="card p-4" style="background:#161e31;border:1px solid rgba(255,255,255,0.08);">

                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label text-white">Document Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Document Type</label>
                        <input type="text" name="type" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Priority Level *</label>
                        <div class="d-flex gap-2">
                            @foreach(['Low','Medium','High'] as $p)
                                <button type="button" class="btn btn-outline-secondary priority-btn">{{ $p }}</button>
                                <input type="radio" name="priority" value="{{ $p }}" hidden {{ $p == 'Medium' ? 'checked' : '' }}>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">SLA</label>
                        <select name="sla" class="form-control @error('sla') is-invalid @enderror" required>
                            <option value="" disabled {{ old('sla') ? '' : 'selected' }}>Select SLA</option>
                            <option value="Standard" {{ old('sla') === 'Standard' ? 'selected' : '' }}>Standard</option>
                            <option value="Expedited" {{ old('sla') === 'Expedited' ? 'selected' : '' }}>Expedited</option>
                            <option value="Critical" {{ old('sla') === 'Critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                        @error('sla')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-secondary">SLA determines due date, alerts, and risk status.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Upload File *</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>

                    <h6 class="text-white mt-4">Routing Information</h6>
                    <div class="mb-3">
                        <label class="form-label text-white">Origin Office *</label>
                        <select name="origin_office_id" class="form-control" required>
                            <option value="" disabled selected>Select origin office</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id ?? $office }}">{{ $office->name ?? $office }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Destination Office *</label>
                        <select name="destination_office_id" class="form-control" required>
                            <option value="" disabled selected>Select destination office</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id ?? $office }}">{{ $office->name ?? $office }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Receiver User *</label>
                        <select name="receiver_user_id" class="form-control" required>
                            <option value="" disabled selected>Select receiver</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} @if($user->department)({{ $user->department->name }})@endif</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-primary w-100 mt-3">Route Document</button>
                </form>

            </div>
        </div>

        <!-- QR Scanner & Routing Flow -->
        <div class="col-md-6 d-flex flex-column gap-4">

            <div class="card p-4 text-center" style="background:#161e31;border:1px solid rgba(255,255,255,0.08);">
                <h6 class="text-white">QR Scanner</h6>
                <video id="qr-video" style="width:100%;height:250px;background:#0b1228;border-radius:10px;"></video>
                <button id="start-camera" class="btn btn-info mt-3 w-100">Start Camera</button>
            </div>

            <div class="card p-4" style="background:#161e31;border:1px solid rgba(255,255,255,0.08);">
                <h6 class="text-white">Routing Flow</h6>
                <ol class="text-white ms-3 mt-2">
                    <li>Document Created (Origin Office)</li>
                    <li>In Transit</li>
                    <li>Received (Destination)</li>
                </ol>
            </div>

        </div>

    </div>

</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
document.querySelectorAll('.priority-btn').forEach((btn,i)=>{
    btn.addEventListener('click',()=>{
        document.querySelectorAll('input[name="priority"]')[i].checked = true;
        document.querySelectorAll('.priority-btn').forEach(b=>b.classList.remove('btn-warning'));
        btn.classList.add('btn-warning');
    });
});

let cameraStarted = false;
document.getElementById('start-camera').addEventListener('click', ()=>{
    if(cameraStarted) return;
    cameraStarted = true;

    const html5QrCode = new Html5Qrcode("qr-video");

    Html5Qrcode.getCameras().then(cameras => {
        if(cameras && cameras.length) {
            html5QrCode.start(
                cameras[0].id,
                { fps: 10, qrbox: 250 },
                qrCodeMessage => { alert("QR Code scanned: " + qrCodeMessage); },
                errorMessage => {}
            );
        }
    }).catch(err => console.error(err));
});
</script>
@endsection