<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFinancialInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'snapshot_month',
        'analysis_period_start',
        'analysis_period_end',
        'status',
        'analysis',
        'recommendations',
        'input_summary',
        'output_payload',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_month' => 'date',
            'analysis_period_start' => 'date',
            'analysis_period_end' => 'date',
            'input_summary' => 'array',
            'output_payload' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
