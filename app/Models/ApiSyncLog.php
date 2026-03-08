<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider', 'status', 'action', 'message', 'records_count', 'started_at', 'finished_at', 'context',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'context' => 'array',
        ];
    }
}
