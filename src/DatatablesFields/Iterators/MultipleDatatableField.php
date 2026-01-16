<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class MultipleDatatableField extends DatatableField
{
	public $requiresPlaceholderElement = true;
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
		try
		{
			return $value->map(function ($item)
			{
				return $this->getItemValue($item);
			});
		}
		catch (\Throwable $e)
		{
			if($this->debug())
				throw $e;

			return collect();
		}
	}

	public function getColumnDefDataIndexString()
	{
		return "data-cellindex=' + i + ' ";
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
		}";
	}

	public function getCompiledAsRowClassConditionScript()
	{
		return "

			window.carc = data[" . $this->getIndex() . "];
			window.compiledAsClass = false;

			if(window.carc.length == 0)
				window.compiledAsClass = false;
			else
			{
				if(Array.isArray(window.carc))
				{
					for(let i = 0; i < window.carc.length; i++)
					{ 
						if(Array.isArray(window.carc))
						{

						}
						else
						{
							console.log('areo qua, gestire areo qua adesso! normale dentro array');
						}
						

						console.log('arreyo');
						console.log(window.carc[i]);
					}
				}
				else
				{
					console.log('areo qua, gestire areo qua adesso!');
					console.log(data[" . $this->getIndex() . "]);					
				}

			}
	    ";
	}

}