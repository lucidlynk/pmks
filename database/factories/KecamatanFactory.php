<?php

namespace Database\Factories;

use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

class KecamatanFactory extends Factory
{
    protected $model = Kecamatan::class;

    public function definition(): array
    {
        return [
            'name'      => 'Kecamatan ' . $this->faker->unique()->word(),
            'code'      => $this->faker->unique()->numerify('##'),
            'is_active' => true,
        ];
    }
}
