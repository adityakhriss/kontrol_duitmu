<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingGoalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'saving_goal_id',
        'user_id',
        'payment_account_id',
        'entry_type',
        'amount',
        'entry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'entry_date' => 'date',
        ];
    }

    public function savingGoal(): BelongsTo
    {
        return $this->belongsTo(SavingGoal::class);
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
