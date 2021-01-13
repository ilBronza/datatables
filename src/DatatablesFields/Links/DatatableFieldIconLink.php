<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldIconLink extends DatatableFieldLink
{
	public $icon = 'link';
	public $textParameter = false;

	public function getIconHtml()
	{
		if(! $this->icon)
			return ;

		return "<span uk-icon=\"" . $this->icon . "\"></span>";
	}

	public function _getCustomColumnDef()
	{
		return "
		{
			targets: [" . $this->getIndex() . "],
			render: function ( data, type, row, meta )
			{
				if(type == 'display')
					return '<a href=\"' + data + '\" >" . $this->getIconHtml() . "</a>';

				return data;
			}
		}
		";
	}

    public function getCustomColumnDef()
    {
    	return $this->_getCustomColumnDef();
	}
}
