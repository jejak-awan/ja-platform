<?php

namespace Modules\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Media\Models\File;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        $extension = $this->faker->fileExtension();
        $filename = Str::slug($name) . '.' . $extension;

        return [
            'name' => $name,
            'file_name' => $filename,
            'mime_type' => $this->faker->mimeType(),
            'disk' => 'public',
            'path' => 'media/' . $filename,
            'size' => $this->faker->numberBetween(100, 1000000),
            'workspace_id' => null,
            'author_id' => null,
        ];
    }

    public function image(): self
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/jpeg',
            'path' => 'media/' . Str::slug($attributes['name']) . '.jpg',
        ]);
    }

    public function document(): self
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'application/pdf',
            'path' => 'media/' . Str::slug($attributes['name']) . '.pdf',
        ]);
    }
}
