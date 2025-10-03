<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldMerge extends DatatableFieldLink
{
	public $faIcon = 'code-merge';
	public ? string $translationPrefix = 'datatables::fields';

	public function transformValue($value)
	{
		if(! $value)
			return [null, null];

		if($this->textMethod)
			return [
				$value->getMergeUrl(),
				$value->{$this->textMethod}()
			];

		if(! $this->textParameter)
			return $value->getMergeUrl();

		return [
			$value->getMergeUrl(),
			$value->{$this->textParameter}
		];
	}
}
