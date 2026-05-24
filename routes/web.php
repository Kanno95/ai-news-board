<?php

use App\Models\NewsItem;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $newsItems = NewsItem::query()
        ->whereNotNull('published_at')
        ->where('region', 'domestic')
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
        ->paginate(10);

    return view('news.index', [
        'newsItems' => $newsItems,
    ]);
});
