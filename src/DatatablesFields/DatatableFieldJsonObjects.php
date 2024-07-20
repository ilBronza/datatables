<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldJsonObjects extends DatatableField
{

	/**
	 * IMPORTANT
	 * please cast this model's property as array, returning empty array if null
	 **/

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

				objects = item;

				if(objects.length > 0)
				{
					result.push('<table class=\"jsondfieldtable\"><tr>');

					for (var key in fields) {
						result.push('<th><span uk-tooltip=\"' + key + '\" class=\"uk-text-truncate\">' + key + '</span></th>');
					}
					result.push('</tr>');

					for (var key in objects)
					{
						result.push('<tr>');

						if (objects.hasOwnProperty(key))
							result.push(window.datatablesGetJsonObjectString(fields, objects[key]));

						result.push('</tr>');
					}

					result.push('</table>');
				}

				if(result.length == 0)
					item = '';

				else
					item = result.join('');
			}


			else item = ''";
	}
}