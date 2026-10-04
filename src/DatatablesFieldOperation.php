<?php

namespace IlBronza\Datatables;

class DatatablesFieldOperation
{
    public $name;
    public string $type;
    public ? string $icon = null;
    public ? string $faIcon = null;
    public ? string $link = null;
    public ? string $target = null;

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

        if(isset($parameters['faIcon']))
            $fieldOperation->setFaIcon($parameters['faIcon']);

        if(isset($parameters['link']))
            $fieldOperation->setLink($parameters['link']);

        if(isset($parameters['target']))
            $fieldOperation->setTarget($parameters['target']);

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

    public function getRenderedLink() : ? string
    {
        if($this->target !== 'iframe' || ! $this->link)
            return $this->link;

        [$url, $fragment] = array_pad(explode('#', $this->link, 2), 2, null);

        $url = preg_replace('/([?&])iframed(?:=[^&]*)?(?=&|$)/', '$1iframed=true', $url, 1, $replaced);

        if(! $replaced)
        {
            $separator = str_contains($url, '?')
                ? (str_ends_with($url, '?') || str_ends_with($url, '&') ? '' : '&')
                : '?';

            $url .= $separator . 'iframed=true';
        }

        return $url . ($fragment === null ? '' : '#' . $fragment);
    }

    public function setTarget(string $target) : static
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget() : ? string
    {
        return $this->target;
    }

    public function setIcon(string $icon) : static
    {
        $this->icon = $icon;

        return $this;
    }

    public function setFaIcon(string $faIcon) : static
    {
        $this->faIcon = $faIcon;

        return $this;
    }

    public function getFaIcon() : ? string
    {
        return $this->faIcon;
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
            'delete' => 'trash',
            default => 'plus'
        };
    }

    public function setName(string $name) : static
    {
        $this->name = $name;

        return $this;
    }

    public function getName() : ? string
    {
        return $this->name;
    }
}
