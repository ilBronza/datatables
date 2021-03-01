<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldEditor extends DatatableField
{
	public function getCustomColumnDef()
	{
		$fieldIndex = $this->getIndex();

		return "
		{
			targets: [" . $this->getIndex() . "],
			render: function ( item, type, row, meta )
			{
				if(type == 'display')
				{
					" . $this->getCustomColumnDefSingleResult() . ";
				}

			return item;
			}
		}";
	}

	
}