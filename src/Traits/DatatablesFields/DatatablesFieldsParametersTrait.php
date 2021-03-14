<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Spatie\Permission\Models\Role;

trait DatatablesFieldsParametersTrait
{
    public function getName()
    {
        return $this->name;
    }

    public function setParameters(array $parameters)
    {
        // $this->setParameterByName('view', $parameters, true);

        foreach($parameters as $name => $parameter)
            $this->setParameter($name, $parameter);
    }

    private function setParameter($name, $parameter)
    {
        if(! is_int($name))
            $this->$name = $parameter;

        else
            $this->$parameter = [];
    }

}
