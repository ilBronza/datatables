<?php

namespace IlBronza\Datatables\DatatablesFields\Numbers;

use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldNumeric;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\ConditionallyDisabledEditorTrait;

/**
 * Editor numerico disabilitabile in base al model della singola riga.
 */
class DatatableFieldNumberConditionallyDisabled extends DatatableFieldNumeric
{
	use ConditionallyDisabledEditorTrait;

	protected function getConditionallyDisabledCellIndex() : int
	{
		return 2;
	}

	protected function getConditionallyDisabledHtmlTag() : string
	{
		return 'input';
	}
}
