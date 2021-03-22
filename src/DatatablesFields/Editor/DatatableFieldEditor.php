<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\DataAttributesTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\HtmlClassesAttributesTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;
use Illuminate\Support\Str;

class DatatableFieldEditor extends DatatableField
{
	use DataAttributesTrait;
	use HtmlClassesAttributesTrait;
	use IconTextContentTrait;

	public $ajax = true;
	public $spin = true;
	public $requireElement = true;

    public function __construct(string $name, array $parameters = [], int $index = null)
	{
		parent::__construct($name, $parameters, $index);

		$this->setParameterByName();
	}

	public function getFieldSpecificData() : array
	{
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

		$this->pluralModelClass = Str::plural(
			lcfirst(
				class_basename($this->element
				)
			)
		);

		return $this->getRouteElementClassName();
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
		$routeElementParameterName = Str::singular($routeElementClassName);

		$parameters = [
			$routeElementParameterName => config("datatables.replace_model_id_string")
		];

		return route($routeElementClassName . '.update', $parameters);
	}

	public function transformValue($value)
	{
		if(! $this->requireElement())
			return $value;

		$this->element = $value;

		return [
			$this->element->getKey(),
			$value->{$this->name}
		];
	}

	protected function substituteUrlParameter()
	{
		return "
			let url = '" . $this->getEditorUpdateUrl() . "';
			url = url.replace('" . config("datatables.replace_model_id_string") . "', item[0]);
		";
	}

    public function getCustomColumnDefSingleSearchResult()
    {
        return "
			item = item[1];
        ";
    }	
}