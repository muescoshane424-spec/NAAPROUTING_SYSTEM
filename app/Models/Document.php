<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Document extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * These must match your MySQL table columns exactly.
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'priority',
        'sla',
        'origin_office_id',
        'current_office_id',
        'destination_office_id',
        'destination_offices',
        'receiver_user_id',
        'uploaded_by',
        'file_path',
        'status',
        'qr_code',
        'qr_id',
        'due_date',
        'receiver_signature',
        'qr_scanned_at',
        'received_at',
        'routing_notes',
        'routing_history',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'qr_scanned_at' => 'datetime',
        'received_at' => 'datetime',
        'destination_offices' => 'array',
        'routing_history' => 'array',
    ];

    /**
     * Relationship: The office where the document is currently located.
     */
    public function currentOffice()
    {
        return $this->belongsTo(Office::class, 'current_office_id');
    }

    /**
     * Relationship: The office that originally created/uploaded the document.
     */
    public function originOffice()
    {
        return $this->belongsTo(Office::class, 'origin_office_id');
    }

    /**
     * Relationship: The intended final destination for the document.
     */
    public function destinationOffice()
    {
        return $this->belongsTo(Office::class, 'destination_office_id');
    }

    /**
     * Relationship: The user who uploaded the document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }

    public function receiverUsers()
    {
        return $this->belongsToMany(User::class, 'document_routings', 'document_id', 'receiver_user_id')->distinct();
    }

    /**
     * Relationship: History of movements/actions for this document.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function routings()
    {
        return $this->hasMany(DocumentRouting::class);
    }

    /**
     * Helper: Determine SLA status based on due date.
     */
    public function getSlaStatusAttribute()
    {
        if (!$this->due_date) {
            return 'on-time';
        }

        if ($this->due_date->isPast()) {
            return 'breached';
        }

        return $this->due_date->lessThanOrEqualTo(now()->addDays(2)) ? 'at-risk' : 'on-time';
    }

    public function getSlaStatusLabelAttribute()
    {
        return match ($this->sla_status) {
            'breached' => 'Breached ❌',
            'at-risk'  => 'At Risk ⚠️',
            default    => 'On Time ✅',
        };
    }

    public function getSlaStatusClassAttribute()
    {
        return match ($this->sla_status) {
            'breached' => 'text-white bg-danger',
            'at-risk'  => 'text-dark bg-warning',
            default    => 'text-white bg-success',
        };
    }

    /**
     * Helper: Get a Bootstrap-friendly color based on priority.
     * Useful for your Blade views.
     */
    public function getPriorityColorAttribute()
    {
        return match (strtolower($this->priority)) {
            'high'   => 'danger',
            'medium' => 'warning',
            'low'    => 'success',
            default  => 'secondary',
        };
    }

    /**
     * Check if document has been delivered and signed
     */
    public function isDelivered()
    {
        return $this->status === 'Completed' && $this->receiver_signature !== null;
    }

    /**
     * Mark document as received with signature
     */
    public function markAsReceived($signatureData = null)
    {
        $this->update([
            'status' => 'Completed',
            'received_at' => now(),
            'receiver_signature' => $signatureData,
            'qr_scanned_at' => now(),
        ]);
    }

    /**
     * Get delivery proof status
     */
    public function getDeliveryProofAttribute()
    {
        if (!$this->receiver_signature || !$this->qr_scanned_at) {
            return null;
        }

        return [
            'signed_at' => $this->qr_scanned_at,
            'has_signature' => true,
            'receiver_id' => $this->receiver_user_id,
        ];
    }

    /**
     * Notify receiver about document
     */
    public function notifyReceiver()
    {
        if ($this->receiver_user_id) {
            $receiver = User::find($this->receiver_user_id);
            if ($receiver) {
                $receiver->notify(new \App\Notifications\DocumentRoutedNotification($this));
            }
        }
    }

    /**
     * Notify uploader when document is signed
     */
    public function notifyUploader($signerName = null)
    {
        if ($this->uploaded_by) {
            $uploader = User::find($this->uploaded_by);
            if ($uploader) {
                $uploader->notify(new \App\Notifications\DocumentSignedNotification($this, $signerName));
            }
        }
    }
}