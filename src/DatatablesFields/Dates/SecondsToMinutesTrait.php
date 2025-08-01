<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

trait SecondsToMinutesTrait
{
	public $showZeros = false;

	public function getCustomColumnDefSingleSearchResult()
	{
		$keyedItemString = $this->requiresKey() ? 'item = item[1]' : '';
		$showZerosString = ($this->showZeros) ? '' : '&&(item > 0)';

		return '
			' . $keyedItemString . '
		
			if((Math.floor(item))' . $showZerosString . ")
				item = Math.floor(item / 60);

			else item = ''";
	}

	public function getCustomColumnDefSingleResult()
	{
		$keyedItemString = $this->requiresKey() ? 'item = item[1]' : '';
		$showZerosString = ($this->showZeros) ? '' : '&&(item > 0)';

		return '
			' . $keyedItemString . '
			
			if((Math.floor(item))' . $showZerosString . ")
				item = Math.floor(item / 60) + '\'';

			else item = ''";
	}

}