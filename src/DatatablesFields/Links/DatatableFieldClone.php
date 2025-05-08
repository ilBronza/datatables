<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldClone extends DatatableFieldLink
{
	public $faIcon = 'clone';
	public $confirmMessage = 'datatables::messages.areYouSureToCloneThisObject';
	public ? string $translationPrefix = 'datatables::fields';

	public function transformValue($value)
	{
		if(! $value)
			return [null, null];

		if($this->textMethod)
			return [
				$value->getCloneUrl(),
				$value->{$this->textMethod}()
			];

		if(! $this->textParameter)
			return $value->getCloneUrl();

		return [
			$value->getCloneUrl(),
			$value->{$this->textParameter}
		];
	}
}
