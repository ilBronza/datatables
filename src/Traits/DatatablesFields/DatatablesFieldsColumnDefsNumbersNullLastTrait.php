<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsColumnDefsNumbersNullLastTrait
{
	public bool $nullLast = true;

	public function getCustomColumnDefSingleSortResult()
	{
		if($this->nullLast)
			return "
				if(item === null)
					item = Number.MAX_SAFE_INTEGER;
			";

		return "";
	}	
}
