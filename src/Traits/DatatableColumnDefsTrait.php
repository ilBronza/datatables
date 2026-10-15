<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\DatatablesFields\ColumnDef;
use IlBronza\Datatables\Contracts\DatatableFieldContract;

trait DatatableColumnDefsTrait
{
    public $customColumnDefs = [];

    private function addCustomColumnDefsRenderingMethods(DatatableFieldContract $field)
    {
        if($alternativeRenderAs = $field->getRenderAsType())
            $field = $this->replicateRenderAsField('fakeRenderingField', $alternativeRenderAs, $field);

        // $fieldIndex = $field->getIndex();

        // if($field->name == 'fakeRenderingField')
        //     dd("fakeRenderingField");

        if($customColumnDef = $field->getCustomColumnDef())
            return $this->customColumnDefs[] = $customColumnDef;
    }


    /**
     * return table columndef by its name if it exists, otherwhise create it
     *
     * @param string $definition
     * @return \columnDef
     */
    private function getColumnDefByDefinition($definition) : ColumnDef
    {
        if(! isset($this->columnDefs[$definition]))
            $this->columnDefs[$definition] = new ColumnDef($definition);

        return $this->columnDefs[$definition];
    }

    /**
     * parse all fields to build columnDef
     **/
    public function parseColumnDefs()
    {
        foreach($this->getFields() as $field)
            $this->checkFieldColumnDefs($field);
    }    

    /**
     * check if all field's columnDefs are set in datatable
     * if not, set them
     *
     * @param datatableField $field
     */
    private function checkFieldColumnDefs(DatatableFieldContract $field)
    {
        //get field base columnDefs
        $fieldColumnDefs = $field->getColumnDefs();

        foreach($fieldColumnDefs as $definition => $value)
        {
            $columnDef = $this->getColumnDefByDefinition($definition);
            $columnDef->addIndexToValue($value, $field->getIndex());
        }

        $this->addCustomColumnDefsRenderingMethods($field);
    }

    public function getCreatedRowScripts()
    {
        return $this->createdRowScripts;
    }
}

