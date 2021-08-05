<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldFetcher extends DatatableFieldLink
{
	public $icon = false;
	public $tarbet = '_blank';
	public $htmlTag = 'span';

	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->getKey();

		return [
			$value->getKey(),
			$value->{$this->textParameter}
		];
	}

}
