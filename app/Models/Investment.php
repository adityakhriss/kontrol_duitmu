<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'ticker', 'type', 'units', 'buy_price', 'current_price', 'total_cost',
        'current_value', 'platform', 'purchase_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'decimal:8',
            'buy_price' => 'decimal:2',
            'current_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'current_value' => 'decimal:2',
            'purchase_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvestmentTransaction::class);
    }
}
