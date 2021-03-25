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
            if(! in_array($name, ['htmlClasses', 'data']))
                $this->setParameter($name, $parameter);
    }

    private function setParameter($name, $parameter)
    {
        if(! is_int($name))
            $this->$name = $parameter;

        else
            $this->$parameter = [];
    }

    public function setDataAttributes(array $parameters = [])
    {
        $this->data = array_merge(
            $this->data ?? [],
            $parameters['data'] ?? []
        );
    }

    public function setHeaderDataAttributes(array $parameters = [])
    {
        $this->headerData = array_merge(
            $this->headerData ?? [],
            $parameters['headerData'] ?? []
        );
    }

    public function setHeaderData(string $name, $value)
    {
        $this->headerData[$name] = $value;
    }

    public function getHeaderData()
    {
        return $this->headerData;
    }

    static function getOverrideGuardedFields()
    {
        return static::$overrideGuardedFields;
    }

    static function extractOverrideableParametersNameByType(string $fieldType)
    {
        $className = static::getClassNameByType($fieldType);

        mori($className);

        $field = new $className('placeholder');

        $parameters = array_keys(
            get_object_vars($field)
        );

        return array_diff($parameters, static::getOverrideGuardedFields());
    }

    public function hasDoublerClass()
    {
        return in_array('doubler', $this->headerHtmlClasses);
    }

    public function checkConfirmMessage()
    {
        if(! ($this->confirmMessage ?? false))
            return ;

        $this->setHeaderData('confirm', __($this->confirmMessage));
    }

}
