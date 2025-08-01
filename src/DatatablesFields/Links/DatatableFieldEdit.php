<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldEdit extends DatatableFieldLink
{
	public ? string $translationPrefix = 'datatables::fields';

	public $faIcon = 'pen-to-square';
	public $method = 'getEditUrl';

	public function getEditMethodName()
	{
		return $this->method;
	}

	public function transformValue($value)
	{
		if(! $value)
			return null;

		if($this->textParameter)
			return [
				$value->{$this->getEditMethodName()}(),
				$value->{$this->textParameter}
			];

		if($this->staticText)
			return [
				$value->{$this->getEditMethodName()}(),
				$this->staticText
			];

		return $value->{$this->getEditMethodName()}();

	}
}
