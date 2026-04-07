<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * These must match your MySQL table columns exactly.
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'priority',
        'origin_office_id',
        'current_office_id',
        'destination_office_id',
        'uploaded_by',
        'file_path',
        'status',
        'qr_code',
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

    /**
     * Relationship: History of movements/actions for this document.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
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
}