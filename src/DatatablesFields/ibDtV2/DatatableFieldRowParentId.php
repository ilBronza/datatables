<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2;

use InvalidArgumentException;

/** A structural field: its presence enables the client-side row hierarchy. */
class DatatableFieldRowParentId extends DatatableField
{
	public bool $sortable = false;
	public bool $filterable = false;
	public bool $flatOnOrder = false;

	public function requiresRowId() : bool { return true; }
	public function getValuePosition() : string { return 'value'; }
	public function getWidthFromConfig() : string
	{
		return config('datatables.datatableFieldWidths.ibDtV2.datatableFieldRowParentId', '6em');
	}

	public function getTranslatedName() : string
	{
		return $this->translatedName ?? __('datatables::rowHierarchy.title');
	}

	public function transformValue($value)
	{
		$value = $this->hasForceValue() ? $this->forceValue : $this->getFieldCellDataValue($this->name, $value);
		$cell = is_array($value) ? $value : ['value' => $value];
		if (! array_key_exists('value', $cell)
			|| ($cell['value'] !== null && ! is_int($cell['value']) && ! is_string($cell['value'])))
			throw new InvalidArgumentException('row_parent_id must be null, an integer or a string, or an array with a value key.');
		if (array_key_exists('row_opened', $cell) && ! is_bool($cell['row_opened']))
			throw new InvalidArgumentException('row_opened must be a boolean.');

		$parent = ['value' => $cell['value'], 'row_opened' => $cell['row_opened'] ?? false];
		if (($cell['row_group'] ?? false) === true) $parent['row_group'] = true;
		return $parent;
	}

	public function getColumnDefs() : array
	{
		return array_replace(parent::getColumnDefs(), ['orderable' => false, 'searchable' => false]);
	}

	public function getCustomColumnDef()
	{
		$labels = $this->javascriptLiteral(__('datatables::rowHierarchy'));
		$flatOnOrder = $this->javascriptLiteral($this->flatOnOrder);
		return "{
			targets: [{$this->getIndex()}],
			ibDtRowParent: {$labels},
			ibDtFlatOnOrder: {$flatOnOrder},
			{$this->compileCellAttributes()}
			render: function(item, type) {
				return type === 'display' ? '<span class=\"ib-dt-tree-controls\"></span>' : '';
			}
		}";
	}
}
