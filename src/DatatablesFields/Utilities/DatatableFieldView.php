<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldView extends DatatableFieldFlat
{
	public string $viewName;
	public string $viewParametersGetter;
	public array $viewParameters = [];

	public function getViewName()
	{
		return $this->viewName;
	}

	public function getViewParameters()
	{
		return $this->viewParameters;
	}

	public function getViewParametersGetter() : ? string
	{
		return $this->viewParametersGetter;
	}

	public function provideViewParameters($value) : array
	{
		if(! $getter = $this->getViewParametersGetter())
			return $this->viewParameters;

		return array_merge($this->viewParameters, $value->{$getter}(
			$this
		));
	}

	public function transformValue($value)
	{
		$parameters = $this->provideViewParameters($value);

		return view(
			$this->getViewName(),
			$parameters
		)->render();
	}
}