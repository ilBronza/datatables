<?php

namespace IlBronza\Datatables\DatatablesFields;

use Auth;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldEditor;
use IlBronza\Datatables\DatatableFieldsGroup;
use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DataAttributesTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\HtmlClassesAttributesTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsColumnDefsTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsDisplayTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsElementTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsFetcherTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsFiltersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsHtmlClassesTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsIdentifiersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsOperationsTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsParametersTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsParentingTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsPermissionsTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsSortingTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsSummaryTrait;
use IlBronza\Datatables\Traits\DatatablesFields\DatatablesFieldsUserDataTrait;

class DatatableField
{
    use DatatablesFieldsPermissionsTrait;
    use DatatablesFieldsIdentifiersTrait;
    use DatatablesFieldsDisplayTrait;
    use DatatablesFieldsColumnDefsTrait;
    use DatatablesFieldsSummaryTrait;
    use DatatablesFieldsParametersTrait;
    use DatatablesFieldsFiltersTrait;
    use DatatablesFieldsSortingTrait;
    use DatatablesFieldsHtmlClassesTrait;
    use DatatablesFieldsOperationsTrait;
    use DatatablesFieldsFetcherTrait;
    use DatatablesFieldsElementTrait;
    use DatatablesFieldsParentingTrait;
    use DatatablesFieldsUserDataTrait;

    use HtmlClassesAttributesTrait;
    use DataAttributesTrait;

    public $id;
    public $name;
    public $index;
    public $avoidIcon;
    public $rowId = false;
    public $tooltip = false;
    public $summary;
    public $data = [];
    public $headerData = [];
    public $columnDefs = [];
    public $customColumnDefs = [];
    public $columnOptions = [];
    public $icon;
	public $htmlClasses = [];
	public $tDHtmlClasses = [];

    public $headerHtmlClasses = [];
    public $fieldOperations = [];
    public $fieldExtraData = [];
    public $filteredTable;
    public $parameter;

	public bool $truncateText = false;
    public $filterType;
    public $type;
    public $table;
    public $userData;
    public $rangeFilter;
    public $summaryValues;
    public $elementValues;
    public $function;
    public $sortable = true;
    public $requireElement = false;
    public $requiresPlaceholderElement = false;
    public $placeholderElement;
    public $element;
    public $valueAsRowClass = false;
    public $valueAsRowClassPrefix = false;
    public $compiledAsRowClass;
    public $compiledAsRowClassPrefix;
    public $extraModelClassname;
    public $htmlTag;
    public $width;
    public $target;
    public $doubler = false;
    public $jqueryFilterEvents = ['change', 'keyup'];

    public $strLimit = 0;

    public ? string $translationPrefix = null;

    public DatatableFieldsGroup $fieldsGroup;

    public $defaultWidth = '120px';

    public $canDrawTable = true;

    //nuovo campo per capire se è abilitato il filtro o meno
    public $filterable = true;

	public function isEditor() : bool
	{
		return $this instanceof DatatableFieldEditor;
	}

	public function addExtradata(string $key, mixed $value)
	{
		$this->fieldExtraData[$key] = $value;
	}
    public function hasDoubler()
    {
        return $this->doubler;
    }

    public $defaultFilterType = 'text';

    //this is used to tell datatables how to render sorting value
    // public $datatableType = 'string';

    public $availableColumnOptions = ['order'];
    public $availableColumnDefs = [
        'width' => 'width',
        'orderDataType' => 'orderDataType',
        'visible' => 'visible',
        'datatableType' => 'type'
    ];

    public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
    {
        $this->name = $name;

        $this->id = $this->generateId();

        $this->table = $table;

        $this->setParentField($parent);

        $this->setParameters($parameters);

        $this->setPlaceholderElement();

        $this->manageWidth($parameters);


        $this->setIndex($index);

        // $this->setType();
        $this->setColumnDefs();
        $this->setColumnOptions();

        $this->setHtmlClasses($parameters);
        $this->setDataAttributes($parameters);

        $this->manageFieldOperations($parameters);

        $this->setFetcherParameters($parameters);

        $this->summaryValues = collect();

        $this->checkConfirmMessage($parameters);
    }

    public function getFieldCellDataValue(string $fieldName, $element)
    {
        $properties = explode(".", $fieldName);

        $this->elementValues = [];

        $i = 0;

        $this->elementValues[$i] = $element;

        do {
            $property = array_shift($properties);
                
            if(strpos($property, 'mySelf') === false)
                $element = $element->$property?? false;

            $this->elementValues[++$i] = $element;

        } while (count($properties));

        return $element;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isFlatType()
    {
        return $this->getType() == 'flat';
    }

    public function requiresKey()
    {
        if(isset($this->fetcher))
        {
            if($this->isFlatType())
                return false;

            return true;
        }

        return false;
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

    public function requireElement()
    {
        return $this->requireElement;
    }

    public function isRowId()
    {
        return $this->rowId;
    }

    static function getPackageClassNameByType(string $fieldType) : string
    {
        $pieces = explode("::", $fieldType);

        $package = array_shift($pieces);

        $fieldType = static::getClassNameByType(implode(".", $pieces), false);

        $fullnamespace = get_class(app($package));

        $namespacePieces = explode("\\", $fullnamespace);
        array_pop($namespacePieces);

        return implode("\\", $namespacePieces) . "\Providers\DatatablesFields\\" . $fieldType;
    }

    static function getClassNameByType(string $fieldType, bool $fullQualifiedNamespace = true) : string
    {
        //if contains "::" in string, it's a custom field
        if(strpos($fieldType, "::") !== false)
            return static::getPackageClassNameByType($fieldType);

        $pieces = explode(".", $fieldType);

        $fieldType = ucfirst(array_pop($pieces));

        $folders = implode("", array_map(function($item)
            {
                return ucfirst($item) . '\\';
            }, $pieces));

        if(! $fullQualifiedNamespace)
            return $folders . "DatatableField" . $fieldType;

        return __NAMESPACE__ . '\\' . $folders . "DatatableField" . $fieldType;
    }

    static function createByType(string $fieldName, string $fieldType, array $fieldParameters, int $index = 0, DatatableField $parent = null, Datatables $table = null)
    {
        $className = static::getClassNameByType($fieldType);

        return new $className($fieldName, $fieldParameters, $index, $parent, $table);
    }

    public function getRenderAsType()
    {
        if(isset($this->renderAs))
            return $this->renderAs;

        return ;

        try
        {
            return $this->type;
        }
        catch(\Exception $e)
        {
            dd('render as missing ' . $this->name);
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

    public function setFieldsGroup(DatatableFieldsGroup $fieldsGroup)
    {
        $this->fieldsGroup = $fieldsGroup;
    }

    public function getFieldsGroup() : DatatableFieldsGroup
    {
        return $this->fieldsGroup;
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

    public function handleError($e)
    {
        return $e->getMessage();
    }

    public function getHtmlTagString()
    {
        return $this->getHtmlTag() . ' ';
    }

    public function getHtmlTag()
    {
        return $this->htmlTag;
    }

    public function getTable() : Datatables
    {
        return $this->table;
    }

    public function debug() : bool
    {
        return $this->getTable()->debug();
    }
}
