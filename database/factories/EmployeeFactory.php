<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employee> */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'salary' => $this->faker->numberBetween(1000_00, 9000_00),
        ];
    }
}
