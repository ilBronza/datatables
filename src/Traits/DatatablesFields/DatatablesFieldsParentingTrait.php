<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\DatatableField;

trait DatatablesFieldsParentingTrait
{
	public function addChildField()
	{
		$namePortions = explode(".", $this->name);

		array_shift($namePortions);

		$this->child = static::createByType(
			implode(".", $namePortions),
			$this->childParameters['type'],
			$this->childParameters,
			0,
			$this,
			$this->table
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