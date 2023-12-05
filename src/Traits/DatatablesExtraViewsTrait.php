<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\UikitTemplate\Fetcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait DatatablesExtraViewsTrait
{
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
            $view = 'uikittemplate::models._parentModel';

        $this->addTopView($view, compact('parentModel'));
    }

    // private function getAvailableExtraViewsPositions()
    // {
    //     return array_keys($this->extraViews);
    // }

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
        return $this->addExtraView($position, $viewName, $parameters);
        // if(! in_array($position, $this->getValidExtraViewsPositions()))
        //     throw \Exception("posizione {$position} non esistente");

        // $parameters['tableId'] = $this->getId();

        // $this->extraViews[$position][] = view($viewName, $parameters);
    }

    public function getExtraViews(string $position)
    {
        return $this->extraViews[$position];
    }

































    // private function getExtraViewsCollection()
    // {
    //     return $this->extraViews ?? null;
    // }

    // public function getFetchers() : Collection
    // {
    //     return $this->fetchers;
    // }

    // private function createExtraViewsCollection()
    // {
    //     $result = [];

    //     foreach($this->getValidExtraViewsPositions() as $position)
    //         $result[$position] = collect();

    //     $this->extraViews = collect($result);
    // }

    // private function checkForExtraViewsCollection()
    // {
    //     if(! $this->getExtraViewsCollection())
    //         $this->createExtraViewsCollection();
    // }

    // private function checkValidPosition(string $position)
    // {
    //     if(! in_array($position, static::getValidExtraViewsPositions()))
    //         throw new \Exception($position . ' is not a valid position for this ' . class_basename($this));
    // }

    // public function addFetcher(string $position, Fetcher $fetcher)
    // {
    //     $this->checkValidPosition($position);

    //     $this->getFetchers()->push([
    //         'position' => $position,
    //         'fetcher' => $fetcher
    //     ]);
    // }

    // public function addExtraView(string $position, string $viewName, array $parameters)
    // {
    //     $this->checkValidPosition($position);
    //     $this->checkForExtraViewsCollection();

    //     $this->extraViews->get($position)[$viewName] = $parameters;
    // }

    // public function hasFetchersPositions($positions) : bool
    // {
    //     foreach($positions as $position)
    //         if($this->getFetchers()->firstWhere('position', $position))
    //             return true;

    //     return false;
    // }

    // public function getFetchersPosition(string $position) : Collection
    // {
    //     return $this->getFetchers()->where('position', $position)->pluck('fetcher');
    // }

    // public function getExtraViewsPosition(string $position) : Collection
    // {
    //     if(! $this->getExtraViewsCollection())
    //         return collect();

    //     return $this->extraViews->get($position) ?? collect();
    // }

    // public function hasExtraViewsPosition(string $position) : bool
    // {
    //     if(! $position = $this->getExtraViewsPosition($position))
    //         return false;

    //     return count($position) > 0;
    // }

    // public function hasExtraViewsPositions($positions = null) : bool
    // {
    //     $positions = is_array($positions) ? $positions : func_get_args();

    //     if($this->hasFetchersPositions($positions))
    //         return true;

    //     // if((! $this->getExtraViewsCollection()))
    //     //     return false;

    //     // foreach($positions as $position)
    //     //     if($this->hasExtraViewsPosition($position))
    //     //         return true;

    //     return false;
    // }

    // public function renderExtraViews(string $position) : string
    // {
    //     $result = [];

    //     // foreach($this->getExtraViewsPosition($position) as $name => $parameters)
    //     //     $result[] = view($name, $parameters)->render();

    //     foreach($this->getFetchersPosition($position) as $fetcher)
    //         $result[] = $fetcher->render();

    //     return implode(" ", $result);
    // }



}