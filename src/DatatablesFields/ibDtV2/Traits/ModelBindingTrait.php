<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use LogicException;

trait ModelBindingTrait
{
	public ?string $modelClass = null;
	public ?object $placeholderElement = null;
	public ?object $element = null;
	public mixed $forceValue = null;
	public ?bool $solveElement = null;

	public function getPlaceholderElement() : object
	{
		if ($this->placeholderElement !== null)
			return $this->placeholderElement;
		if ($this->modelClass !== null)
			return $this->placeholderElement = new $this->modelClass();
		if ($this->parent !== null)
			throw new LogicException('V2 child field "' . $this->name . '" requires modelClass or placeholderElement.');
		if ($this->table !== null)
			return $this->table->getPlaceholderElement();

		throw new LogicException('V2 field "' . $this->name . '" requires a modelClass, placeholderElement or table.');
	}

	public function hasForceValue() : bool { return $this->forceValue !== null; }
	public function requireElement() : bool { return true; }
	public function getOverridingValueMethod() : ?string { return null; }

	public function getFieldCellDataValue(string $fieldName, $element)
	{
		foreach (explode('.', $fieldName) as $property)
			if (! str_starts_with($property, 'mySelf'))
				$element = data_get($element, $property);

		return $element;
	}
}
