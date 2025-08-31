<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Models;

use Illuminate\Database\Eloquent\Model;

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
        'role',
        'balanceCents',
        'lowBalanceThresholdCents',
        'locked',
        'failedLoginAttempts'
    ];

    protected $hidden = [
        'pinHash'
    ];

    protected $casts = [
        'locked' => 'boolean',
        'balanceCents' => 'integer',
        'lowBalanceThresholdCents' => 'integer',
        'failedLoginAttempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'userId');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'userId');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'userId');
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasLowBalance(): bool
    {
        return $this->balanceCents < $this->lowBalanceThresholdCents;
    }

    public function addBalance(int $amountCents): void
    {
        $this->balanceCents += $amountCents;
        $this->save();
    }

    public function deductBalance(int $amountCents): bool
    {
        if ($this->balanceCents >= $amountCents) {
            $this->balanceCents -= $amountCents;
            $this->save();
            return true;
        }
        return false;
    }

    public function getFullNameAttribute(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getBalanceEuroAttribute(): float
    {
        return $this->balanceCents / 100.0;
    }
}
