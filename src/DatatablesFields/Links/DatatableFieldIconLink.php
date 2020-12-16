<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldIconLink extends DatatableFieldLink
{
	public $icon = 'link';

    public function getCustomColumnDef()
    {
		return "
		{
			targets: [" . $this->getIndex() . "],
			render: function ( data, type, row, meta )
			{
				if(type == 'display')
					return '<a uk-icon=\"" . $this->icon . "\" href=\"' + data + '\" ></a>';

				return data;
			}
		}
		";
	}
}
