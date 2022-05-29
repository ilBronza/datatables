<?php

namespace IlBronza\Datatables\Castables;

use IlBronza\Datatables\Castables\Data;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


class DatatableUserDataCastable implements CastsAttributes
{
	public function get($model, $key, $value, $attributes)
    {
        return new Data($value);
    }

	public function set($model, $key, $value, $attributes)
    {
		return [
			'data' => json_encode($value)
		];
    }
}