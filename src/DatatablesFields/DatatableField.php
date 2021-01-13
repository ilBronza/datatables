<?php

namespace IlBronza\Datatables\DatatablesFields;

use Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatatableField
{
    public $id;
    public $name;
    public $index;
    public $rowId = false;
    public $summary;
    public $columnDefs = [];
    public $customColumnDefs = [];
    public $columnOptions = [];
    public $filterType;
    public $rangeFilter;
    public $summaryValues;

    public $defaultFilterType = 'text';

    public $availableColumnOptions = ['order'];
    public $availableColumnDefs = ['width', 'orderDataType', 'visible'];

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

        $this->summaryValues = collect();
    }

    public function generateId()
    {
        return Str::slug($this->name . rand(0, 999999), '');
    }

    public function getId()
    {
        return $this->id;
    }

    public function transformValue($value)
    {
        return $value;
    }

    public function getSummaryType()
    {
        return $this->summary;
    }

    public function getSummaryResult()
    {
        if(! $summaryType = $this->getSummaryType())
            return null;

        if($summaryType == 'average')
            return $this->summaryValues->avg(function ($value)
            {
                return (float) $value;
            });

        if($summaryType == 'distinct')
        {
            return $this->summaryValues->unique(function ($value)
            {
                return strip_tags($value);
            })->implode('-');
        }

        if($summaryType == 'sum')
            return $this->summaryValues->sum(function ($value)
            {
                return (float) $value;
            });

        if($summaryType == 'sumMinutes')
        {
            $totalMinutes = $this->summaryValues->sum(function ($value)
            {
                return (float) $value;
            });

            $pieces = [];

            if($hours = floor($totalMinutes / 60))
                $pieces[] = $hours . " h";

            if($minutes = $totalMinutes % 60)
                $pieces[] = $minutes . " \'";

            return  implode(" ", $pieces);      

        }


        mori('manca summaryType ' . $summaryType);
    }

    public function transformValueWithSummary($value)
    {
        $value = $this->transformValue($value);

        $this->summaryValues->push($value);

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

        // if(! class_exists($className))
        //     $className = static::class;

        return new $className($fieldName, $fieldParameters, $index);
    }

    public function getCustomColumnDef()
    {
        
    }

    public function getTranslatedName()
    {
        return __('fields.' . $this->name);
    }

    public function getFieldName()
    {
        return $this->name;
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
            mori($this);
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
    public function addColumnDef(string $columnDef, $value)
    {
        $this->columnDefs[$columnDef] = $value;
    }

    // public function setAbsoluteIndex(int $index)
    // {
    //     $this->absoluteIndex = $index;
    // }

    public function setIndex(int $index)
    {
        $this->index = $index;
    }

    public function incrementIndex()
    {
        $this->index ++;
    }

    public function getIndex()
    {
        return $this->index;
    }

    // public function getIndex()
    // {
    //     return $this->index;
    // }

    private function setParameter($name, $parameter)
    {
        if(! is_int($name))
            $this->$name = $parameter;

        else
            $this->$parameter = [];
    }

    public function hasRangeFilter()
    {
        return !! $this->rangeFilter;
    }

    // public function isSelect()
    // {
    //     return $this->filterType == 'select';
    // }

    // public function isDate()
    // {
    //     return $this->view == 'date' || $this->renderAsView == 'date';
    // }

    public function getHtmlClass()
    {
        return Str::camel(str_replace(".", " ", $this->name));
    }

    private function setClassnameColumnDef()
    {
        //add className to field columnDefs
        if(! isset($this->columnDefs['className']))
            $this->columnDefs['className'] = $this->getHtmlClass();        
    }

    public function getColumnDefs()
    {
        $this->setClassnameColumnDef();

        return $this->columnDefs;
    }

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

    public function setParameters(array $parameters)
    {
        // $this->setParameterByName('view', $parameters, true);

        foreach($parameters as $name => $parameter)
            $this->setParameter($name, $parameter);
    }

    public function renderHeader()
    {
        return view('datatables::datatablesFields._header', ['field' => $this]);
    }

    public function getFilterType()
    {
        return $this->filterType ?? $this->defaultFilterType;
    }

    public function getrangeFilterType()
    {
        if($this->rangeFilter === true)
            return 'normal';

        return $this->rangeFilter;
    }

    public function assignSummary(string $summary)
    {
        $this->summary = $summary;
    }

    public function assignRoles(array $roles)
    {
        $this->allowedForRoles = array_merge(
            $this->allowedForRoles ?? [],
            $roles
        );
    }

    public function assignForbiddenRoles(array $roles)
    {
        $this->forbiddenForRoles = array_merge(
            $this->forbiddenForRoles ?? [],
            $roles
        );
    }

    public function isAllowedForGuest()
    {
        if(isset($this->allowedForRoles))
            return false;

        if(isset($this->forbiddenForRoles))
            return false;

        return true;
    }

    public function isForbiddenForRole(Role $role)
    {
        if(! isset($this->forbiddenForRoles))
            return false;

        return in_array($role->name, $this->forbiddenForRoles);
    }

    public function isAllowedForRole(Role $role)
    {
        if(! isset($this->allowedForRoles))
            return true;

        return in_array($role->name, $this->allowedForRoles);        
    }

    public function isAllowed()
    {
        if($this->isAllowedForGuest())
            return true;

        if(! $user = Auth::user())
            return false;

        foreach($user->roles as $role)
            if($this->isForbiddenForRole($role))
                return false;

        foreach($user->roles as $role)
            if($this->isAllowedForRole($role))
                return true;

        return false;
    }

    public function getRangeFilterJavascriptPlugin(string $tableId = null)
    {
        $view = 'datatables::datatablesFields.filters.scripts._range' . ucfirst($this->getrangeFilterType());

        return view($view, [
            'tableId' => $tableId,
            'field' => $this
        ])->render();
    }
}
