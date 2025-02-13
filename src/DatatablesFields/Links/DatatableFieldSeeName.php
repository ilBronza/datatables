<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldIconLink;

use function is_null;

class DatatableFieldSeeName extends DatatableFieldIconLink
{
	public $textParameter = 'name';
	public $faIcon = false;

    public function transformValue($value)
    {
		if(! $value)
			return [
				null,
				null
			];

		if($this->textParameter == 'name')
			return [
				$value->getShowUrl(),
				$value->getName()
			];

		return [
			$value->getShowUrl(),
			$value->{$this->textParameter}
		];

    }
}
