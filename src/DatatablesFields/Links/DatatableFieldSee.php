<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldSee extends DatatableFieldLink
{
	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->getShowUrl();

		return [
			$value->getShowUrl(),
			$value->{$this->textParameter}
		];
	}
}
