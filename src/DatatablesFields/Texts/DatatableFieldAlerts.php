<?php

namespace IlBronza\Datatables\DatatablesFields\Texts;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldAlerts extends DatatableFieldFlat
{
	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
				item = '<span class=\"ib-dt-alert-cell\" title=\"' + item + '\" uk-icon=\"warning\"></span>';

			else item = ''
		";
	}
}