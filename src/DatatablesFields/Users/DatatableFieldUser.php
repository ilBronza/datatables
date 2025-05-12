<?php

namespace IlBronza\Datatables\DatatablesFields\Users;

use IlBronza\AccountManager\Models\User;
use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldUser extends DatatableField
{
	public function getUser($value)
	{
		if(! $value)
			return null;

		if((is_string($value))||(is_numeric($value)))
			return cache()->remember(
				'user' . $value,
				3600,
				function() use($value)
				{
					return User::gpc()::find($value);
				}
			);

		if($value instanceof User)
			return $value;

		dd('users is something else, implement code here');
	}
}