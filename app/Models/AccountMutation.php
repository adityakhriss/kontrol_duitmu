<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_account_id',
        'source_type',
        'source_id',
        'mutation_type',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'mutation_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'mutation_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
