<?php

namespace IlBronza\Datatables;

use Illuminate\Support\Str;

class DatatableField
{
    public $name;
    public $index;
    public $columnDefs = [];
    public $customColumnDefs = [];
    public $columnOptions = [];
    public $filterType = 'text';

    public $availableColumnOptions = ['order'];
    public $availableColumnDefs = ['width', 'orderDataType', 'visible'];

    public function __construct(string $name, array $parameters = [], int $index = null)
    {
        $this->name = $name;

        if(count($parameters))
            $this->setParameters($parameters);

        if($index !== null)
            $this->setIndex($index);

        $this->setColumnDefs();
        $this->setColumnOptions();
    }

    public function getCellClass()
    {
        if(! ($cellClass = $this->cellClass ?? null))
            return false;

        if(isset($cellClass['view']))
            return view('datatables::datatables.scripts.cellClasses.' . $cellClass['view'], [
                'field' => $this
            ])->render();

        return false;
    }

    private function getRelationPivotModelName()
    {
        $tableModel = class_basename($this->table->modelClass);
        $fieldModel = ucfirst(Str::singular($this->name));

        $pieces = [$tableModel, $fieldModel];
        sort($pieces);

        return lcfirst(implode("", $pieces));
    }

    private function getRelationModelName()
    {
        $fieldModel = ucfirst(Str::singular($this->name));

        return lcfirst($fieldModel);
    }

    public function getModelSprintFShowRoute()
    {
        return $this->getModelSprintFRouteByType('show');        
    }

    public function getRelationPivotSprintFShowRoute()
    {
        return $this->getRelationPivotSprintFRouteByType('show');        
    }

    public function getRelationModelSprintFShowRoute()
    {
        return $this->getRelationModelSprintFRouteByType('show');
    }

    public function getRelationModelSprintFEditRoute()
    {
        return $this->getRelationModelSprintFRouteByType('edit');
    }

    private function getRelationModelSprintFRouteByType(string $type)
    {
        return $this->getSprintFRouteByModelType(
            $this->getRelationModelName(),
            $type
        );
    }

    private function getRelationPivotSprintFRouteByType(string $type)
    {
        return $this->getSprintFRouteByModelType(
            $this->getRelationPivotModelName(),
            $type
        );
    }

    private function getModelSprintFRouteByType(string $type)
    {
        return $this->getSprintFRouteByModelType(
            $this->table->singularBaseName,
            $type
        );
    }

    private function getSprintFRouteByModelType(string $modelBasename, string $type)
    {
        $routeBasename = Str::plural($modelBasename);

        return route($routeBasename . '.' . $type, [$modelBasename => '%s']);

    }

    /**
     * set field own options
     */
    private function setColumnOptions()
    {
        //parse through available columnDefs parameters and id set, store it
        foreach($this->availableColumnOptions as $availableColumnOption)
            $this->setColumnOption($availableColumnOption);
    }

    /**
     * if exists in object, add columnOption to field
     */
    public function setColumnOption(string $columnOption)
    {
        if(($value = $this->$columnOption?? null) !== null)
            $this->addColumnOption($columnOption, $value);
    }

    /**
     * add columnDef to field
     *
     * @param string $name
     * @param mixed $value
     */
    public function addColumnOption(string $name, $value)
    {
        $this->columnOptions[$name] = $value;       
    }

    /**
     * set field own columnDefs
     */
    private function setColumnDefs()
    {
        $this->columnDefs = [];

        //parse through available columnDefs parameters and id set, store it
        foreach($this->availableColumnDefs as $availableColumnDef)
            $this->setColumnDef($availableColumnDef);
    }

    /**
     * if exists in object, add columnDef to field
     */
    public function setColumnDef(string $columnDef)
    {
        if(($value = $this->$columnDef?? null) !== null)
            $this->addColumnDef($columnDef, $value);
    }

    /**
     * add columnDef to field
     *
     * @param string $name
     * @param mixed $value
     */
    public function addColumnDef(string $name, $value)
    {
        $this->columnDefs[$name] = $value;
    }


    public function setAbsoluteIndex(int $index)
    {
        $this->absoluteIndex = $index;
    }

    public function setIndex(int $index)
    {
        $this->index = $index;
    }

    public function getIndex()
    {
        return $this->index;
    }

    private function setParameter($name, $parameter)
    {
        if(! is_int($name))
            $this->$name = $parameter;

        else
            $this->$parameter = [];
    }

    public function isRange()
    {
        return $this->filterType == 'range';
    }

    public function isSelect()
    {
        return $this->filterType == 'select';
    }

    public function isDate()
    {
        return $this->view == 'date' || $this->renderAsView == 'date';
    }

    public function getColumnDefs()
    {
        return $this->columnDefs;
    }

    public function getColumnOptions()
    {
        return $this->columnOptions;
    }

    // private function setParameterByName(string $name, array &$parameters, bool $compulsory = false)
    // {
    //  if(! isset($parameters[$name]))
    //  {
    //      if($compulsory)
    //          throw new \Exception("missing {$name} for {$this->name}");

    //      return false;
    //  }

    //  $this->setParameter($name, $parameters[$name]);

    //  unset($parameters[$name]);
    // }

    public function setParameters(array $parameters)
    {
        // $this->setParameterByName('view', $parameters, true);

        foreach($parameters as $name => $parameter)
            $this->setParameter($name, $parameter);
    }
}
