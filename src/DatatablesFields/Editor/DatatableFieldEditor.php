<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;
use Illuminate\Support\Str;

class DatatableFieldEditor extends DatatableField
{
	use IconTextContentTrait;

	public $ajax = true;
	public $spin = true;
	public $requireElement = true;
	public $requiresPlaceholderElement = true;

	//defines if vlaue is retrieved by a methd called on field element
	public $editorValueFunction = false;

    public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);

		$this->setParameterByName();
	}

	public function getFieldSpecificData() : array
	{
		if($this->editorProperty ?? false)
			return [
				'field' => $this->editorProperty
			];

		if($this->parameter ?? false)
			return [
				'field' => $this->parameter
			];

		return [];
	}

	/**
	 * if parameter to toggle is not declared in model's array, field name is taken as default
	 **/
	private function setParameterByName()
	{
		if(! isset($this->parameter))
			$this->parameter = $this->name;
	}

	public function getRouteElementClassName()
	{
		if($this->pluralModelClass ?? false)
			return $this->pluralModelClass;

		$element = $this->element ?? $this->getPlaceholderElement();

		if(method_exists($element, 'getRouteBasename'))
			return $element->getRouteBasename();

		$this->pluralModelClass = Str::plural(
			lcfirst(
				class_basename($this->element ?? $this->getPlaceholderElement())
			)
		);

		return $this->getRouteElementClassName();
	}

	public function getRouteElementParameterName($routeElementClassName)
	{
		if($placeholderElement =  $this->getPlaceholderElement())
			return Str::camel(class_basename($placeholderElement));

		return Str::singular($routeElementClassName);
	}

	public function hasSpinner()
	{
		return $this->spin;
	}

	public function setHtmlClasses(array $parameters = [])
	{
		parent::setHtmlClasses($parameters);

		if($this->hasSpinner())
			$this->addHtmlClass('spin');
	}

	public function getEditorUpdateUrl()
	{
		if(! $this->requireElement())
			return $this->element::getDatatableEditorUrl();

		$routeElementClassName = $this->getRouteElementClassName();

		$routeElementParameterName = $this->getRouteElementParameterName($routeElementClassName);

		$parameters = [
			$routeElementParameterName => config("datatables.replace_model_id_string")
		];

		try
		{
			return route($routeElementClassName . '.update', $parameters);
		}
		catch(\Exception $e)
		{
			throw new \Exception($e->getMessage() . '. ------ Also check pluralization for ' . $routeElementClassName . ' and ' . $routeElementParameterName);
		}
	}

	public function transformValue($value)
	{
		if(isset($this->solveElement))
			$value = $this->getFieldCellDataValue($this->name, $value);

		if(! $this->requireElement())
			return $value;

		$this->element = $value;

		if($this->editorValueFunction)
			return [
				$this->element->getKey(),
				$this->element->{$this->editorValueFunction}()
			];

		$propertyName = $this->editorProperty ?? $this->name;

		return [
			$this->element->getKey(),
			$value->{$propertyName}
		];
	}

	protected function substituteUrlParameter()
	{
		return "
			let url = '" . $this->getEditorUpdateUrl() . "';
			url = url.replace('" . config("datatables.replace_model_id_string") . "', item[0]);

			if(item[1] === null)
				item[1] = '';

		";
	}


	// <div class="uk-width-1-2@s input-group">
 //        <input class="uk-input" type="text" placeholder="50">
 //      <div class="input-group-append">
 //        <div class="input-group-text">
 //          @email
 //        </div>
 //      </div>
 //    </div>

	public function getSuffixString()
	{
		if(! $suffix = $this->getSuffix())
			return null;
		
		return "<div class=\'ib-suffix\'><div>" . $suffix . "</div></div>";
	}

	public function getPrefixString()
	{
		if(! $prefix = $this->getPrefix())
			return null;
		
		return "<div class=\'ib-prefix\'><div>" . $prefix . "</div></div>";
	}

    public function getEndingResultOptions() : ? string
    {
        if($this->getSuffix()||$this->getPrefix())
            return "
            if(item)
                item = '<div class=\'ib-suffix-container\'>" . $this->getPrefixString() . "' + item + '" . $this->getSuffixString() . "</div>';
        ";

        return null;
    }

    public function getCustomColumnDefSingleSearchResult()
    {
        return "
			item = item[1];
        ";
    }	

    public function getCustomColumnDefSingleSortResult()
    {
        return "
			item = item[1];
        ";
    }	
}