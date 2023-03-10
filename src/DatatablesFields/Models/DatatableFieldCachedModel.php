<?php

namespace IlBronza\Datatables\DatatablesFields\Models;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Support\Str;

class DatatableFieldCachedModel extends DatatableField
{
	private function manageChildType()
	{
		$this->addChildField();
	}

	public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);

		$this->manageChildType();
	}

    public function transformValue($value)
    {
    	$item = cache()->remember(
			Str::slug($this->modelClass . $value),
			3600,
			function() use($value)
			{
				return $this->modelClass::find($value);
			}
		);

		return $this->getItemValue($item);
    }

	public function getItemValue($item)
	{
		try
		{
			if($propertyName = $this->child->getPropertyName())
				$item = $this->getFieldCellDataValue($propertyName, $item);

			return $this->child->transformValue($item);			
		}
		catch(\Exception $e)
		{
			return $e->getMessage();
		}
	}

}