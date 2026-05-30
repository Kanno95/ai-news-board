<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_board_page_is_displayed(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
