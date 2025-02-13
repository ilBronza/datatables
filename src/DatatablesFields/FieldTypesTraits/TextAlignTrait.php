<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;

trait TextAlignTrait
{
	public ?string $textAlign = null;

	public function getTextAlign() : ?string
	{
		return $this->textAlign;
	}

	public function getAlignmentCssString() : ?string
	{
		if (($textAlign = $this->getTextAlign()) === null)
			return null;

		return "text-align: {$textAlign};";
	}
}