<?php

namespace Tests\Unit;

use App\Models\Meta\Employee;
use App\Support\Meta\Fields\Field;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $employee = new Employee();


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
