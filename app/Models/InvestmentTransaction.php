<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id', 'user_id', 'payment_account_id', 'type', 'transaction_date', 'units', 'price', 'total_amount', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'units' => 'decimal:8',
            'price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }
}
