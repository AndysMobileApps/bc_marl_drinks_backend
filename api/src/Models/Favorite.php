<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $table = 'favorites';
    protected $primaryKey = ['userId', 'productId'];
    public $incrementing = false;
    
    protected $fillable = [
        'userId',
        'productId'
    ];

    protected $casts = [
        'createdAt' => 'datetime'
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
}



