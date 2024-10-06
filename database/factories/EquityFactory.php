<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Equity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Equity> */
class EquityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => fn () => Employee::factory(),
            'is_eligible' => $this->faker->boolean(),
            'amount' => $this->faker->numberBetween(100_00, 1000_00),
        ];
    }
}
