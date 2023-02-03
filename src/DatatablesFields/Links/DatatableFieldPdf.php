<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldPdf extends DatatableFieldLink
{
	public $icon = null;
	public $faIcon = 'file-pdf';
	public $target = '_blank';

	public function transformValue($value)
	{
		try
		{
			if(! $this->textParameter)
				return $value->{$this->function}();			
		}
		catch(\Throwable $e)
		{
			return $e->getMessage();
		}

		try
		{
			return [
				$value->{$this->function}(),
				$value->{$this->textParameter}
			];			
		}
		catch(\Throwable $e)
		{
			return [
				$e->getMessage(),
				$e->getMessage()
			];
		}
	}
}
