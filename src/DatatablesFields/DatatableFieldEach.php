<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldEach extends MultipleDatatableField
{
	public $child;

	private function manageChildType()
	{
        $this->child = static::createByType(
            $this->name,
            $this->childParameters['type'],
            $this->childParameters,
            0
        );
	}

	public function __construct(string $name, array $parameters = [], int $index = null)
	{
		parent::__construct($name, $parameters, $index);

		$this->manageChildType();
	}

	public function getItemValue($item)
	{
		return $this->child->transformValue($item);
	}

	public function getColumnDefSingleResult()
	{
		return $this->child->getColumnDefSingleResult();
	}

	public function getCustomColumnDef()
	{
		$singleColumnDef = (method_exists($this->child, 'getColumnDefSingleResult')) ? $this->child->getColumnDefSingleResult() : 'item;';

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
						result += " . $singleColumnDef . ";
						result += '" . $this->separator . "';
					});

					return result;
				}

				return data;
			}
		}
		";
	}
}