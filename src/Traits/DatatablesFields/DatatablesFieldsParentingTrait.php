<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\DatatableField;

trait DatatablesFieldsParentingTrait
{
	private function getChildName()
	{
		if(isset($this->property))
			return $this->property;

		$namePortions = explode(".", $this->name);

		array_shift($namePortions);

		return implode(".", $namePortions);
	}

	public function addChildField()
	{
		$childName = $this->getChildName();

		$this->child = static::createByType(
			$childName,
			$this->childParameters['type'],
			$this->childParameters,
			$index = 0,
			$parent = $this,
			$table = $this->table
		);
	}

	public function setParentField(DatatableField $parent = null)
	{
		$this->parent = $parent;
	}

	public function hasParent()
	{
		return !! ($this->parent ?? false);
	}

	public function getParentDataIndexString()
	{
		if(! $this->hasParent())
			return ;

		return $this->parent->getColumnDefDataIndexString();
	}
}