<?php

namespace IlBronza\Datatables\Contracts;

use IlBronza\Datatables\DatatableFieldsGroup;

/** The table boundary: implementations own their configuration and rendering. */
interface DatatableFieldContract
{
	public function getName() : ?string;
	public function getFieldName();
	public function getIndex();
	public function setIndex(?int $index = null);
	public function setFieldsGroup(DatatableFieldsGroup $fieldsGroup);
	public function isAllowed();
	public function isRowId();
	public function isEditor() : bool;
	public function isBulkEditable() : bool;
	public function requiresSelectRowCheckboxes() : bool;
	public function getSaveSettings() : array;
	public function getInlineFieldType() : string;
	public function getColumnDefs();
	public function getColumnOptions();
	public function getCreatedRowScripts();
	public function getCustomColumnDef();
	public function getRenderAsType();
	public function requireElement();
	public function transformValue($value);
	public function getFieldCellDataValue(string $fieldName, $element);
	public function getOverridingValueMethod() : ?string;
}
