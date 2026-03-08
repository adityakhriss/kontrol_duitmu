<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_account_id',
        'name',
        'category_name',
        'amount',
        'due_date',
        'status',
        'is_recurring',
        'recurring_period',
        'paid_at',
        'last_generated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'is_recurring' => 'boolean',
            'paid_at' => 'datetime',
            'last_generated_at' => 'datetime',
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

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }
}
