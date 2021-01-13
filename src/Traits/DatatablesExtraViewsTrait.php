<?php

namespace IlBronza\Datatables\Traits;

trait DatatablesExtraViewsTrait
{
    public $extraViews = [
        'top' => [],
        'bottom' => [],
        'left' => [],
        'right' => []
    ];

    private function getAvailableExtraViewsPositions()
    {
        return array_keys($this->extraViews);
    }

    public function addTopView(string $viewName, array $parameters = [])
    {
        return $this->addView('top', $viewName, $parameters);
    }

    public function addBottomView(string $viewName, array $parameters = [])
    {
        return $this->addView('bottom', $viewName, $parameters);
    }

    public function addLeftView(string $viewName, array $parameters = [])
    {
        return $this->addView('left', $viewName, $parameters);
    }

    public function addRightView(string $viewName, array $parameters = [])
    {
        return $this->addView('right', $viewName, $parameters);
    }

    public function addView(string $position, string $viewName, array $parameters = [])
    {
        if(! in_array($position, $this->getAvailableExtraViewsPositions()))
            throw \Exception("posizione {$position} non esistente");

        $this->extraViews[$position][] = view($viewName, $parameters);
    }

    public function getExtraViews(string $position)
    {
        return $this->extraViews[$position];
    }
}