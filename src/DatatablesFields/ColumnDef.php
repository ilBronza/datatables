<?php

namespace IlBronza\Datatables\DatatablesFields;

class ColumnDef
{
    public $name;
    public $values;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->values = [];
    }

    /**
     * add column index to definition value array
     *
     * @param string $definitionValue
     * @param int $index
     */
    public function addIndexToValue(string $definitionValue, int $index)
    {
        if(! isset($this->values[$definitionValue]))
            $this->values[$definitionValue] = [];

        array_push($this->values[$definitionValue], $index);
    }    
}