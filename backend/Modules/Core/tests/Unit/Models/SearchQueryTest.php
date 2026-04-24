<?php

namespace Modules\Core\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\SearchQuery;
use Modules\Core\Models\User;
use Tests\TestCase;

class SearchQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_search_query()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $log = SearchQuery::log('test query', 5, ['page' => 1]);

        $this->assertEquals('test query', $log->query);
        $this->assertEquals(5, $log->results_count);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals(['page' => 1], $log->filters);
        $this->assertNotNull($log->searched_at);
        $this->assertInstanceOf(\Modules\Core\Models\User::class, $log->user);
    }

    public function test_popular_queries()
    {
        SearchQuery::create(['query' => 'apple', 'searched_at' => now(), 'results_count' => 1]);
        SearchQuery::create(['query' => 'apple', 'searched_at' => now(), 'results_count' => 2]);
        SearchQuery::create(['query' => 'banana', 'searched_at' => now(), 'results_count' => 0]);
        // Old query
        SearchQuery::create(['query' => 'old', 'searched_at' => now()->subDays(40), 'results_count' => 1]);

        $popular = SearchQuery::getPopularQueries(10, 30);
        $this->assertCount(2, $popular);
        $this->assertEquals('apple', $popular[0]->query);
        $this->assertEquals(2, $popular[0]->count);
    }

    public function test_no_results_queries()
    {
        SearchQuery::create(['query' => 'nothing', 'searched_at' => now(), 'results_count' => 0]);
        SearchQuery::create(['query' => 'something', 'searched_at' => now(), 'results_count' => 5]);

        $noResults = SearchQuery::getNoResultsQueries();
        $this->assertCount(1, $noResults);
        $this->assertEquals('nothing', $noResults[0]->query);
    }
}
