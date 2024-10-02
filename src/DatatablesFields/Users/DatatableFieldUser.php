<?php

namespace IlBronza\Datatables\DatatablesFields\Users;

use IlBronza\AccountManager\Models\User;
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
				return User::gpc()::find($value);
			}
		);
	}
}