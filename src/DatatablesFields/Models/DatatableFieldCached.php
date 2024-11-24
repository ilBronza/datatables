<?php

namespace IlBronza\Datatables\DatatablesFields\Models;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Support\Str;

class DatatableFieldCached extends DatatableField
{
	public $property = 'name';

    public function transformValue($value)
    {
		$item = cache()->remember(
			Str::slug($this->modelClass . $value),
			3600,
			function() use($value)
			{
				return $this->modelClass::find($value);
			}
		);

		return $this->getItemValue($item);
    }

	public function getItemValue($item)
	{
		return $item->{$this->property};
	}

}