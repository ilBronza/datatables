<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldArray extends DatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;

	public function transformValue($value)
	{
		$result = [];

		foreach($value as $_value)
		{
			if($_value === false)
				$_value = 0;

			$result[] = $_value;
		}


		return implode(',', $result);
	}
}