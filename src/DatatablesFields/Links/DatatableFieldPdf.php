<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldPdf extends DatatableFieldLink
{
	public $icon = 'file-pdf';
	public $target = '_blank';

	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->{$this->function}();

		return [
			$value->{$this->function}(),
			$value->{$this->textParameter}
		];
	}
}
