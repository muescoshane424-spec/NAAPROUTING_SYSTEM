@extends('layouts.app')

@section('title','Document Tracking - ' . $document->title)

@section('content')
<style>
    .tracking-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .back-link {
        color: #00d7ff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        transition: 0.2s;
    }

    .back-link:hover {
        color: #00e5ff;
        transform: translateX(-4px);
    }

    .document-header {
        background: linear-gradient(135deg, rgba(0, 215, 255, 0.1), rgba(168, 85, 247, 0.1));
        border: 1px solid rgba(0, 215, 255, 0.2);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 30px;
    }

    .document-title {
        color: #00d7ff;
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 16px 0;
    }

    .document-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .meta-item {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(0, 215, 255, 0.1);
        border-radius: 12px;
        padding: 16px;
    }

    .meta-label {
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }

    .meta-value {
        color: white;
        font-size: 1rem;
        font-weight: 500;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .status-completed {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .status-in-transit {
        background: rgba(245, 158, 11, 0.2);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .status-pending {
        background: rgba(59, 130, 246, 0.2);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .timeline-section {
        margin-bottom: 30px;
    }

    .timeline-title {
        color: #00d7ff;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .timeline-container {
        position: relative;
        padding-left: 40px;
    }

    .timeline-line {
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: rgba(0, 215, 255, 0.2);
    }

    .timeline-item {
        display: flex;
        margin-bottom: 32px;
        position: relative;
    }

    .timeline-dot {
        position: absolute;
        left: -30px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #000;
        box-shadow: 0 0 0 3px #0f172a;
        z-index: 10;
    }

    .dot-created {
        background: #10b981;
    }

    .dot-transit {
        background: #f59e0b;
    }

    .dot-signed {
        background: #00d7ff;
    }

    .dot-received {
        background: #10b981;
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-event-title {
        color: #00d7ff;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 8px;
    }

    .timeline-event-details {
        color: #94a3b8;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .timeline-timestamp {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .timeline-notes {
        background: rgba(0, 215, 255, 0.08);
        border-left: 3px solid #00d7ff;
        padding: 12px;
        border-radius: 8px;
        margin-top: 12px;
        color: #ccc;
        font-size: 0.9rem;
        font-style: italic;
    }

    .proof-of-delivery {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: 12px;
        padding: 16px;
        margin-top: 12px;
    }

    .proof-header {
        color: #10b981;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .proof-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(16, 185, 129, 0.2);
        font-size: 0.9rem;
    }

    .proof-row:last-child {
        border-bottom: none;
    }

    .proof-label {
        color: #94a3b8;
    }

    .proof-value {
        color: white;
        font-weight: 500;
    }

    .signature-preview {
        display: inline-block;
        margin-top: 12px;
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: 8px;
        padding: 8px;
        background: rgba(255, 255, 255, 0.05);
    }

    .signature-preview img {
        max-width: 150px;
        height: auto;
        border-radius: 4px;
    }

    @media (max-width: 768px) {
        .document-title {
            font-size: 1.5rem;
        }

        .document-meta {
            grid-template-columns: 1fr;
        }

        .meta-item {
            padding: 12px;
        }

        .timeline-container {
            padding-left: 30px;
        }

        .timeline-dot {
            left: -22px;
            width: 28px;
            height: 28px;
            font-size: 0.75rem;
        }
    }
</style>

<div class="tracking-container">
    <a href="{{ route('track.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Tracking
    </a>

    <!-- Document Header -->
    <div class="document-header">
        <h1 class="document-title">{{ $document->title }}</h1>
        <div class="document-meta">
            <div class="meta-item">
                <div class="meta-label">📋 Document ID</div>
                <div class="meta-value">#{{ $document->id }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">🔖 QR Code ID</div>
                <div class="meta-value">{{ $document->qr_id ?? 'N/A' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Status</div>
                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $document->status)) }}">
                    {{ $document->status }}
                </span>
            </div>
            <div class="meta-item">
                <div class="meta-label">📤 Origin</div>
                <div class="meta-value">{{ $document->originOffice?->name ?? 'Unknown' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">📥 Destination</div>
                <div class="meta-value">{{ $document->destinationOffice?->name ?? 'Unknown' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">📍 Current Location</div>
                <div class="meta-value">{{ $document->currentOffice?->name ?? 'In Transit' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">⏰ Created</div>
                <div class="meta-value">{{ $document->created_at->format('M j, Y H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Routing Timeline -->
    <div class="timeline-section">
        <h2 class="timeline-title">
            <i class="fas fa-map-pin"></i> Routing Timeline
        </h2>

        <div class="timeline-container">
            <div class="timeline-line"></div>

            <!-- Document Created -->
            <div class="timeline-item">
                <div class="timeline-dot dot-created">✓</div>
                <div class="timeline-content">
                    <div class="timeline-event-title">Document Created</div>
                    <div class="timeline-event-details">
                        {{ $document->originOffice?->name ?? 'Unknown Office' }}
                    </div>
                    <div class="timeline-timestamp">
                        {{ $document->created_at->format('M j, Y \a\t H:i:s') }}
                    </div>
                </div>
            </div>

            <!-- Routing Steps -->
            @forelse($document->routings()->orderBy('created_at')->get() as $routing)
            <div class="timeline-item">
                <div class="timeline-dot dot-transit">→</div>
                <div class="timeline-content">
                    <div class="timeline-event-title">{{ ucfirst($routing->status) }}</div>
                    <div class="timeline-event-details">
                        {{ $routing->fromOffice?->name }} 
                        <i class="fas fa-arrow-right" style="color: #94a3b8; margin: 0 8px;"></i> 
                        {{ $routing->toOffice?->name }}
                    </div>
                    <div class="timeline-timestamp">
                        {{ $routing->created_at->format('M j, Y \a\t H:i:s') }}
                        @if($routing->received_at)
                            <br><small>Received: {{ $routing->received_at->format('M j, Y \a\t H:i:s') }}</small>
                        @endif
                    </div>

                    @if($routing->notes)
                    <div class="timeline-notes">
                        "{{ $routing->notes }}"
                    </div>
                    @endif

                    <!-- Signature Proof -->
                    @if($routing->signature)
                    <div class="proof-of-delivery">
                        <div class="proof-header">
                            <i class="fas fa-pen"></i> Signed for Receipt
                        </div>
                        <div class="proof-row">
                            <span class="proof-label">Signed by:</span>
                            <span class="proof-value">{{ $routing->signed_by ?? 'System' }}</span>
                        </div>
                        <div class="proof-row">
                            <span class="proof-label">Signed at:</span>
                            <span class="proof-value">{{ $routing->received_at?->format('M j, Y H:i') ?? 'N/A' }}</span>
                        </div>
                        <div class="signature-preview">
                            <img src="{{ strpos($routing->signature, 'storage/') === 0 ? asset($routing->signature) : 'data:image/png;base64,' . $routing->signature }}" alt="Signature Proof" onerror="this.style.display='none'">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            @endforelse

            <!-- Document Received & Signed -->
            @if($document->received_at)
            <div class="timeline-item">
                <div class="timeline-dot dot-signed">✓</div>
                <div class="timeline-content">
                    <div class="timeline-event-title">Document Received & Signed</div>
                    <div class="timeline-event-details">
                        {{ $document->destinationOffice?->name ?? 'Unknown' }}
                    </div>
                    <div class="timeline-timestamp">
                        {{ $document->received_at->format('M j, Y \a\t H:i:s') }}
                    </div>

                    @if($document->receiver_signature)
                    <div class="proof-of-delivery">
                        <div class="proof-header">
                            <i class="fas fa-check-circle"></i> Proof of Delivery (QR Signed)
                        </div>
                        <div class="proof-row">
                            <span class="proof-label">Received by:</span>
                            <span class="proof-value">{{ $document->receiverUser?->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="proof-row">
                            <span class="proof-label">Received at:</span>
                            <span class="proof-value">{{ $document->received_at->format('M j, Y H:i:s') }}</span>
                        </div>
                        <div class="proof-row">
                            <span class="proof-label">QR Scanned:</span>
                            <span class="proof-value">{{ $document->qr_scanned_at?->format('M j, Y H:i:s') ?? 'N/A' }}</span>
                        </div>
                        <div class="signature-preview">
                            <img src="{{ asset('storage/' . $document->receiver_signature) }}" alt="Receiver Signature" onerror="this.style.display='none'">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Status: Completed -->
            @if($document->status === 'Completed')
            <div class="timeline-item">
                <div class="timeline-dot dot-received">✓</div>
                <div class="timeline-content">
                    <div class="timeline-event-title">Delivery Completed</div>
                    <div class="timeline-event-details">
                        All routing steps completed
                    </div>
                    <div class="timeline-timestamp">
                        {{ $document->updated_at->format('M j, Y \a\t H:i:s') }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Real-time tracking with polling
    const documentId = {{ $document->id }};
    let lastUpdateTime = new Date();

    function updateTrackingStatus() {
        fetch(`/api/documents/${documentId}/status`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.updated) {
                // Reload the page to show updated status
                location.reload();
            }
        })
        .catch(error => console.log('Polling update check...'));
    }

    // Poll every 5 seconds for updates
    setInterval(updateTrackingStatus, 5000);

    // Initial check when page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateTrackingStatus();
    });
</script>
@endsection