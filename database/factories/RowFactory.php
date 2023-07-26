<?php

namespace Database\Factories;

use App\Models\Row;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Row>
 */
class RowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'date' => fake()->date
        ];
    }
}
