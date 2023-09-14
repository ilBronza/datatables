<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldMilestone extends DatatableFieldFlat
{
	public $width = '120px';
	public $backgroundColor = '#fff';
	public $milestoneColor = '#0c0';

	public function transformValue($value)
	{
		return $value;
	}

	public function getBackgroundColor()
	{
		return $this->backgroundColor;
	}

	public function getMilestoneColor()
	{
		return $this->milestoneColor;
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
				item = '<div style=\"width: 100%; height: 1em; background-color: {$this->getBackgroundColor()};\"><div style=\"width: ' + item + '%; height: 100%; background-color: {$this->getMilestoneColor()};\"></div></div>';

			else item = '';
		";		
	}
}