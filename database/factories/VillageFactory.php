<?php

namespace Database\Factories;

use App\Models\Kecamatan;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

class VillageFactory extends Factory
{
    protected $model = Village::class;

    public function definition(): array
    {
        return [
            'kecamatan_id' => Kecamatan::factory(),
            'name'         => 'Desa ' . $this->faker->unique()->word(),
            'code'         => $this->faker->unique()->numerify('####'),
            'type'         => $this->faker->randomElement(['desa', 'kelurahan']),
            'is_active'    => true,
        ];
    }
}
