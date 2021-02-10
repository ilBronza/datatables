<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldBoolean extends DatatableField
{
	public function transformValue($value)
	{
		if($value == null)
			return ;

		return !! $value;
	}

	public function getCustomColumnDef()
	{
		$fieldIndex = $this->getIndex();

		return "
		{
			targets: [{$fieldIndex}],
			render: function ( data, type, row, meta )
			{
				if(type == 'display')
				{
					if(data === true)
						return '<strong class=\"uk-text-danger\">" . __('fields.booleanTrue') . "</strong>';

					if(data === false)
						return '" . __('fields.booleanFalse') . "';

					return '" . __('fields.booleanNull') . "';
				}

			return data;

			console.log('admamama');
			}
		}";
	}
}