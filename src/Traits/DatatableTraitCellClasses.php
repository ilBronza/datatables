<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\DatatableField;

trait DatatableTraitCellClasses
{
	/**
	 * parse all fields and check if proper columnDef has been set
	 * if not, it sets it
	 */
	public function parseCellClasses()
	{
		foreach($this->fieldsGroups as $fieldsGroup)
			foreach($fieldsGroup->fields as $field)
				$this->checkFieldCellClasses($field);
	}

	/**
	 * check if all field's columnDefs are set in datatable
	 * if not, set them
	 *
	 * @param datatableField $field
	 */
	private function checkFieldCellClasses(DatatableField $field)
	{
		if(! $cellClass = $field->getCellClass())
			return false;

		$this->columnCellClasses[] = $cellClass;
	}
}