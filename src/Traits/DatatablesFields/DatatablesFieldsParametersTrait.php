<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Spatie\Permission\Models\Role;

trait DatatablesFieldsParametersTrait
{
    static $overrideGuardedFields = [
        'defaultFilterType'
    ];

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

    static function getOverrideGuardedFields()
    {
        return static::$overrideGuardedFields;
    }

    static function extractOverrideableParametersNameByType(string $fieldType)
    {
        $className = static::getClassNameByType($fieldType);

        $field = new $className('placeholder');

        $parameters = array_keys(
            get_object_vars($field)
        );

        return array_diff($parameters, static::getOverrideGuardedFields());
    }
}
