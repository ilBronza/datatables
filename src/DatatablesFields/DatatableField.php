<?php

namespace IlBronza\Datatables\DatatablesFields;

use Auth;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsColumnDefsTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsDisplayTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsFiltersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsIdentifiersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsParametersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsPermissionsTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsSummaryTrait;

class DatatableField
{
    public $id;
    public $name;
    public $index;
    public $rowId = false;
    public $tooltip = false;
    public $summary;
    public $columnDefs = [];
    public $customColumnDefs = [];
    public $columnOptions = [];
    public $htmlClasses = [];
    public $filterType;
    public $rangeFilter;
    public $summaryValues;

    public $defaultFilterType = 'text';

    public $availableColumnOptions = ['order'];
    public $availableColumnDefs = ['width', 'orderDataType', 'visible'];

    use DatatablesFieldsPermissionsTrait;
    use DatatablesFieldsIdentifiersTrait;
    use DatatablesFieldsDisplayTrait;
    use DatatablesFieldsColumnDefsTrait;
    use DatatablesFieldsSummaryTrait;
    use DatatablesFieldsParametersTrait;
    use DatatablesFieldsFiltersTrait;

    public function __construct(string $name, array $parameters = [], int $index = null)
    {
        $this->name = $name;

        $this->id = $this->generateId();

        if(count($parameters))
            $this->setParameters($parameters);

        if($index !== null)
            $this->setIndex($index);

        // $this->setType();
        $this->setColumnDefs();
        $this->setColumnOptions();
        $this->setHtmlClasses();

        $this->summaryValues = collect();
    }

    public function getCellDataValue(string $fieldName, $element)
    {
        $properties = explode(".", $fieldName);

        do {
            $property = array_shift($properties);

            if(strpos($property, 'mySelf') === false)
                $element = $element->$property?? false;

        } while (count($properties));

        return $element;
    }

    public function __toString()
    {
        unset($this->table);

        return json_encode($this);
    }

    public function getPropertyName()
    {
        return $this->property ?? false;
    }

    public function transformValue($value)
    {
        return $value;
    }

    public function isRowId()
    {
        return $this->rowId;
    }

    // public function g3tSummaryResult()
    // {
    //     if(! $this->hasSummary())
    //         return null;

    //     return json_encode($this->summaryValues);
    // }

    static function getClassNameByType(string $fieldType)
    {
        $pieces = explode(".", $fieldType);

        $fieldType = ucfirst(array_pop($pieces));

        $folders = implode("", array_map(function($item)
            {
                return ucfirst($item) . '\\';
            }, $pieces));

        return __NAMESPACE__ . '\\' . $folders . "DatatableField" . $fieldType;
    }

    static function createByType(string $fieldName, string $fieldType, array $fieldParameters, int $index)
    {
        $className = static::getClassNameByType($fieldType);

        return new $className($fieldName, $fieldParameters, $index);
    }

    public function getRenderAsType()
    {
        if(isset($this->renderAs))
            return $this->renderAs;

        try
        {
            return $this->type;
        }
        catch(\Exception $e)
        {
            mori('render as missing ' . $this->name);
        }

    }

    // public function getCellClass()
    // {
    //     if(! ($cellClass = $this->cellClass ?? null))
    //         return false;

    //     if(isset($cellClass['view']))
    //         return view('datatables.scripts.cellClasses.' . $cellClass['view'], [
    //             'field' => $this
    //         ])->render();

    //     return false;
    // }

    // private function getRelationPivotModelName()
    // {
    //     $tableModel = class_basename($this->table->modelClass);
    //     $fieldModel = ucfirst(Str::singular($this->name));

    //     $pieces = [$tableModel, $fieldModel];
    //     sort($pieces);

    //     return lcfirst(implode("", $pieces));
    // }

    // private function getRelationModelName()
    // {
    //     $fieldModel = ucfirst(Str::singular($this->name));

    //     return lcfirst($fieldModel);
    // }

    // public function getModelSprintFShowRoute()
    // {
    //     return $this->getModelSprintFRouteByType('show');        
    // }

    // public function getRelationPivotSprintFShowRoute()
    // {
    //     return $this->getRelationPivotSprintFRouteByType('show');        
    // }

    // public function getRelationModelSprintFShowRoute()
    // {
    //     return $this->getRelationModelSprintFRouteByType('show');
    // }

    // public function getRelationModelSprintFEditRoute()
    // {
    //     return $this->getRelationModelSprintFRouteByType('edit');
    // }

    // private function getRelationModelSprintFRouteByType(string $type)
    // {
    //     return $this->getSprintFRouteByModelType(
    //         $this->getRelationModelName(),
    //         $type
    //     );
    // }

    // private function getRelationPivotSprintFRouteByType(string $type)
    // {
    //     return $this->getSprintFRouteByModelType(
    //         $this->getRelationPivotModelName(),
    //         $type
    //     );
    // }

    // private function getModelSprintFRouteByType(string $type)
    // {
    //     return $this->getSprintFRouteByModelType(
    //         $this->table->singularBaseName,
    //         $type
    //     );
    // }

    // private function getSprintFRouteByModelType(string $modelBasename, string $type)
    // {
    //     $routeBasename = Str::plural($modelBasename);

    //     return route($routeBasename . '.' . $type, [$modelBasename => '%s']);

    // }

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
    private function setColumnOption(string $columnOption)
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

    public function getColumnOptions()
    {
        return $this->columnOptions;
    }

    // public function setAbsoluteIndex(int $index)
    // {
    //     $this->absoluteIndex = $index;
    // }

    // public function getIndex()
    // {
    //     return $this->index;
    // }

    // public function isSelect()
    // {
    //     return $this->filterType == 'select';
    // }

    // public function isDate()
    // {
    //     return $this->view == 'date' || $this->renderAsView == 'date';
    // }

    // public function getColumnOptions()
    // {
    //     return $this->columnOptions;
    // }

    // 
    //TODO ERA GIA CANCELLATA PRIMA, CHE JE'?
    //
    //
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

}
