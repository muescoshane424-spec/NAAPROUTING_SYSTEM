<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRouting extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'from_office_id',
        'to_office_id',
        'receiver_user_id',
        'status',
        'notes',
        'signed_by',
        'signature',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function document() { return $this->belongsTo(Document::class); }
    public function fromOffice() { return $this->belongsTo(Office::class, 'from_office_id'); }
    public function toOffice() { return $this->belongsTo(Office::class, 'to_office_id'); }
    public function receiverUser() { return $this->belongsTo(User::class, 'receiver_user_id'); }
}
