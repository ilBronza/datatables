<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Illuminate\Support\Str;

trait DatatablesFieldsIdentifiersTrait
{
    
    public function generateId()
    {
        return Str::slug($this->name . rand(0, 999999), '');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFieldName()
    {
        return $this->name;
    }

    public function setIndex(int $index = null)
    {
        if(! $index)
            $index = 0;

        $this->index = $index;
    }

    public function incrementIndex()
    {
        $this->index ++;
    }

    public function getIndex()
    {
        return $this->index;
    }

}
