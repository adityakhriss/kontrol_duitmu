<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentNews extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider', 'external_id', 'title', 'category', 'source', 'url', 'image_url', 'summary', 'published_at', 'payload',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
