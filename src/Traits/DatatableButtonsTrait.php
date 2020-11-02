<?php

namespace IlBronza\Datatables\Traits;

trait DatatableButtonsTrait
{
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

        $this->buttons[] = $addingButton;
    }

    public function getButtons()
    {
        return $this->buttons;
    }
}