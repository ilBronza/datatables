<?php

namespace IlBronza\Datatables\DatatablesFields\Utilities;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldMilestone extends DatatableFieldFlat
{
	public ? string $translationPrefix = 'datatables::fields';
	public ? string $forcedStandardName = 'milestone';

	public $width = '120px';
	public $milestoneColor = '#0c0';

    public function transformValue($value)
    {
    	if(! is_numeric($value))
			return null;

		if($value > 100)
			$value = 100;

		return round(100 - $value, 2);
    }

	public function getMilestoneColor()
	{
		return $this->milestoneColor;
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if((item)||(item === 0))
				item = '<div class=\"ibmilestone\"><span>' + (Math.round((100 - item) * 100)/100) + '%</span><div style=\"width: ' + item + '%;\"></div></div>';

			else item = '';
		";		
	}
}