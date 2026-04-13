@extends('layouts.app')
@section('title', 'QR Scanner & Document Delivery')

@section('head')
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/3.0.0-beta.4/signature_pad.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --glass-bg: rgba(30, 41, 59, 0.8);
        --panel-border: rgba(0, 215, 255, 0.15);
        --accent-cyan: #00d7ff;
        --accent-success: #10b981;
        --accent-warning: #f59e0b;
        --accent-danger: #ef4444;
    }

    body {
        background: #0f172a;
    }

    .glass-card {
        background: var(--glass-bg);
        border: 1px solid var(--panel-border);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    .glass-card h5 {
        color: var(--accent-cyan);
        font-weight: 700;
        font-size: 1.25rem;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .scanner-box {
        border: 2px solid var(--panel-border);
        border-radius: 16px;
        min-height: 350px;
        display: grid;
        place-items: center;
        background: rgba(15, 23, 42, 0.6);
        overflow: hidden;
        position: relative;
    }

    #reader {
        width: 100%;
        height: 100%;
    }

    .scanner-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .scanner-icon {
        font-size: 3rem;
        opacity: 0.7;
    }

    .scanner-text {
        color: #94a3b8;
        font-size: 0.9rem;
        text-align: center;
    }

    .btn-scan {
        background: linear-gradient(135deg, var(--accent-cyan), #0891b2);
        border: none;
        color: #000;
        font-weight: 700;
        padding: 12px 24px;
        border-radius: 12px;
        transition: all 0.3s ease;
        width: 100%;
        margin-top: 16px;
    }

    .btn-scan:hover {
        background: linear-gradient(135deg, #00e5ff, var(--accent-cyan));
        box-shadow: 0 0 20px rgba(0, 215, 255, 0.4);
        transform: translateY(-2px);
    }

    .btn-scan:active {
        transform: translateY(0);
    }

    .signature-pad-container {
        border: 2px solid var(--panel-border);
        border-radius: 12px;
        background: rgba(0, 0, 0, 0.3);
        overflow: hidden;
        margin-bottom: 12px;
    }

    #signature {
        border-radius: 10px;
        cursor: crosshair;
        display: block;
        background: white;
    }

    .signature-btns {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .signature-btns button {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-clear-sig {
        background: rgba(239, 68, 68, 0.2);
        color: var(--accent-danger);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .btn-clear-sig:hover {
        background: rgba(239, 68, 68, 0.3);
    }

    .btn-submit-sig {
        background: linear-gradient(135deg, var(--accent-success), #059669);
        color: white;
        border: none;
    }

    .btn-submit-sig:hover {
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
    }

    .alert-custom {
        border-radius: 12px;
        border: none;
        padding: 16px;
        margin-bottom: 20px;
    }

    .alert-success-custom {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        border-left: 4px solid #10b981;
    }

    .alert-danger-custom {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border-left: 4px solid #ef4444;
    }

    .alert-info-custom {
        background: rgba(0, 215, 255, 0.15);
        color: var(--accent-cyan);
        border-left: 4px solid var(--accent-cyan);
    }

    .document-info {
        background: rgba(0, 215, 255, 0.08);
        border: 1px solid var(--panel-border);
        border-radius: 12px;
        padding: 16px;
        margin-top: 12px;
    }

    .document-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid rgba(0, 215, 255, 0.1);
    }

    .document-info-row:last-child {
        border-bottom: none;
    }

    .document-info-label {
        color: #94a3b8;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .document-info-value {
        color: white;
        font-weight: 500;
    }

    .badge-custom {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-success {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .badge-pending {
        background: rgba(245, 158, 11, 0.2);
        color: var(--accent-warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .routing-flow {
        position: relative;
        padding: 20px 0;
    }

    .step {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        position: relative;
    }

    .step:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 18px;
        top: 32px;
        width: 2px;
        height: 40px;
        background: var(--panel-border);
    }

    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #000;
        margin-right: 16px;
        z-index: 2;
        position: relative;
    }

    .step-1 { background: var(--accent-cyan); }
    .step-2 { background: var(--accent-warning); }
    .step-3 { background: var(--accent-success); }

    .step-content h6 {
        color: white;
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0 0 4px 0;
    }

    .step-content p {
        color: #94a3b8;
        font-size: 0.85rem;
        margin: 0;
    }

    @media (max-width: 768px) {
        .glass-card {
            padding: 16px;
        }

        .scanner-box {
            min-height: 250px;
        }

        h5 {
            font-size: 1rem;
        }
    }

    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(0, 215, 255, 0.3);
        border-top-color: var(--accent-cyan);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-custom alert-success-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-custom alert-danger-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Scanner -->
        <div class="col-lg-7">
            <div class="glass-card">
                <h5><i class="fas fa-qrcode"></i> QR Code Scanner</h5>
                <p style="color: #94a3b8; margin: 0 0 16px 0;">Scan document QR codes to track and receive documents</p>
                
                <div style="display: flex; gap: 10px; margin-bottom: 16px;">
                    <button type="button" id="tab-scan" class="btn flex-grow-1" style="background: var(--accent-cyan); color: #000; font-weight: 600; border-radius: 10px;">
                        <i class="fas fa-camera me-2"></i> Scan
                    </button>
                    <button type="button" id="tab-upload" class="btn flex-grow-1" style="background: rgba(0, 215, 255, 0.2); color: var(--accent-cyan); font-weight: 600; border-radius: 10px;">
                        <i class="fas fa-upload me-2"></i> Upload
                    </button>
                </div>
                
                <div id="scan-mode">
                    <div class="scanner-box">
                        <div id="reader"></div>
                        <div id="scanner-placeholder" class="scanner-placeholder">
                            <div class="scanner-icon">📷</div>
                            <div class="scanner-text">
                                <p>Click "Start Camera" to scan QR codes</p>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="start-scan" class="btn btn-scan">
                        <i class="fas fa-camera me-2"></i> Start Camera
                    </button>
                </div>

                <div id="upload-mode" style="display: none;">
                    <div style="border: 2px dashed var(--panel-border); border-radius: 16px; padding: 40px 20px; text-align: center;">
                        <i class="fas fa-file-image" style="font-size: 3rem; color: var(--accent-cyan); opacity: 0.7; display: block; margin-bottom: 12px;"></i>
                        <p style="color: #94a3b8; margin-bottom: 16px;">Upload a QR code image file</p>
                        <input type="file" id="qr-file-input" accept="image/*" style="display: none;">
                        <button type="button" id="upload-qr-btn" class="btn btn-scan">
                            <i class="fas fa-upload me-2"></i> Select QR Image
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scanned Document Info -->
            <div id="scanned-result" style="display: none;">
                <div class="glass-card">
                    <h5><i class="fas fa-file-check"></i> Scanned Document</h5>
                    <div id="scanned-content"></div>
                </div>
            </div>

            <!-- Signature Capture -->
            <div id="signature-section" style="display: none;" class="glass-card">
                <h5><i class="fas fa-pen"></i> Add Your Signature</h5>
                <p style="color: #94a3b8; margin: 0 0 16px 0; font-size: 0.9rem;">
                    Sign here to confirm receipt of the document
                </p>
                
                <div class="signature-pad-container">
                    <canvas id="signature" width="100%" height="200"></canvas>
                </div>

                <div class="signature-btns">
                    <button type="button" class="btn-clear-sig" id="clear-sig">
                        <i class="fas fa-redo me-2"></i> Clear
                    </button>
                    <button type="button" class="btn-submit-sig" id="submit-sig">
                        <i class="fas fa-check me-2"></i> Submit & Save
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column: Info & Flow -->
        <div class="col-lg-5">
            <!-- How to Use -->
            <div class="glass-card">
                <h5><i class="fas fa-info-circle"></i> How to Use</h5>
                <ul style="color: #94a3b8; line-height: 1.8; margin-bottom: 0;">
                    <li><strong>1. Start Camera:</strong> Click the start button to enable your device camera</li>
                    <li><strong>2. Scan QR:</strong> Point at the document's QR code</li>
                    <li><strong>3. Add Signature:</strong> Sign to confirm document receipt</li>
                    <li><strong>4. Submit:</strong> Your signature serves as proof of delivery</li>
                </ul>
            </div>

            <!-- Routing Flow -->
            <div class="glass-card">
                <h5><i class="fas fa-route"></i> Document Flow</h5>
                <div class="routing-flow">
                    <div class="step">
                        <div class="step-circle step-1">1</div>
                        <div class="step-content">
                            <h6>Document Created</h6>
                            <p>Uploaded to system</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-circle step-2">2</div>
                        <div class="step-content">
                            <h6>In Transit</h6>
                            <p>Being routed</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-circle step-3">3</div>
                        <div class="step-content">
                            <h6>Signed & Delivered</h6>
                            <p>Receipt confirmed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Documents -->
            <div class="glass-card">
                <h5><i class="fas fa-history"></i> Recent Documents</h5>
                @forelse($documents as $doc)
                    <div class="document-info">
                        <div class="document-info-row">
                            <span class="document-info-label">📄 Document</span>
                            <span class="document-info-value" style="max-width: 50%; text-align: right; word-wrap: break-word;">{{ substr($doc->title, 0, 20) }}...</span>
                        </div>
                        <div class="document-info-row">
                            <span class="document-info-label">Status</span>
                            <span class="badge-custom {{ $doc->status === 'Completed' ? 'badge-success' : 'badge-pending' }}">
                                {{ $doc->status }}
                            </span>
                        </div>
                        <div class="document-info-row">
                            <span class="document-info-label">Receiver</span>
                            <span class="document-info-value">{{ $doc->receiverUser?->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                @empty
                    <p style="color: #94a3b8; text-align: center; margin: 20px 0;">No recent documents</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    let html5QrCode;
    let signaturePad;
    let currentScannedDoc = null;

    document.addEventListener('DOMContentLoaded', function() {
        initializeScanner();
        initializeSignaturePad();
        initializeTabs();
    });

    function initializeTabs() {
        const tabScan = document.getElementById('tab-scan');
        const tabUpload = document.getElementById('tab-upload');
        const scanMode = document.getElementById('scan-mode');
        const uploadMode = document.getElementById('upload-mode');
        const qrFileInput = document.getElementById('qr-file-input');
        const uploadQrBtn = document.getElementById('upload-qr-btn');

        tabScan.addEventListener('click', function() {
            scanMode.style.display = 'block';
            uploadMode.style.display = 'none';
            tabScan.style.background = 'var(--accent-cyan)';
            tabScan.style.color = '#000';
            tabUpload.style.background = 'rgba(0, 215, 255, 0.2)';
            tabUpload.style.color = 'var(--accent-cyan)';
        });

        tabUpload.addEventListener('click', function() {
            scanMode.style.display = 'none';
            uploadMode.style.display = 'block';
            tabScan.style.background = 'rgba(0, 215, 255, 0.2)';
            tabScan.style.color = 'var(--accent-cyan)';
            tabUpload.style.background = 'var(--accent-cyan)';
            tabUpload.style.color = '#000';
        });

        uploadQrBtn.addEventListener('click', function() {
            qrFileInput.click();
        });

        qrFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const imageData = event.target.result;
                // Try to decode QR from image using html5-qrcode
                if (typeof Html5Qrcode !== 'undefined') {
                    const html5qrcode = new Html5Qrcode("upload-scanner");
                    html5qrcode.scanFile(file, true)
                        .then(decodedText => {
                            handleQRScan(decodedText);
                        })
                        .catch(err => {
                            console.log("QR decode error:", err);
                            showAlert('Could not read QR code from image. Please try scanning with camera or upload a clearer image.', 'error');
                        });
                }
            };
            reader.readAsDataURL(file);
        });
    }

    function initializeScanner() {
        const scannerBtn = document.getElementById('start-scan');
        const placeholder = document.getElementById('scanner-placeholder');

        scannerBtn.addEventListener('click', function() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    placeholder.style.display = 'flex';
                    scannerBtn.innerHTML = '<i class="fas fa-camera me-2"></i> Start Camera';
                });
                return;
            }

            placeholder.style.display = 'none';
            scannerBtn.innerHTML = '<span class="loading-spinner me-2"></span> Stop Camera';
            html5QrCode = new Html5Qrcode("reader");

            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => handleQRScan(decodedText),
                (errorMessage) => console.log("Scan error:", errorMessage)
            ).catch((err) => {
                console.error("Camera error:", err);
                placeholder.style.display = 'flex';
                scannerBtn.innerHTML = '<i class="fas fa-camera me-2"></i> Start Camera';
                showAlert('Camera access denied or not available', 'error');
            });
        });
    }

    function handleQRScan(qrData) {
        // Send QR data to server
        fetch('{{ route("qr.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ qr_data: qrData })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentScannedDoc = data.document;
                displayScannedDocument(data.document);
                document.getElementById('signature-section').style.display = 'block';
                showAlert(data.message, 'success');
                
                // Stop scanning after successful scan
                if (html5QrCode && html5QrCode.isScanning) {
                    html5QrCode.stop().then(() => {
                        document.getElementById('start-scan').innerHTML = '<i class="fas fa-camera me-2"></i> Start Camera';
                    });
                }
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error scanning QR code', 'error');
        });
    }

    function displayScannedDocument(doc) {
        const resultDiv = document.getElementById('scanned-result');
        const contentDiv = document.getElementById('scanned-content');
        
        contentDiv.innerHTML = `
            <div class="document-info">
                <div class="document-info-row">
                    <span class="document-info-label">📄 Title</span>
                    <span class="document-info-value">${doc.title}</span>
                </div>
                <div class="document-info-row">
                    <span class="document-info-label">Status</span>
                    <span class="badge-custom ${doc.status === 'Completed' ? 'badge-success' : 'badge-pending'}">
                        ${doc.status}
                    </span>
                </div>
                <div class="document-info-row">
                    <span class="document-info-label">Receiver</span>
                    <span class="document-info-value">${doc.receiver || 'N/A'}</span>
                </div>
                <div class="document-info-row">
                    <span class="document-info-label">Location</span>
                    <span class="document-info-value">${doc.current_office || 'N/A'}</span>
                </div>
                <div class="document-info-row">
                    <span class="document-info-label">Scanned</span>
                    <span class="document-info-value">${doc.scanned_at || 'Just now'}</span>
                </div>
            </div>
        `;
        
        resultDiv.style.display = 'block';
    }

    function initializeSignaturePad() {
        const canvas = document.getElementById('signature');
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'white',
            penColor: '#1e293b'
        });

        // Adjust canvas size
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = 200 * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        document.getElementById('clear-sig').addEventListener('click', () => {
            signaturePad.clear();
        });

        document.getElementById('submit-sig').addEventListener('click', () => {
            if (signaturePad.isEmpty()) {
                showAlert('Please sign before submitting', 'error');
                return;
            }

            if (!currentScannedDoc) {
                showAlert('No document scanned yet', 'error');
                return;
            }

            submitSignature();
        });
    }

    function submitSignature() {
        const signatureData = signaturePad.toDataURL('image/png');
        const docId = currentScannedDoc.id;

        fetch('{{ route("qr.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                qr_data: docId, 
                signature: signatureData 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Document signed and delivered! ✓', 'success');
                signaturePad.clear();
                document.getElementById('signature-section').style.display = 'none';
                document.getElementById('scanned-result').style.display = 'none';
                
                // Reload page after 2 seconds
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error submitting signature', 'error');
        });
    }

    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success-custom' : 'alert-danger-custom';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        const alertHTML = `
            <div class="alert alert-custom ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        const container = document.querySelector('.container-fluid');
        const existingAlerts = container.querySelectorAll('.alert-custom');
        if (existingAlerts.length > 0) {
            existingAlerts[0].remove();
        }
        
        container.insertAdjacentHTML('afterbegin', alertHTML);
    }
</script>

@endsection
