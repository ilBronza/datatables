<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldSee extends DatatableFieldLink
{
	public ? string $translationPrefix = 'datatables::fields';

	public function transformValue($value)
	{
		if(! $value)
			return [null, null];

		if($this->textMethod)
			return [
				$value->getShowUrl(),
				$value->{$this->textMethod}()
			];

		if(! $this->textParameter)
			return $value->getShowUrl();

		return [
			$value->getShowUrl(),
			$value->{$this->textParameter}
		];
	}
}
