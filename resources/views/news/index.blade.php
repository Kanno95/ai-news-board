<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI News Board</title>
    <style>
        body {
            margin: 0;
            background: #eef4f7;
            color: #162028;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 20px 48px;
        }

        .masthead,
        .news-item,
        .empty {
            border: 1px solid #dbe8ec;
            border-radius: 8px;
            background: #ffffff;
        }

        .masthead {
            padding: 22px;
        }

        .eyebrow {
            margin: 0 0 8px;
            color: #0f6b63;
            font-size: 13px;
            font-weight: 700;
        }

        h1 {
            margin: 0;
            font-size: 30px;
            line-height: 1.2;
        }

        .news-list {
            display: grid;
            gap: 12px;
            margin-top: 20px;
        }

        .news-item {
            padding: 18px;
        }

        .news-title {
            margin: 0;
            font-size: 18px;
            line-height: 1.45;
        }

        .news-title a {
            color: #14384a;
            text-decoration: none;
        }

        .news-title a:hover {
            color: #0f6b63;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .summary {
            margin: 10px 0 0;
            color: #3c4d55;
            font-size: 14px;
            line-height: 1.65;
        }

        .meta {
            margin-top: 12px;
            color: #64757d;
            font-size: 13px;
        }

        .empty {
            margin-top: 20px;
            padding: 24px;
            color: #5f6f78;
        }

        .pagination {
            margin-top: 22px;
        }

        .pagination nav {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="masthead">
            <p class="eyebrow">AIニュース速報</p>
            <h1>AI News Board</h1>
        </header>

        @if ($newsItems->isEmpty())
            <div class="empty">
                まだニュースが保存されていません。
            </div>
        @else
            <section class="news-list" aria-label="News list">
                @foreach ($newsItems as $newsItem)
                    <article class="news-item">
                        <h2 class="news-title">
                            <a href="{{ $newsItem->url }}" target="_blank" rel="noopener noreferrer">
                                {{ $newsItem->title }}
                            </a>
                        </h2>
                        @if ($newsItem->summary)
                            <p class="summary">{{ $newsItem->summary }}</p>
                        @endif
                        @if ($newsItem->published_at)
                            <div class="meta">
                                {{ $newsItem->published_at->timezone('Asia/Tokyo')->format('Y-m-d H:i') }}
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>

            <div class="pagination">
                {{ $newsItems->links() }}
            </div>
        @endif
    </main>
</body>
</html>
