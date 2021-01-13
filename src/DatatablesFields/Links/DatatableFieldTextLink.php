<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldIconLink;

class DatatableFieldTextLink extends DatatableFieldIconLink
{
	public function getCustomColumnDef()
	{
		return "
		{
			targets: [" . $this->getIndex() . "],
			render: function ( data, type, row, meta )
			{
				if(type == 'display')
					return '<a href=\"' + data[0] + '\" >' + data[1] + '</a>';

				return data;
			}
		}
		";
	}
}
