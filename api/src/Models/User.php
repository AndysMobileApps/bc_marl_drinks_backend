<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'firstName',
        'lastName', 
        'email',
        'mobile',
        'pinHash',
        'role',
        'balanceCents',
        'lowBalanceThresholdCents',
        'locked',
        'failedLoginAttempts'
    ];

    protected $casts = [
        'locked' => 'boolean',
        'balanceCents' => 'integer',
        'lowBalanceThresholdCents' => 'integer',
        'failedLoginAttempts' => 'integer',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime'
    ];

    protected $hidden = [
        'pinHash'
    ];

    // Relationships
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'userId');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'userId');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'userId');
    }

    // Helper methods
    public function getFullNameAttribute(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getBalanceEuroAttribute(): float
    {
        return $this->balanceCents / 100.0;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBalanceBelowThreshold(): bool
    {
        return $this->balanceCents < $this->lowBalanceThresholdCents;
    }

    public function incrementFailedAttempts(): void
    {
        $this->failedLoginAttempts++;
        if ($this->failedLoginAttempts >= 3) {
            $this->locked = true;
        }
        $this->save();
    }

    public function resetFailedAttempts(): void
    {
        $this->failedLoginAttempts = 0;
        $this->save();
    }

    public function unlock(): void
    {
        $this->locked = false;
        $this->failedLoginAttempts = 0;
        $this->save();
    }
}
