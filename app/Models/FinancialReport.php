<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_financial_insight_id',
        'title',
        'period_start',
        'period_end',
        'payload',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'payload' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiFinancialInsight(): BelongsTo
    {
        return $this->belongsTo(AiFinancialInsight::class);
    }
}
