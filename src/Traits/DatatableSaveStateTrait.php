<?php

namespace IlBronza\Datatables\Traits;

trait DatatableSaveStateTrait
{
	public function getSaveState() : ? bool
	{
		return $this->saveState;
	}

	public function hasSaveState() : bool
	{
		if(! is_null($saveState = $this->getSaveState()))
			return $saveState;

		return !! config('datatables.saveState', false);
	}
}


















