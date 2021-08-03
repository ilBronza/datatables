<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldEdit extends DatatableFieldLink
{
	public $icon = 'file-edit';

	public function transformValue($value)
	{
		if(! $value)
			return null;

		if(! $this->textParameter)
			return $value->getEditUrl();

		return [
			$value->getEditUrl(),
			$value->{$this->textParameter}
		];
	}
}
