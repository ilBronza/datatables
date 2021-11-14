<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsFiltersTrait
{
    public function hasRangeFilter()
    {
        return !! $this->rangeFilter;
    }

    public function getFilterType()
    {
        return $this->filterType ?? $this->defaultFilterType;
    }

    public function getrangeFilterType()
    {
        if($this->rangeFilter === true)
            return 'normal';

        return $this->rangeFilter;
    }

    public function getRangeFilterJavascriptPlugin(string $tableId = null)
    {
        $view = 'datatables::datatablesFields.filters.scripts._range' . ucfirst($this->getrangeFilterType());

        return view($view, [
            'tableId' => $tableId,
            'field' => $this
        ])->render();
    }

    public function getJqueryFilterEvents()
    {
        return $this->jqueryFilterEvents;
    }

    public function getJqueryFilterEventsString()
    {
        return implode(" ", $this->getJqueryFilterEvents());
    }

    public function canDrawTable()
    {
        return $this->canDrawTable;
    }

    public function canDrawKeyup()
    {
        if(! $this->canDrawTable())
            return false;

        return $this->table->drawOnFieldsEvents();
    }
}
