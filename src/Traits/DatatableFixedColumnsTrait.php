<?php

namespace IlBronza\Datatables\Traits;

use function config;

trait DatatableFixedColumnsTrait
{
	public function getFixedColumnsLeft() : int
	{
		return $this->fixedColumnsLeft;
	}

	public function setFixedColumnsLeft(int $fixedColumnsLeft) : void
	{
		$this->fixedColumnsLeft = $fixedColumnsLeft;
	}

	public function getFixedColumnsRight() : int
	{
		return $this->fixedColumnsRight;
	}

	public function setFixedColumnsRight(int $fixedColumnsRight) : void
	{
		$this->fixedColumnsRight = $fixedColumnsRight;
	}

	public function hasFixedColumnsEnabled() : bool
	{
		return config('datatables.fixedColumns.enabled', false);
	}

	public function hasFixedColumns() : bool
	{
		if(! $this->hasFixedColumnsEnabled())
			return false;

		if(!! $this->getFixedColumnsLeft())
			return true;

		if(!! $this->getFixedColumnsRight())
			return true;

		return false;
	}
}