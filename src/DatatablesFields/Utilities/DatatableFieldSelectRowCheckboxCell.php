<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableField;

/**
 * Checkbox di selezione riga piazzabile in una colonna qualsiasi, non solo la prima.
 * Non salva niente sull'elemento: spuntarlo seleziona la riga chiamando le API Select
 * di datatables, e la selezione fatta altrove (prima colonna, seleziona tutti,
 * solo selezionate) ricade sul checkbox.
 *
 * Esempio:
 * 'seleziona' => [
 *     'type' => 'utilities.selectRowCheckboxCell',
 * ],
 */
class DatatableFieldSelectRowCheckboxCell extends DatatableField
{
	public ?string $translationPrefix = 'datatables::fields';

	public bool $requiresRowSelectCheckbox = true;

	public $width = '20px';
	public $filterable = false;
	public $sortable = false;
	public $showLabel = false;

	public function transformValue($value)
	{
		return;
	}

	public function getCustomColumnDefSingleResult()
	{
		return "

		item = '<input type=\"checkbox\" class=\"uk-checkbox ib-dt-row-select-proxy\" />';

		";
	}

	public function getCustomColumnDefSingleResultExport()
	{
		return "

		item = '';

		";
	}
}
