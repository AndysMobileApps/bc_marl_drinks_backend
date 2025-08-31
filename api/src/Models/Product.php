<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'name',
        'icon',
        'priceCents',
        'category',
        'active'
    ];

    protected $casts = [
        'priceCents' => 'integer',
        'active' => 'boolean',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime'
    ];

    // Relationships
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'productId');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'productId');
    }

    // Helper methods
    public function getPriceEuroAttribute(): float
    {
        return $this->priceCents / 100.0;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->getPriceEuroAttribute(), 2, ',', '.') . ' €';
    }

    public function activate(): void
    {
        $this->active = true;
        $this->save();
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->save();
    }
}
