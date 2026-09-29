<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\Traits\DatatableFieldsTrait;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HoldFieldsGroupDefinitionsTest extends TestCase
{
    public function test_hold_fields_are_hidden_and_exposed_as_a_separate_group() : void
    {
        $field = (new ReflectionClass(DatatableField::class))->newInstanceWithoutConstructor();
        $field->index = 2;
        $field->holdFieldsGroupsDefinitions = ['gross_costs'];

        $this->assertSame(['gross_costs'], $field->getHoldFieldsGroupsDefinitions());
        $this->assertFalse($field->isVisible());

        $table = new class($field)
        {
            use DatatableFieldsTrait;

            public Collection $fields;

            public function __construct(DatatableField $field)
            {
                $this->fields = collect([$field]);
            }
        };

        $definitions = $table->getFieldsGroupsDefinitions();

        $this->assertSame([2], $definitions['gross_costs']['columnIndexes']);
        $this->assertTrue($definitions['gross_costs']['holdOnly']);
    }
}
