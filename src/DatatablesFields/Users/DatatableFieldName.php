<?php

namespace IlBronza\Datatables\DatatablesFields\Users;

class DatatableFieldName extends DatatableFieldUser
{
	public function transformValue($value)
	{
		if(! $user = $this->getUser($value))
			return ;

		return $user->getName();
	}
}