<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Cms\Models\MediaFolder;

class MediaFolderFactory extends Factory
{
    protected $model = MediaFolder::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.uniqid(),
            'parent_id' => null,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function withParent(MediaFolder $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
