<?php

namespace IlBronza\Datatables\DatatablesFields\Iterators;

class DatatableFieldFirst extends DatatableFieldEach
{
	// public $child;

	// private function manageChildType()
	// {
 //        $this->child = static::createByType(
 //            $this->name,
 //            $this->childParameters['type'],
 //            $this->childParameters,
 //            0
 //        );
	// }

	// public function __construct(string $name, array $parameters = [], int $index = null)
	// {
	// 	parent::__construct($name, $parameters, $index);

	// 	$this->manageChildType();
	// }

	public function transformValue($value)
	{
		if(! isset($this->element))
			return ;

		//return first element of array, object, collection
		foreach($value->{$this->element} as $item)
			return $this->getItemValue($item);
	}

	public function getCustomColumnDefSingleResult()
	{
		return $this->child->getCustomColumnDefSingleResult();
	}

	public function getCustomColumnDef()
	{
		// $singleColumnDef = (method_exists($this->child, 'getColumnDefSingleResult')) ? $this->child->getColumnDefSingleResult() : 'item;';

		// mori($singleColumnDef);

		return "
		{
            //" . $this->getName() . "
			targets: [" . $this->getIndex() . "],
			render: function ( item, type, row, meta )
			{
				if(type == 'display')
				{
					// let result = '';

					// data.forEach(function(item)
					// {
						" . $this->child->getCustomColumnDefSingleResult() . "
                    	" . $this->getEndingResultOptions() . "

						// if(item)
						// 	result += item;

						item += '" . $this->separator . "';
					// });

					// return result;
				}

				return item;
			}
		}";
	}
}