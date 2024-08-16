<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldMilestone extends DatatableFieldFlat
{
	public $width = '120px';
	public $milestoneColor = '#0c0';

    public function transformValue($value)
    {
    	if(is_numeric($value))
    	{
    		if($value > 100)
    			$value = 100;

    		return round(100 - $value, 2);
    	}

    	return null;
    }

	public function getMilestoneColor()
	{
		return $this->milestoneColor;
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if((item)||(item === 0))
				item = '<div class=\"ibmilestone\"><span>' + (100 - item) + '%</span><div style=\"width: ' + item + '%;\"></div></div>';

			else item = '';
		";		
	}
}