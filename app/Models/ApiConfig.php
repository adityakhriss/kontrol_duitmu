<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider', 'base_url', 'api_key', 'is_active', 'default_category', 'fetch_limit', 'sync_interval_minutes', 'last_synced_at', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
            'settings' => 'array',
        ];
    }
}
