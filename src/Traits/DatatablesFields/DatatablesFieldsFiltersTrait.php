<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsFiltersTrait
{
    public function hasRangeFilter()
    {
		if(! config('datatables.rangeFilter.enabled', false))
			return false;

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
        if ($this->jqueryFilterEvents !== null) {
            return $this->jqueryFilterEvents;
        }
        $trigger = config('datatables.filterTrigger', 'enter');

        if ($trigger === 'blur')
            return ['change', 'blur'];

        if ($trigger === 'keyup')
            return ['change', 'keyup'];

        // 'enter': virtual event handled in JS, fires only on Enter key
        return ['change', 'enter'];
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
