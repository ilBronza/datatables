<?php

namespace IlBronza\Datatables;

class DatatablesFieldOperation
{
    public string $type;
    public ? string $icon = null;
    public ? string $link = null;

    static function createFromString(string $name)
    {
        $fieldOperation = new static();

        return $fieldOperation->setType($name)->setName($name);
    }

    static function createFromArray(array $parameters, string $name)
    {
        $fieldOperation = new static();

        if(isset($parameters['type']))
            $fieldOperation->setType($parameters['type']);

        if(isset($parameters['icon']))
            $fieldOperation->setIcon($parameters['icon']);

        if(isset($parameters['link']))
            $fieldOperation->setLink($parameters['link']);

        return $fieldOperation->setName($name);
    }

    static function createFromParameters(string|array $parameters, string $name = null)
    {
        if(is_string($parameters))
            return static::createFromString($parameters);

        return static::createFromArray($parameters, $name);
    }

    public function setType(string $type) : static
    {
        $this->type = $type;

        return $this;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setLink(string $link) : static
    {
        $this->link = $link;

        return $this;
    }

    public function getLink() : ? string
    {
        return $this->link;
    }

    public function setIcon(string $icon) : static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon() : string
    {
        if($this->icon)
            return $this->icon;

        return $this->getIconByType();
    }

    private function getIconByType() : string
    {
        return match($this->getType())
        {
            'checkVisible' => 'check',
            'ban' => 'ban',
            'search' => 'search',
            'close' => 'close',
            'checkAll' => 'check',
            'filteredTable' => 'table',
            'link' => 'link',
            default => 'plus'
        };
    }

    public function setName(string $name) : static
    {
        $this->name = $name;

        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }
}