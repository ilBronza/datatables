<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldEach extends MultipleDatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;

	public $child;
	public $childParameters;
	public $order;

	protected function manageChildType()
	{
		$this->addChildField();
	}

	public function __construct(string $name, array $parameters = [], int $index = null, DatatableField $parent = null, Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);

		$this->manageChildType();
	}

	public function getItemValue($item)
	{
		try
		{
			if($propertyName = $this->child->getPropertyName())
				$item = $this->getFieldCellDataValue($propertyName, $item);

			return $this->child->transformValue($item);			
		}
		catch(\Throwable $e)
		{
			if($this->debug())
				throw $e;

			return $this->handleError($e);
		}
	}

	public function getColumnDefSingleResult()
	{
		return $this->child->getColumnDefSingleResult();
	}

	public function getCustomColumnDefSingleResult()
	{
		return $this->child->getCustomColumnDefSingleResult();
	}

	public function getSearchJavascriptString()
	{
		if(method_exists($this->child, '_getCustomColumnDefSingleSearchResult'))
			return $this->child->_getCustomColumnDefSingleSearchResult();

		return $this->child->getCustomColumnDefSingleResult();
	}

	public function getCustomColumnDef()
	{
		// $singleColumnDef = (method_exists($this->child, 'getColumnDefSingleResult')) ? $this->child->getColumnDefSingleResult() : 'item;';

		$result = "
		{
			//" . $this->getName() . "
			targets: [" . $this->getIndex() . "],
			render: function ( data, type, row, meta )
			{
				if(type == 'display')
				{
					let result = '';

					let i = 0;

					data.forEach(function(item)
					{
						" . $this->child->getCustomColumnDefSingleResult() . "
						" . $this->getEndingResultOptions() . "

						if(item)
							result += item;

						i ++;

						result += '" . $this->separator . "';
					});

					return result;
				}
				
                if(type == 'filter')
                {
					let result = '';

					let i = 0;

					data.forEach(function(item)
					{
						" . $this->getSearchJavascriptString() . "

						if(item)
							result += item + ' ';
					});

					return result;
                }


				return data;
			}
		}";

		return $result;
	}
}