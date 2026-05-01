<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Media;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $fileName = $this->faker->uuid().'.jpg';

        return [
            'name' => $this->faker->words(3, true),
            'file_name' => $fileName,
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'path' => 'media/'.$fileName,
            'size' => $this->faker->numberBetween(10000, 5000000),
            'alt' => $this->faker->optional()->sentence(),
            'description' => $this->faker->optional()->text(),
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/gif', 'image/webp']),
        ]);
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'application/pdf',
            'file_name' => $this->faker->uuid().'.pdf',
            'path' => 'media/'.$this->faker->uuid().'.pdf',
        ]);
    }

    public function document(): static
    {
        return $this->pdf();
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'video/mp4',
            'file_name' => $this->faker->uuid().'.mp4',
            'path' => 'media/'.$this->faker->uuid().'.mp4',
            'size' => $this->faker->numberBetween(5000000, 50000000),
        ]);
    }
}
