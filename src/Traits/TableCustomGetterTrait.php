<?php

namespace App\Traits\Datatables;

trait TableCustomGetterTrait
{
    public function getValueFor(string $parameter)
    {
        return $this->$parameter;
    }
}