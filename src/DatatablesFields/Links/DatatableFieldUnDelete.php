<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldUnDelete extends DatatableFieldDelete
{
    public $confirmMessage = 'datatables::messages.areYouSureToRestoreThisObject';
    public $icon = null;
    public $faIcon = 'recycle';
    
	public function transformValue($value)
	{
		if(! $this->textParameter)
			return $value->getRestoreUrl();

		return [
			$value->getRestoreUrl(),
			$value->{$this->textParameter}
		];
	}
}
