<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Datatables\Datatables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait DatatablesFieldsRelationsTrait
{
    public function getRelationName() : string
    {
        if($this->relation ?? null)
            return $this->relation;

        return $this->name;
    }

    public function getRelatedModelPlaceholder() : ? Model
    {
        $relationName = $this->getRelationName();

        if(! $placeholderElement = $this->getPlaceholderElement())
            return null;

        return $placeholderElement->$relationName()->make();
    }

    public function isDependentRelation()
    {
        return $this->isDependentRelation ?? false;
    }

    public function getRelationModelNameByFieldName()
    {
        $parts = explode(".", $this->name);

        return Str::singular(
            array_pop($parts)
        );
    }	

    /**
     * get pivot model name to create route name eg. manufacturerPaper
     *
     * @return string
     **/
    private function getRelationModelName()
    {
        if(isset($this->relation))
            return class_basename($this->relation);

        return $this->getRelationModelNameByFieldName();
    }

    private function getRelationPivotModelName()
    {
        if(empty($this->pivot))
            throw new \Exception('Manca dichiarazione pivot per il campo ' . $this->name . " -> " . json_encode($this));

        return class_basename($this->pivot);
    }

    public function getRelationPivotSprintFShowRoute()
    {
        return $this->getRelationPivotSprintFRouteByType('edit');
    }

    private function getRelationPivotSprintFRouteByType(string $type)
    {
        return $this->getSprintFRouteByModelType(
            $this->getRelationPivotModelName(),
            $type
        );
    }

    private function getSprintFRouteByModelType(string $modelBasename, string $type)
    {
        $modelBasename = Str::singular(lcfirst($modelBasename));
        $routeBasename = Str::plural($modelBasename);

        return route($routeBasename . '.' . $type, [$modelBasename => '%s']);
    }

    private function getSprintFRouteByModel(Model $model, string $type)
    {
        $routeBasename = $model->getRouteBasename();
        $modelBasename = Str::singular(lcfirst(class_basename($model)));

        return route($routeBasename . '.' . $type, [$modelBasename => '%s']);
    }

    private function getRelationshipSprintFRouteByModelType(string $modelBasename, string $type)
    {
        $parentModelBasename = Str::camel(class_basename($this->table->modelClass));
        $parentRouteBasename = Str::plural($parentModelBasename);

        $relatedRouteBasername = Str::plural($modelBasename);

        if(Route::has($route = implode(".", [$parentRouteBasename, $relatedRouteBasername, $type]), [
            $parentModelBasename => '%f',
            $modelBasename => '%s'
        ]))
            return route($route);

        return false;
    }

    private function getRelationModelSprintFRouteByType(string $type)
    {
        if(isset($this->routeBasename))
            return $this->getSprintFRouteByModelType(
                $this->routeBasename,
                $type
            );

        if(isset($this->table->modelClass)&&($this->isDependentRelation()))
            if($route = $this->getRelationshipSprintFRouteByModelType(
                $this->getRelationModelName(),
                $type
            ))
            return $route;

        if($relatedModel = $this->getRelatedModelPlaceholder())
            return $this->getSprintFRouteByModel(
                $relatedModel,
                $type
            );

        try
        {            
            return $this->getSprintFRouteByModelType(
                $this->getRelationModelName(),
                $type
            );
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function getRelationModelSprintFShowRoute()
    {
        return $this->getRelationModelSprintFRouteByType('show');
    }
}
