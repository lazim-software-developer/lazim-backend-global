<?php

namespace Database\Factories\Building;

use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Building\FlatTenant;
use App\Models\Vendor\Vendor;
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
                Vendor::class,
                Building::class,
                FlatTenant::class,
            ]),
            'documentable_id' => $this->faker->randomNumber(1, 100),
                
            
        ];
    }
}
