<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_payment_account_id',
        'to_payment_account_id',
        'transfer_date',
        'amount',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'from_payment_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'to_payment_account_id');
    }
}
