<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldJsonObjects extends DatatableField
{
	private function getProperties()
	{
		return $this->properties;
	}

	private function getJsonProperties()
	{
		return json_encode($this->getProperties());
	}

	public function getCustomColumnDefSingleResult()
	{
		return "
			if(item)
			{
				let fields = " . $this->getJsonProperties() . ";
				let result = new Array();

				objects = JSON.parse(item);

				for (var key in objects)
					if (objects.hasOwnProperty(key))
						result.push(window.datatablesGetJsonObjectString(fields, objects[key]));

				if(result.length == 0)
					item = '';

				else
					item = '<pre class=\"noborder\">' + result.join('<br />') + '</pre>';
			}


			else item = ''";
	}
}