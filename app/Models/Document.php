<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

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

    public function originOffice() { return $this->belongsTo(Office::class, 'origin_office_id'); }
    public function destinationOffice() { return $this->belongsTo(Office::class, 'destination_office_id'); }
    public function currentOffice() { return $this->belongsTo(Office::class, 'current_office_id'); }
    public function routings() { return $this->hasMany(DocumentRouting::class); }
}
