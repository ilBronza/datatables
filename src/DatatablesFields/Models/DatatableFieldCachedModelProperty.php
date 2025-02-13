<?php

namespace IlBronza\Datatables\DatatablesFields\Models;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Support\Str;

class DatatableFieldCachedModelProperty extends DatatableField
{
	public function transformValue($value)
	{
		if(! $value)
			return null;

		if(! $model = $this->modelClass::findCached($value))
			return null;

		return $model->{$this->property};
	}
}