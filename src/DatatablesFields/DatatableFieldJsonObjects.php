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

	public function transformValue($value)
	{
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
					let fields = " . $this->getJsonProperties() . ";
					let result = new Array();

					objects = JSON.parse(data);

					for (var key in objects)
						if (objects.hasOwnProperty(key))
							result.push(window.datatablesGetJsonObjectString(fields, objects[key]));

					// for (var key in objects)
					// 	result.push(window.datatablesJsonEncode(objects[key]));

					if(result.length == 0)
						return '';

					return '<pre class=\"noborder\">' + result.join('<br />') + '</pre>';
				}

				return data;
			}
		}
		";
	}
}