<?php

namespace App\Models\Meta;
use App\Support\Meta\Fields\Field;
use App\Support\Meta\Wheres\Where;
use App\Support\Meta\Model;
use App\Support\Meta\Relation;
use App\Support\Meta\Types\Type;

readonly class Equity extends Model
{
    public function __construct()
    {
        parent::__construct(
            table: 'equities',
            primaryKey: 'id',
            fields: [
                Field::column('id', Type::integer()),
                Field::column('employee_id', Type::integer()),
                Field::column('is_eligible', Type::boolean()),
                Field::column('amount', Type::subunit(2)),
            ],
            relations: [
                Relation::make('employee', Employee::class, Where::equals('employee_id', 'employee.id')),
            ],
        );
    }
}
