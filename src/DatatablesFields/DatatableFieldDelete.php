<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldDelete extends DatatableField
{
	public function transformValue($value)
	{
		if($value->trashed())
			return [1 => $value->getDestroyUrl()];

		return [0 => $value->getDeleteUrl()];
	}

    public function getCustomColumnDef()
    {
		return "
		{
			targets: [" . $this->getIndex() . "],
			render: function ( data, type, row, meta )
			{
				if(typeof data[0] !== \"undefined\")
					return '<button data-url=\"' + data[0] + '\" class=\"delete-button button-delete\" type=\"button\" uk-icon=\"icon: trash\"></button>';

				if(typeof data[1] !== \"undefined\")
					return '<button data-destroy=\"1\" data-url=\"' + data[1] + '\" class=\"delete-button button-delete uk-text-danger\" type=\"button\" uk-icon=\"icon: trash\"></button>';
			}
		}
		";
	}
}
