<?php

namespace Database\Factories\Master;

use Illuminate\Support\Str;
use App\Models\Master\DocumentLibrary;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentLibraryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DocumentLibrary::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'url' => $this->faker->text(),
            'type' => $this->faker->word(),
        ];
    }
}
