<?php

namespace App\Models\Meta;


use App\Support\Meta\Fields\Field;
use App\Support\Meta\Wheres\Where;
use App\Support\Meta\Model;
use App\Support\Meta\Relation;
use App\Support\Meta\Types\Type;

readonly class Employee extends Model
{
    public function __construct()
    {
        parent::__construct(
            table: 'employees',
            primaryKey: 'id',
            fields: [
                Field::column('id', Type::integer()),
                Field::column('name', Type::text()),
                Field::column('salary', Type::subunit(2)),
                Field::column('manager_id', Type::integer()->nullable()),

                Field::computed('double_salary', Type::subunit(2), ['salary'], get: fn (string $salary) => "$salary * 2"),
                Field::computed('quadruple_salary', Type::subunit(2), ['double_salary'], get: fn (string $salary) => "$salary * 2"),
                Field::computed('manager_double_salary', Type::subunit(2), ['manager.salary'], get: fn (string $salary) => "$salary * 2"),
                Field::computed('double_equity_amount', Type::subunit(2), ['equity.amount'], get: fn (string $equityAmount) => "$equityAmount * 2"),
            ],
            relations: [
                Relation::make('equity', Equity::class, Where::equals('id', 'equity.employee_id')),
                Relation::make('manager', Employee::class, Where::equals('manager_id', 'manager.id')),
                Relation::make('manager_equity', Equity::class, Where::equals('manager.id', 'manager_equity.employee_id')),
            ],
        );
    }
}
