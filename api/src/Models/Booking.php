<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'userId',
        'productId',
        'quantity',
        'unitPriceCents',
        'totalCents',
        'timestamp',
        'status',
        'voidedByAdminId',
        'voidedAt',
        'originalBookingId'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unitPriceCents' => 'integer',
        'totalCents' => 'integer',
        'timestamp' => 'datetime',
        'voidedAt' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    public function voidedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voidedByAdminId');
    }

    // Helper methods
    public function getTotalEuroAttribute(): float
    {
        return $this->totalCents / 100.0;
    }

    public function getUnitPriceEuroAttribute(): float
    {
        return $this->unitPriceCents / 100.0;
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function void(string $adminId): void
    {
        if ($this->status !== 'booked') {
            return;
        }

        $this->status = 'voided';
        $this->voidedByAdminId = $adminId;
        $this->voidedAt = now();
        $this->save();
    }
}
