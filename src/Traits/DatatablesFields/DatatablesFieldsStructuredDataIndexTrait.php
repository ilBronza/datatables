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
 *
 * labelPosition indica dove sta il testo mostrato all'utente quando è diverso dal valore
 * (es. editor.select ritorna [key, value, label] quindi label sta su [2])
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

	public function getLabelStructuredDataIndexString() : ?string
	{
		if(($labelPosition = $this->getLabelPosition()) !== null)
			return "[{$labelPosition}]";

		return null;
	}

	public function getLabelPosition() : null|int|string
	{
		return $this->labelPosition;
	}
}
