<?php

namespace IlBronza\Datatables\Traits;

trait DatatableFiltersTrait
{
	public function hasRemoveFiltersButton() : bool
	{
		if(! is_null($this->removeFiltersButton))
			return $this->removeFiltersButton;

		return config('datatables.removeFiltersButton', false);
	}
}

