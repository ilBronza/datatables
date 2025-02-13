<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

/**
 * this trait describes where the data value is stored in json array data cell for datatables
 *
 * ex. in editor we have return [key, value] so value is on [1] and key is on [0]
 *
 * is based on this properties:
 *
 * on flat field
 *
 *      public null|int|string $keyPosition = null;
 *      public null|int|string $valuePosition = null;
 *
 * on editor field
 *      public null|int|string $keyPosition = 0;
 *      public null|int|string $valuePosition = 1;
 */
trait DatatablesFieldsStructuredDataIndexTrait
{
	public function getStructuredDataIndexString() : ?string
	{
		if(($valuePosition = $this->getValuePosition()) !== null)
			return "[{$valuePosition}]";

		return null;
	}

	public function getValuePosition() : null|int|string
	{
		return $this->valuePosition;
	}
}
