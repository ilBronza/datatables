<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\ColumnOption;
use IlBronza\Datatables\DatatableField;

trait DatatableTraitOptions
{
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

	private function normalizeOrderOption()
	{
		if(! isset($this->options['order']))
			return false;

		ksort($this->options['order']);
	}

	/**
	 * parse all fields and check if proper columnDef has been set
	 * if not, it sets it
	 */
	public function parseOptions()
	{
		foreach($this->fieldsGroups as $fieldsGroup)
			foreach($fieldsGroup->fields as $field)
				$this->checkFieldOptions($field);

		$this->normalizeOrderOption();
	}

	/**
	 * check if all field's columnDefs are set in datatable
	 * if not, set them
	 *
	 * @param \newField $field
	 */
	private function checkFieldOptions(DatatableField $field)
	{
		$fieldColumnOptions = $field->getColumnOptions();

		foreach($fieldColumnOptions as $definition => $value)
		{
			$columnOption = $this->getColumnOptionByDefinition($definition);

			if($definition == 'order')
				$this->addIndexToOrder($value, $field->absoluteIndex);

			else
				$columnOption->addIndexToValue($value, $field->absoluteIndex);
		}
	}


	//"order": [[ 4, 'asc' ], [ 1, 'desc' ]],

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

}