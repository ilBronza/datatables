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
			return $this->handleError($e);
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
			if($this->debug())
				ddd($e->getMessage());

			return [
				$e->getMessage(),
				$e->getMessage()
			];
		}
	}
}
