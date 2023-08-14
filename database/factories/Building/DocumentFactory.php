<?php

namespace Database\Factories\Building;

use App\Models\Building\Document;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => $this->faker->text(),
            'status' => $this->faker->word(),
            'comments' => [],
            'expiry_date' => $this->faker->date(),
            'document_library_id' => \App\Models\Master\DocumentLibrary::factory(),
            'accepted_by' => \App\Models\User\User::factory(),
            'documentable_type' => $this->faker->randomElement([
                \App\Models\Vendor\Vendor::class,
                \App\Models\Building\Building::class,
                \App\Models\Building\FlatTenant::class,
            ]),
            'documentable_id' => 1
                
            
        ];
    }
}
