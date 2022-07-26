<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldFetcher extends DatatableFieldLink
{
	public $icon = false;
	public $tarbet = '_blank';
	public $requiresPlaceholderElement = true;
	public $htmlTag = 'span';

	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $this->_getKey($value);

		return [
			$this->_getKey($value),
			$value->{$this->textParameter}
		];
	}

}
