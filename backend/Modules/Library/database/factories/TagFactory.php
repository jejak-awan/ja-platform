<?php

namespace Modules\Library\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Library\Models\Tag;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(1, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => 'content',
            'workspace_id' => null,
            'author_id' => null,
        ];
    }
}
