<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Models;

use Illuminate\Database\Eloquent\Model;

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
        'voidedAt' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    public const STATUS_BOOKED = 'booked';
    public const STATUS_VOIDED = 'voided';

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    public function voidedByAdmin()
    {
        return $this->belongsTo(User::class, 'voidedByAdminId');
    }

    public function originalBooking()
    {
        return $this->belongsTo(Booking::class, 'originalBookingId');
    }

    public function reversalBookings()
    {
        return $this->hasMany(Booking::class, 'originalBookingId');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_BOOKED);
    }

    public function scopeVoided($query)
    {
        return $query->where('status', self::STATUS_VOIDED);
    }

    public function void(string $adminId): void
    {
        $this->status = self::STATUS_VOIDED;
        $this->voidedByAdminId = $adminId;
        $this->voidedAt = \Carbon\Carbon::now()->toDateTimeString();
        $this->save();
    }

    public function isVoided(): bool
    {
        return $this->status === self::STATUS_VOIDED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_BOOKED;
    }

    public function getTotalEuroAttribute(): float
    {
        return $this->totalCents / 100.0;
    }

    public function getUnitPriceEuroAttribute(): float
    {
        return $this->unitPriceCents / 100.0;
    }
}
