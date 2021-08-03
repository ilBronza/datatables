<?php

namespace IlBronza\Datatables\DatatablesFields\Texts;

use IlBronza\Datatables\DatatablesFields\DatatableFieldFlat;

class DatatableFieldEllipsis extends DatatableFieldFlat
{
	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
				item = '<span class=\"uk-text-truncate\">' + item + '</span>';

			else item = ''
		";
	}
}