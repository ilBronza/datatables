<?php

namespace IlBronza\Datatables\Traits;

trait DatatableTraitNavbars
{

	public function addButton(\dgButton $button)
	{
		$this->buttons[] = $button;
	}

	public function addSecondNavbarButton(\dgButton $button)
	{
		$this->secondNavbarButtons[] = $button;
	}

}
