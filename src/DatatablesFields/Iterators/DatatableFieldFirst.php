<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

class DatatableFieldFirst extends DatatableFieldEach
{
	public function transformValue($value)
	{
		if(! ($item = $value->first()))
			return ;

		//return first element of array, object, collection
		return $this->getItemValue($item);
	}

	public function getCustomColumnDefSingleResult()
	{
		return $this->child->getCustomColumnDefSingleResult();
	}

	public function getCustomColumnDef()
	{
		return "
		{
            //" . $this->getName() . "
			targets: [" . $this->getIndex() . "],
			render: function ( item, type, row, meta )
			{
				" . $this->child->getCustomColumnDefSingleResult() . "
				" . $this->getEndingResultOptions() . "

				item += '" . $this->separator . "';

				return item;
			}
		}";
	}
}