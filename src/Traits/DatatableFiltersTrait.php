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

	public function setFooterFilters(bool $footerFilters)
	{
		$this->footerFilters = $footerFilters;
	}

	public function hasFooterFilters() : bool
	{
		if(isset($this->footerFilters))
			return $this->footerFilters;

		return config('datatables.footerFilters');
	}
}

