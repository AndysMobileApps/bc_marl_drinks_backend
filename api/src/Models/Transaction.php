<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'userId',
        'type',
        'amountCents',
        'reference',
        'timestamp',
        'enteredByAdminId'
    ];

    protected $casts = [
        'amountCents' => 'integer',
        'timestamp' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function enteredByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enteredByAdminId');
    }

    // Helper methods
    public function getAmountEuroAttribute(): float
    {
        return $this->amountCents / 100.0;
    }

    public function getSignedAmountCentsAttribute(): int
    {
        return in_array($this->type, ['DEPOSIT', 'REVERSAL']) ? 
            $this->amountCents : 
            -$this->amountCents;
    }

    public function getSignedAmountEuroAttribute(): float
    {
        return $this->getSignedAmountCentsAttribute() / 100.0;
    }

    public function isPositive(): bool
    {
        return in_array($this->type, ['DEPOSIT', 'REVERSAL']);
    }
}


