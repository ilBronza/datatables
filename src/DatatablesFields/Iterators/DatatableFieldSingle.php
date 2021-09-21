<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

class DatatableFieldSingle extends DatatableFieldEach
{
	public function getCustomColumnDefSingleResult()
	{
		return $this->child->getCustomColumnDefSingleResult();
	}

	public function getColumnDefDataIndexString()
	{
		return null;
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