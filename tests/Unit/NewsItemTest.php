<?php

namespace Tests\Unit;

use App\Models\NewsItem;
use PHPUnit\Framework\TestCase;

class NewsItemTest extends TestCase
{
    public function test_news_item_casts_latest_flag_to_boolean(): void
    {
        $newsItem = new NewsItem(['is_latest' => 1]);

        $this->assertTrue($newsItem->is_latest);
    }
}
