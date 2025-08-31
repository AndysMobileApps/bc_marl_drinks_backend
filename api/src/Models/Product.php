<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Models;

use Illuminate\Database\Eloquent\Model;

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Category constants
    public const CATEGORY_DRINKS = 'DRINKS';
    public const CATEGORY_SNACKS = 'SNACKS';
    public const CATEGORY_ACCESSORIES = 'ACCESSORIES';
    public const CATEGORY_MEMBERSHIP = 'MEMBERSHIP';

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_DRINKS,
            self::CATEGORY_SNACKS,
            self::CATEGORY_ACCESSORIES,
            self::CATEGORY_MEMBERSHIP
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'productId');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'productId');
    }

    public function getPriceEuroAttribute(): float
    {
        return $this->priceCents / 100.0;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
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

    public function isAvailable(): bool
    {
        return $this->active;
    }
}
