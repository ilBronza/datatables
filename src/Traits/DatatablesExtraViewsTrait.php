<?php

namespace IlBronza\Datatables\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait DatatablesExtraViewsTrait
{
    public $extraViews = [
        'top' => [],
        'bottom' => [],
        'left' => [],
        'right' => []
    ];

    public function getParentModelView(Model $model)
    {
        if(isset($model->topExtraView))
            return $model->topExtraView;

        $pluralModelName = Str::camel(Str::plural(class_basename($model)));

        $viewName = "{$pluralModelName}.topExtraView";

        if (view()->exists($viewName))
            return $viewName;

        return false;
    }

    public function addParentModel(Model $parentModel)
    {
        if(! $parentModel)
            return ;

        if(! $view = $this->getParentModelView($parentModel))
            $view = 'crud::models._parentModelExtraView';

        $this->addTopView($view, compact('parentModel'));
    }

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

        $parameters['tableId'] = $this->getId();

        $this->extraViews[$position][] = view($viewName, $parameters);
    }

    public function getExtraViews(string $position)
    {
        return $this->extraViews[$position];
    }
}