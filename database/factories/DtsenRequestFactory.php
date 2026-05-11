<?php

namespace Database\Factories;

use App\Enums\DtsenStatus;
use App\Models\DtsenRequest;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

class DtsenRequestFactory extends Factory
{
    protected $model = DtsenRequest::class;

    public function definition(): array
    {
        $village = Village::factory()->create();
        $user    = User::factory()->create(['village_id' => $village->id]);

        return [
            'reference_number' => DtsenRequest::generateReferenceNumber(),
            'village_id'       => $village->id,
            'user_id'          => $user->id,
            'status'           => DtsenStatus::DRAFT->value,
            'purpose'          => $this->faker->sentence(),
            'notes'            => null,
            'processed_by'     => null,
            'processed_at'     => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(['status' => DtsenStatus::SUBMITTED->value]);
    }

    public function onProcess(): static
    {
        return $this->state(['status' => DtsenStatus::ON_PROCESS->value]);
    }

    public function ready(): static
    {
        return $this->state(['status' => DtsenStatus::READY->value]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => DtsenStatus::CANCELLED->value]);
    }
}
