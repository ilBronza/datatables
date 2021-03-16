<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldDelete extends DatatableField
{

	//TODO OCIO
	public function transformValue($value)
	{
		if($value->trashed())
			return [1 => $value->getDestroyUrl()];

		return [0 => $value->getDeleteUrl()];
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
		if(item)
		{
			if(typeof item[1] !== \"undefined\")
				item = '<button data-destroy=\"1\" data-url=\"' + item[1] + '\" class=\"delete-button button-delete uk-text-danger\" type=\"button\" uk-icon=\"icon: trash\"></button>';

			elseif(typeof item[0] !== \"undefined\")
				item = '<button data-url=\"' + item[0] + '\" class=\"delete-button button-delete\" type=\"button\" uk-icon=\"icon: trash\"></button>';
		}

		else item = ''";
	}
}
