<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Support\Str;

class DatatableFieldEditor extends DatatableField
{
	public $spin = true;
	public $requireElement = true;

    public function __construct(string $name, array $parameters = [], int $index = null)
	{
		parent::__construct($name, $parameters, $index);

		$this->setParameter();
	}

	/**
	 * if parameter to toggle is not declared in model's array, field name is taken as default
	 **/
	private function setParameter()
	{
		if(empty($this->parameter))
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

    public function setHtmlClasses()
    {
    	parent::setHtmlClasses();

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

		return route($routeElementClassName . '.updateEditor', $parameters);
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
			return item[1];
        ";
    }	
}