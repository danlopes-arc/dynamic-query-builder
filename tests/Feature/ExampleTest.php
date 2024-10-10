<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Meta\Employee;
use App\Support\Meta\Fields\Field;
use App\Support\Reports\Header;
use App\Support\Reports\Report;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();

        $employee = new Employee();

        $rep = new Report(
            name: 'Test Report',
            model: Employee::class,
            headers: [
                new Header('id', 'ID'),
                new Header('manager_quadruple_salary', 'Manager Quadruple Salary', 'manager.quadruple_salary'),
                new Header('manager_equity_amount', 'Manager Equity Amount', 'manager.equity.amount'),
                new Header('is_equity_eligible', 'Is Equity Eligible', 'manager.equity.is_eligible'),
                new Header('manager_equity_amount_2', 'Manager Equity Amount 2', 'manager_equity.amount'),
            ]
        );

        $records = $rep->orderBy([
            'manager_equity_amount' => 'asc',
            'id' => 'desc',
        ])->getRecords();


        $columns = collect([
            'employee_id' => 'id',
            'manager_quadruple_salary' => 'manager.quadruple_salary',
        ]);

        /** @var Collection<string, Field> $fields */
        $fields = collect();
        foreach ($columns->values()->all() as $path) {
            $fields = $fields->merge(
                Field::getDependentFields($path, $employee, collect(), 'employee')->all()
            );
        }

        $selects = $columns->map(function (string $path, string $alias) use ($fields) {
            $relations = array_slice(explode('.', $path), 0, -1);
            $sql = $fields->get("employee.$path")->toSql($fields, implode('.', ['employee', ...$relations]));
            return "$sql as $alias";
        });

        $this->assertTrue(true);
    }
}
