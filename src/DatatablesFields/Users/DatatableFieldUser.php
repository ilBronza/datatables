<?php

namespace IlBronza\Datatables\DatatablesFields\Users;

use App\Models\User;
use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldUser extends DatatableField
{
	public function getUser($value)
	{
		return cache()->remember(
			'user' . $value,
			3600,
			function() use($value)
			{
				return User::find($value);
			}
		);
	}
}