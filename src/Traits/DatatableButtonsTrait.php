<?php

namespace IlBronza\Datatables\Traits;

trait DatatableButtonsTrait
{
    private function initializeButtons()
    {
        $this->buttons = [];

        if($this->hasCopyButton())
            $this->buttons[] = [
                'extend' => 'copy',
                'exportOptions' => ['orthogonal' => 'export']
            ];

        if($this->hasCsvButton())
            $this->buttons[] = [
                'extend' => 'csv',
                'exportOptions' => ['orthogonal' => 'export']
            ];
    }

    public function hasDoublerButton() : bool
    {
        if(! $this->hasDoublerFields())
            return false;

        return config('datatables.defaultButtons.doubler', true);
    }

    public function hasSelectFilteredButton() : bool
    {
        if(! $this->hasSelectRowCheckboxes())
            return false;

        return config('datatables.defaultButtons.selectFiltered', true);
    }

    public function hasSearchButton() : bool
    {
        return config('datatables.defaultButtons.search', true);
    }

    public function hasCopyButton() : bool
    {
        return config('datatables.defaultButtons.copy', true);
    }

    public function hasCsvButton() : bool
    {
        return config('datatables.defaultButtons.csv', true);
    }

    public function setButtons(array $buttons)
    {
        return $this->buttons = $buttons;
    }

    public function removeButton($removingButton)
    {
        foreach($this->buttons as $key => $button)
            if($button == $removingButton)
                unset($this->buttons[$key]);
    }

    public function addButton($addingButton)
    {
        foreach($this->buttons as $key => $button)
            if($button == $addingButton)
                return false;

        $addingButton->setTableId($this->getId());

        $this->buttons[] = $addingButton;
    }

    public function getButtons()
    {
        return $this->buttons;
    }
}