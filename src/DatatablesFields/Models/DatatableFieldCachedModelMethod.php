<?php

namespace IlBronza\Datatables\DatatablesFields\Models;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Support\Str;

class DatatableFieldCachedModelMethod extends DatatableField
{
	public $method;

	public function transformValue($value)
	{
		if(! $model = $this->modelClass::findCached($value))
			return null;

		return $model->{$this->method};
	}
}