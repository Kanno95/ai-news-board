<?php

use App\Models\NewsItem;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $today = CarbonImmutable::now('Asia/Tokyo')->startOfDay();
    $oldestDate = $today->subMonth();
    $selectedDate = $request->query('date');

    $baseQuery = NewsItem::query()
        ->whereNotNull('published_at')
        ->where('published_at', '>=', $oldestDate->setTimezone('UTC'))
        ->where('region', 'domestic');

    $dateOptions = (clone $baseQuery)
        ->orderByDesc('published_at')
        ->pluck('published_at')
        ->map(fn ($publishedAt) => $publishedAt->timezone('Asia/Tokyo')->toDateString())
        ->unique()
        ->values();

    $latestDate = $dateOptions->first();

    if (! $dateOptions->contains($selectedDate)) {
        $selectedDate = null;
    }

    $newsItems = (clone $baseQuery)
        ->when($selectedDate, function ($query, string $selectedDate) {
            $startOfDay = CarbonImmutable::parse($selectedDate, 'Asia/Tokyo')->startOfDay();
            $endOfDay = $startOfDay->endOfDay();

            $query->whereBetween('published_at', [
                $startOfDay->setTimezone('UTC'),
                $endOfDay->setTimezone('UTC'),
            ]);
        })
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
        ->paginate(10)
        ->withQueryString();

    return view('news.index', [
        'dateOptions' => $dateOptions,
        'latestDate' => $latestDate,
        'newsItems' => $newsItems,
        'selectedDate' => $selectedDate,
    ]);
});
