<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Department;

class User extends Authenticatable
{
    use Notifiable;

    // Valid roles
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_USER = 'USER';

    protected $fillable = [
        'name', 'email', 'username', 'password', 'role', 'department_id', 'signature', 'phone', 'avatar', 'status', 'two_factor_secret', 'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (!in_array($model->role, [self::ROLE_ADMIN, self::ROLE_USER])) {
                $model->role = self::ROLE_USER;
            }
        });

        self::updating(function ($model) {
            if (!in_array($model->role, [self::ROLE_ADMIN, self::ROLE_USER])) {
                $model->role = self::ROLE_USER;
            }
        });
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Define the relationship to the Department model.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}