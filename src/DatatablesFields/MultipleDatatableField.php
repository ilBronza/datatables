<?php

namespace IlBronza\Datatables\DatatablesFields;

class MultipleDatatableField extends DatatableField
{
	public $separator = "<br />";

	public function setSeparator(string $separator)
	{
		return $this->separator = $separator;
	}

	public function getSeparator()
	{
		return $this->separator;
	}

	public function transformValue($value)
	{
		return $value->map(function ($item)
		{
			return $this->getItemValue($item);
		});
	}

	public function getCustomColumnDef()
	{
		return "
		{
			targets: [" . $this->getIndex() . "],
			render: function ( data, type, row, meta )
			{
				if(type == 'display')
				{
					let result = '';

					data.forEach(function(item)
					{
						result += " . $this->getColumnDefSingleResult() . "
					});

					return result;
				}

				return data;
			}
		}
		";
	}
}