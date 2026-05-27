<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI News Board</title>
    <link rel="stylesheet" href="{{ asset('css/news-board.css') }}">
    <script src="{{ asset('js/news-board.js') }}" defer></script>
</head>
<body>
    <main class="page">
        <header class="masthead">
            <div>
                <div class="eyebrow">
                    <span class="eyebrow-mark" aria-hidden="true"></span>
                    AIニュース速報
                </div>
                <h1 class="title">AI News Board</h1>
            </div>
            <div class="stat-card">
                <span class="stat-number">
                    @if ($selectedDate)
                        {{ $newsItems->total() }}
                    @elseif ($latestDate)
                        {{ \Carbon\CarbonImmutable::parse($latestDate)->format('n/j') }}
                    @else
                        0
                    @endif
                </span>
                <span class="stat-label">
                    @if ($selectedDate)
                        記事数
                    @else
                        最新日
                    @endif
                </span>
            </div>
        </header>

        <section class="filter-bar" aria-label="News filters">
            <div>
                <p class="filter-title">
                    @if ($selectedDate)
                        {{ \Carbon\CarbonImmutable::parse($selectedDate)->format('Y年m月d日') }} のAIニュース
                    @else
                        最新の国内AIニュース
                    @endif
                </p>
                <p class="filter-note">
                    @if ($latestDate)
                        最新: {{ \Carbon\CarbonImmutable::parse($latestDate)->format('Y年m月d日') }}。直近30日分を表示
                    @else
                        直近30日分を表示
                    @endif
                </p>
            </div>
            <div class="filter-controls">
                <form class="filter-form" method="GET" action="/">
                    <select class="date-select" name="date" aria-label="表示する日付" onchange="this.form.submit()">
                        <option value="">最新ニュース</option>
                        @foreach ($dateOptions as $dateOption)
                            <option value="{{ $dateOption }}" @selected($selectedDate === $dateOption)>
                                {{ \Carbon\CarbonImmutable::parse($dateOption)->format('Y年m月d日') }}
                            </option>
                        @endforeach
                    </select>
                    <noscript>
                        <button class="filter-button" type="submit">表示</button>
                    </noscript>
                </form>
            </div>
        </section>

        @if ($newsItems->isEmpty())
            <div class="empty">
                まだニュースが保存されていません。
            </div>
        @else
            <section class="news-list" aria-label="News list">
                @foreach ($newsItems as $newsItem)
                    @php
                        $host = parse_url($newsItem->url, PHP_URL_HOST) ?: 'news';
                        $domain = preg_replace('/^www\./', '', $host);
                        $faviconUrl = 'https://www.google.com/s2/favicons?domain=' . urlencode($domain) . '&sz=64';
                    @endphp
                    <article class="news-item" data-url="{{ $newsItem->url }}" tabindex="0" aria-label="{{ $newsItem->title }} を開く" style="--entry-delay: {{ min($loop->index, 9) * 55 }}ms;">
                        <div class="news-content">
                            <div class="title-line">
                                @if ($newsItem->is_latest)
                                    <span class="latest-badge">最新</span>
                                @endif
                                <h2 class="news-title">
                                    <a href="{{ $newsItem->url }}" target="_blank" rel="noopener noreferrer">
                                        {{ $newsItem->title }}
                                    </a>
                                </h2>
                            </div>
                            @if ($newsItem->summary)
                                <p class="summary">{{ $newsItem->summary }}</p>
                            @endif
                            <div class="meta">
                                @if ($newsItem->published_at)
                                    <span class="pill">{{ $newsItem->published_at->timezone('Asia/Tokyo')->format('Y-m-d H:i') }}</span>
                                @endif
                            </div>
                            <a class="open-link" href="{{ $newsItem->url }}" target="_blank" rel="noopener noreferrer">
                                元記事を読む
                            </a>
                        </div>
                        <div class="preview-frame" aria-hidden="true">
                            @if ($newsItem->image_url)
                                <img class="preview-image" src="{{ $newsItem->image_url }}" alt="" loading="lazy" onerror="this.className='favicon'; this.src='{{ $faviconUrl }}';">
                            @else
                                <img class="favicon" src="{{ $faviconUrl }}" alt="" loading="lazy">
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="pagination">
                <div>
                    @if ($selectedDate)
                        {{ $newsItems->firstItem() }}-{{ $newsItems->lastItem() }} / {{ $newsItems->total() }} articles
                    @else
                        最新{{ $newsItems->count() }}件を表示中 / 保存済み{{ $newsItems->total() }}件
                    @endif
                </div>
                <div class="pagination-links">
                    @if ($newsItems->onFirstPage())
                        <span class="pagination-disabled">Previous</span>
                    @else
                        <a class="pagination-link" href="{{ $newsItems->previousPageUrl() }}">Previous</a>
                    @endif

                    @if ($newsItems->hasMorePages())
                        <a class="pagination-link" href="{{ $newsItems->nextPageUrl() }}">Next</a>
                    @else
                        <span class="pagination-disabled">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </main>
</body>
</html>
