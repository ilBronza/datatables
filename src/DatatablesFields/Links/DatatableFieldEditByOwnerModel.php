<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldEditByOwnerModel extends DatatableFieldEdit
{
	public function transformValue($value)
	{
		if(! $value)
			return null;

		if($this->textParameter)
			return [
				$value->getEditUrlByOwnerModel(),
				$value->{$this->textParameter}
			];

		if($this->staticText)
			return [
				$value->getEditUrlByOwnerModel(),
				$this->staticText
			];

		return $value->getEditUrlByOwnerModel();

	}
}
