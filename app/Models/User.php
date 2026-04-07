<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'office_id',
    ];

    /**
     * Define the relationship to the Office model.
     */
    public function office(): BelongsTo
    {
        // This links office_id in the users table to the id in the offices table
        return $this->belongsTo(Office::class, 'office_id');
    }
}