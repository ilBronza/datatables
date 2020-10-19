<?php

namespace IlBronza\Datatables;

use Illuminate\Http\Request;

class OldDatatables extends Datatables
{
	public function __construct(Request $request = null, string $model, array $fieldNames, string $cacheKey = null, callable $elementsGetter = null, string $modelController = null)
	{
		$this->request = $request;

		$this->setControllerTableTrait($modelController);
		$this->fieldsGroups($fieldNames);
		$this->setElementsGetter($elementsGetter);
	}


}
