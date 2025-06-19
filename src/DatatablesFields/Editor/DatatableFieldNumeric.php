<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

class DatatableFieldNumeric extends DatatableFieldEditor
{
	public function transformValue($value)
	{
		if (isset($this->solveElement))
			$value = $this->getFieldCellDataValue($this->name, $value);

		if (! $this->requireElement())
			return $value;

		$this->element = $value;

		if ($this->editorValueFunction)
			return [
				$this->element->getKey(),
				$this->element->{$this->editorValueFunction}()
			];

		$propertyName = $this->editorProperty ?? $this->name;

		return [
			$this->element->getKey(),
			$value->{$propertyName}
		];
	}

	use EditorSingleFieldTrait;

	//datatable type to be sorted in datatable
	public $datatableType = 'numeric';
	public $fieldType = 'numeric';
	public $digits = false;

	public $width = '5em';

    public function getValueString()
    {
    	if($this->digits !== false)
        	return " inputmode=\"decimal\"  data-originalvalue=\"' + item[1] + '\" value=\"' + ((item[1])? parseFloat(item[1]).toFixed({$this->digits}) : '') + '\" ";

        return " inputmode=\"decimal\"  data-originalvalue=\"' + item[1] + '\" value=\"' + item[1] + '\" ";
    }
}