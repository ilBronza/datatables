<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldBoolean extends DatatableField
{
	public function transformValue($value)
	{
		if(! $value)
			return ;

		return $value;
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
						return '" . _('fields.booleanTrue') . "';

					if(data === false)
						return '" . _('fields.booleanFalse') . "';

					return '" . _('fields.booleanNull') . "';
				}

			return data;
			}
		}";
	}
}