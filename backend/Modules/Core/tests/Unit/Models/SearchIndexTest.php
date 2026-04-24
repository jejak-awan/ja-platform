<?php

namespace Modules\Core\Tests\Unit\Models;

use Modules\Core\Models\SearchIndex;
use Modules\Core\Models\User;
use Tests\TestCase;

class SearchIndexTest extends TestCase
{
    public function test_index_and_remove()
    {
        $user = User::factory()->create(['name' => 'Searchable User']);

        // Test index (create)
        $index = SearchIndex::index($user, [
            'content' => 'Some searchable content',
            'meta' => ['foo' => 'bar'],
        ]);

        $this->assertEquals('Searchable User', $index->title);
        $this->assertEquals('Some searchable content', $index->content);
        $this->assertEquals(['foo' => 'bar'], $index->meta);
        $this->assertEquals(get_class($user), $index->searchable_type);
        $this->assertEquals($user->id, $index->searchable_id);
        $this->assertGreaterThan(0, $index->relevance_score);

        // Test index (update)
        $updatedIndex = SearchIndex::index($user, ['title' => 'Updated Title']);
        $this->assertEquals('Updated Title', $updatedIndex->title);
        $this->assertEquals($index->id, $updatedIndex->id);

        // Test morph relation
        $this->assertInstanceOf(User::class, $updatedIndex->searchable);

        // Test remove
        $this->assertTrue(SearchIndex::remove($user));
        $this->assertEquals(0, SearchIndex::where('searchable_id', $user->id)->count());

        // Remove non-existent returns false
        $this->assertFalse(SearchIndex::remove($user));
    }

    public function test_index_with_various_fallback_attributes()
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $model = \Mockery::mock($user)->makePartial();

        // Return null for everything except id
        $model->shouldReceive('getAttribute')
            ->andReturnUsing(fn ($key) => $key === 'id' ? $user->id : null);

        $index = SearchIndex::index($model);

        $this->assertEquals('', $index->title);
        $this->assertEquals('', $index->content);
        $this->assertNull($index->excerpt);
        $this->assertNull($index->url);
        $this->assertEquals($user->id, $index->searchable_id);
    }
}
