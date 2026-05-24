<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $fillable = [
        'title',
        'title_ja',
        'url',
        'source',
        'region',
        'published_at',
        'summary',
        'summary_ja',
        'image_url',
        'is_latest',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_latest' => 'boolean',
    ];
}
