<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;

trait CssTrait
{
	public array $cssRules = [];
	public ?string $thBackgroundColor = null;
	public ?string $tdBackgroundColor = null;

	public function getCssRules() : array
	{
		return $this->cssRules;
	}

	public function hasCss() : bool
	{
		return count($this->getCssRules()) > 0;
	}
}
