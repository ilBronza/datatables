<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsSortingTrait
{
    public function isSortable()
    {
    	return $this->sortable;
    }
}
