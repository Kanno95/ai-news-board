<?php

namespace App\Console\Commands;

use App\Models\NewsItem;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

#[Signature('news:fetch')]
#[Description('Fetch AI news from RSS feeds')]
class FetchNews extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feeds = [
            [
                'url' => 'https://rss.itmedia.co.jp/rss/2.0/aiplus.xml',
                'region' => 'domestic',
                'source' => 'ITmedia AI+',
            ],
        ];

        $savedCount = 0;
        $failedCount = 0;
        foreach ($feeds as $feed) {
            $this->info("Fetching RSS feed: {$feed['url']}");

            $response = Http::timeout(10)
                ->withUserAgent('ai-news-board/1.0')
                ->get($feed['url']);

            if (! $response->successful()) {
                $failedCount++;
                $this->error("Failed to fetch RSS feed. HTTP status: {$response->status()}");

                continue;
            }

            $rss = $this->parseXml($response->body());

            if (! $rss instanceof SimpleXMLElement || ! isset($rss->channel->item)) {
                $failedCount++;
                $this->error('Failed to parse RSS feed.');

                continue;
            }

            foreach ($rss->channel->item as $item) {
                $url = trim((string) $item->link);

                if ($url === '') {
                    continue;
                }

                $values = [
                    'title' => trim((string) $item->title),
                    'source' => trim((string) $rss->channel->title) ?: $feed['source'],
                    'region' => $feed['region'],
                    'published_at' => $this->parsePublishedAt((string) $item->pubDate),
                    'summary' => trim(strip_tags((string) $item->description)),
                ];

                $previewImageUrl = $this->fetchPreviewImageUrl($url);

                if ($previewImageUrl !== null) {
                    $values['image_url'] = $previewImageUrl;
                }

                NewsItem::updateOrCreate(
                    ['url' => $url],
                    $values,
                );

                $savedCount++;
            }
        }

        if ($failedCount === 0) {
            $latestPublishedAt = NewsItem::whereNotNull('published_at')->max('published_at');

            NewsItem::where('is_latest', true)->update(['is_latest' => false]);

            if ($latestPublishedAt !== null) {
                NewsItem::whereDate('published_at', substr($latestPublishedAt, 0, 10))
                    ->update(['is_latest' => true]);
            }

            $this->info('Marked news items from the latest published date as latest.');
        }

        $this->info("Fetched {$savedCount} news items.");

        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function parseXml(string $body): ?SimpleXMLElement
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);

        libxml_clear_errors();

        return $xml === false ? null : $xml;
    }

    private function parsePublishedAt(string $publishedAt): ?string
    {
        if (trim($publishedAt) === '') {
            return null;
        }

        $timestamp = strtotime($publishedAt);

        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    private function fetchPreviewImageUrl(string $url): ?string
    {
        if (! $this->isHttpUrl($url)) {
            return null;
        }

        try {
            $response = Http::timeout(6)
                ->withUserAgent('ai-news-board/1.0')
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $this->extractPreviewImageUrl($response->body(), $url);
    }

    private function extractPreviewImageUrl(string $html, string $pageUrl): ?string
    {
        libxml_use_internal_errors(true);

        $document = new DOMDocument;
        $loaded = $document->loadHTML($html, LIBXML_NONET);

        libxml_clear_errors();

        if (! $loaded) {
            return null;
        }

        $xpath = new DOMXPath($document);
        $queries = [
            '//meta[translate(@property, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "og:image"]/@content',
            '//meta[translate(@property, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "og:image:secure_url"]/@content',
            '//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "twitter:image"]/@content',
        ];

        foreach ($queries as $query) {
            $nodes = $xpath->query($query);
            $imageUrl = trim((string) ($nodes?->item(0)?->nodeValue ?? ''));

            if ($imageUrl !== '') {
                return $this->absoluteUrl($imageUrl, $pageUrl);
            }
        }

        return null;
    }

    private function absoluteUrl(string $url, string $pageUrl): ?string
    {
        if ($this->isHttpUrl($url)) {
            return $url;
        }

        $pageParts = parse_url($pageUrl);

        if (! isset($pageParts['scheme'], $pageParts['host'])) {
            return null;
        }

        $origin = $pageParts['scheme'].'://'.$pageParts['host'];

        if (isset($pageParts['port'])) {
            $origin .= ':'.$pageParts['port'];
        }

        if (str_starts_with($url, '//')) {
            return $pageParts['scheme'].':'.$url;
        }

        if (str_starts_with($url, '/')) {
            return $origin.$url;
        }

        $path = $pageParts['path'] ?? '/';
        $directory = rtrim(str_replace('\\', '/', dirname($path)), '/');

        return $origin.($directory === '' ? '' : $directory).'/'.$url;
    }

    private function isHttpUrl(string $url): bool
    {
        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }
}
