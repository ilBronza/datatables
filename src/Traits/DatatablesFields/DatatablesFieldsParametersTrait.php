<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsParametersTrait
{
    static $overrideGuardedFields = [
        'defaultFilterType'
    ];

    public function getName()
    {
        return $this->name;
    }

    public function manageRefreshRow(array $parameters)
    {
        if(isset($parameters['refreshRow']))
            $this->setDataAttribute('refreshRow', $parameters['refreshRow']);
    }

    public function setParameters(array $parameters)
    {
        foreach($parameters as $name => $parameter)
            if(! in_array($name, ['htmlClasses', 'data']))
                $this->setParameter($name, $parameter);

        $this->manageRefreshRow($parameters);
    }

    public function setParameter($name, $parameter)
    {
        if(! is_int($name))
            $this->$name = $parameter;

        else
            $this->$parameter = [];
    }

    public function setDataAttribute(string $name, string $value)
    {
        $this->data[$name] = $value;
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

        if($this->parent)
            foreach($this->headerData as $name => $value)
                $this->parent->setHeaderDataAttribute($name, $value);
    }

    public function setHeaderDataAttribute(string $name, $value)
    {
        if($this->parent)
            $this->parent->setHeaderDataAttribute($name, $value);

        $this->headerData[$name] = $value;
    }

    public function parseFieldSpecificHeaderData()
    {
        
    }

    public function getHeaderData()
    {
        $this->parseFieldSpecificHeaderData();

        return $this->headerData;
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

    public function hasDoublerClass()
    {
        return in_array('doubler', $this->headerHtmlClasses);
    }

    public function checkConfirmMessage()
    {
        if(! ($this->confirmMessage ?? false))
            return ;

        $this->setHeaderDataAttribute('confirm', __($this->confirmMessage));
    }

    public function hasForceValue()
    {
        return isset($this->forceValue);
    }
}
