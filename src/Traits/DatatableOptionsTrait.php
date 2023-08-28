<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\ColumnOption;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Support\Str;

trait DatatableOptionsTrait
{
    public function canHideColumns()
    {
        if(! empty($this->canHideColumns))
            return $this->canHideColumns;

        return config('datatables.hideColumns');
    }

    public function setPageLength($pageLength)
    {
        $this->pageLength = $pageLength;
    }

    public function getPageLength()
    {
        return $this->pageLength;
    }

    public function setAutomaticCaption()
    {
        if($this->getCaption())
            return ;

        $this->setCaption(
            str_replace("-", " ", $this->getName())
        );
    }

    public function setCaption(string $caption)
    {
        $this->caption = $caption;
    }

    public function hasFixedTableHeader()
    {
        if(! empty($this->fixedHeader))
            return $this->fixedHeader;

        return config('datatables.fixedHeader');        
    }

    public function getCaption()
    {
        return $this->caption ?? false;
    }

    private function normalizeOrderOption()
    {
        if(! isset($this->options['order']))
            return false;

        ksort($this->options['order']);
    }

    private function addCreatedRowScript(string $script)
    {
        $this->createdRowScripts[] = $script;
    }

    private function checkFieldRowCreationScripts(DatatableField $field)
    {
        if($script = $field->getCreatedRowScripts())
            $this->addCreatedRowScript($script);
    }

    /**
     * check if all field's columnDefs are set in datatable
     * if not, set them
     *
     * @param \newField $field
     */
    private function checkFieldOptions(DatatableField $field)
    {
        $this->checkFieldRowCreationScripts($field);

        $fieldColumnOptions = $field->getColumnOptions();

        foreach($fieldColumnOptions as $definition => $value)
        {
            $columnOption = $this->getColumnOptionByDefinition($definition);

            if($definition == 'order')
                $this->addIndexToOrder($value, $field->getIndex());

            else
                $columnOption->addIndexToValue($value, $field->getIndex());
        }
    }


    private function addIndexToOrder(array $value, int $index)
    {
        if(! isset($this->options['order']))
            $this->options['order'] = [];

        $priority = $value['priority']?? 0;
        $type = $value['type']?? 'asc';

        if(! isset($this->options['order'][$priority]))
            $this->options['order'][$priority] = [$index, $type];
    }

    /**
     * return table columnOption by its name
     *
     * @param string $definition
     *
     * @return \columnOption
     */
    private function getColumnOptionByDefinition($definition)
    {
        if(! isset($this->columnOptions[$definition]))
            $this->columnOptions[$definition] = $this->newColumnOption($definition);

        return $this->columnOptions[$definition];
    }

    /**
     * declared here because trait didn't support it
     *
     * @param string $definition
     *
     * @return object \columnOption
     */
    private function newColumnOption($definition)
    {
        return new ColumnOption($definition);
    }

    /**
     * parse all fields and check if proper columnDef has been set
     * if not, it sets it
     */
    public function parseOptions()
    {
        foreach($this->fieldsGroups as $fieldsGroup)
            if(isset($fieldsGroup->fields))
                foreach($fieldsGroup->fields as $field)
                    $this->checkFieldOptions($field);

        $this->normalizeOrderOption();
    }


}