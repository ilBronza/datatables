<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldCoefficientToPercentage extends DatatableFieldFlat
{
	public $yellowTollerance = 10;
	public $redTollerance = 30;

	public function transformValue($value)
	{
		if(! $value)
			return ;

		return round($value * 100);
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
			{
				let tolerance = Math.abs(item - 100);
				let color = '#0f0';

				if(tolerance > " . $this->yellowTollerance . ")
					color = '#ca0';

				if(tolerance > " . $this->redTollerance . ")
					color = '#f00';

				item = '<strong style=\"color: ' + color + '\">' + item + '%</strong>';
			}

			else item = '';
		";
	}
}