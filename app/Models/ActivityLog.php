<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'action',
        'document_id',
        'ip',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function document() { return $this->belongsTo(Document::class); }
}
