<?php

namespace IlBronza\Datatables;

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

    // /**
    //  * set given columnDefs ['definition' => 'value'] parameter by index
    //  *
    //  * @param int|array $index
    //  * @param array $columnDefs
    //  *
    //  */
    // public function setColumnDefsByIndexes($index, array $columnDefs)
    // {
    //  foreach($columnDefs as $definition => $value)
    //      $this->setColumnDefByIndex($index, $definition, $value);
    // }

    // /**
    //  * create definitions, values and merge the indexes
    //  *
    //  * @param int|array $index
    //  * @param string $definition
    //  * @param string $value
    //  *
    //  */
    // public function setColumnDefByIndexes($index, string $definition, string $value)
    // {
    //  if(! isset($this->columnDefs[$definition]))
    //      $this->columnDefs[$definition] = [];

    //  if(! isset($this->columnDefs[$definition][$value]))
    //      $this->columnDefs[$definition][$value] = [];

    //  if(is_int($index))
    //      $index = [$index];

    //  $this->columnDefs[$definition][$value] = array_merge($this->columnDefs[$definition][$value], $index);
    // }

    // /**
    //  * set columnDefs by fields parameters
    //  */
    // private function setColumnDefs()
    // {
    //  if(! $this->mustShowRowToggler())
    //      $this->removeColumnDef('orderDataType', [0]);

    //  else
    //      $this->setColumnDefsByIndex(0, ['orderDataType' => 'dom-checkbox']);
    // }

}

