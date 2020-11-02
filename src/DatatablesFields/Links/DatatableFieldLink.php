<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldLink extends DatatableField
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
					return " . $this->getLinkColumnDefResult() . ";

				return data;
			}
		}
		";
	}

	public function getIconString()
	{
		return "uk-icon=\"" . $this->icon . "\" ";
	}

	public function getLinkColumnDefResult()
	{
		return "'<a " . $this->getIconString() . " href=\"' + data[0] + '\" >' + data[1] + '</a>'";
	}
}
