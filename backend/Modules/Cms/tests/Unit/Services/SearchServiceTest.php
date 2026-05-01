<?php

namespace Modules\Cms\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Content;
use Modules\Core\Models\Tag;
use Modules\Cms\Services\SearchService;
use Modules\Core\Models\SearchIndex;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SearchService;
    }

    public function test_search_strict_and_loose()
    {
        // Create indexes directly
        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 1,
            'title' => 'Exclusive Laravel Tutorial',
            'content' => 'Learn how to build apps',
            'relevance_score' => 100,
        ]);

        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 2,
            'title' => 'Another Tutorial',
            'content' => 'Some other content',
            'relevance_score' => 50,
        ]);

        // 1. Strict Search (AND) - Both words must be present in titles/contents
        $result = $this->service->search('Exclusive Laravel');
        $this->assertCount(1, $result['results']);
        $this->assertEquals('Exclusive Laravel Tutorial', $result['results'][0]['title']);
        $this->assertFalse($result['is_loose']);

        // 2. Loose Search (OR) - Fallback when strict returns nothing
        $resultLoose = $this->service->search('Exclusive Unknown');
        $this->assertCount(1, $resultLoose['results']);
        $this->assertTrue($resultLoose['is_loose']);
    }

    public function test_search_with_filters()
    {
        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 1,
            'title' => 'News Post',
            'content' => 'Relevant content',
            'type' => 'post',
            'relevance_score' => 10,
        ]);

        SearchIndex::create([
            'searchable_type' => 'Page',
            'searchable_id' => 1,
            'title' => 'News Page',
            'content' => 'Relevant content',
            'type' => 'page',
            'relevance_score' => 10,
        ]);

        $result = $this->service->search('News', ['type' => 'post']);
        $this->assertCount(1, $result['results']);
        $this->assertEquals('post', $result['results'][0]['type']);
    }

    public function test_get_suggestions()
    {
        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 1,
            'title' => 'Laravel Guide',
            'content' => 'Guide content',
            'type' => 'post',
        ]);

        $suggestions = $this->service->getSuggestions('Lara');
        $this->assertCount(1, $suggestions);
        $this->assertEquals('Laravel Guide', $suggestions[0]['text']);
    }

    public function test_reindex_all()
    {
        // One content (creates 1 category)
        Content::factory()->create(['title' => 'Post One', 'status' => 'published']);
        // One standalone active category
        Category::factory()->create(['name' => 'Cat One', 'is_active' => true]);
        Tag::factory()->create(['name' => 'Tag One']);

        $counts = $this->service->reindexAll();

        // 1 content + 2 categories + 1 tag = 4
        $this->assertDatabaseCount('search_indexes', 4);
        $this->assertEquals(1, $counts['contents']);
        $this->assertEquals(2, $counts['categories']);
        $this->assertEquals(1, $counts['tags']);
    }

    public function test_search_empty_query()
    {
        $result = $this->service->search('');
        $this->assertCount(0, $result['results']);
    }

    public function test_search_by_type()
    {
        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 1,
            'title' => 'Type Test',
            'content' => 'Content here',
            'type' => 'custom',
            'relevance_score' => 10,
        ]);

        $result = $this->service->searchByType('Type', 'custom');
        $this->assertCount(1, $result['results']);
    }
}
