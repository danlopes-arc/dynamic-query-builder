<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Equity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $subordinatesFactory = Employee::factory()
            ->count(4)
            ->has(Equity::factory());

        Employee::factory()
            ->count(2)
            ->has(Equity::factory())
            ->has($subordinatesFactory, 'subordinates')
            ->create();
    }
}
