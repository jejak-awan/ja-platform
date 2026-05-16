<?php

namespace Modules\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Media\Models\Folder;

class FolderFactory extends Factory
{
    protected $model = Folder::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'workspace_id' => null,
            'author_id' => null,
            'module' => 'cms',
        ];
    }
}
