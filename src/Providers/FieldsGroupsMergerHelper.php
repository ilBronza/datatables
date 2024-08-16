<?php

namespace IlBronza\Datatables\Providers;

use Illuminate\Support\Collection;

use function array_merge;
use function array_pop;
use function dd;

class FieldsGroupsMergerHelper
{
	public Collection $fieldsGroups;

	public function setFieldsGroups(Collection $fieldsGroups) : void
	{
		$this->fieldsGroups = $fieldsGroups;
	}

	public function getFieldsGroups() : Collection
	{
		return $this->fieldsGroups;
	}

	public array $result;

	public function __construct()
	{
		$this->fieldsGroups = collect();
	}

	public function addFieldsGroupParameters(array $parameters)
	{
		$this->fieldsGroups->push($parameters);
	}

	public function getMergedFieldsGroups()
	{
		$result = collect();

		foreach($this->fieldsGroups as $fieldsGroup)
			$result = $result->mergeRecursive($fieldsGroup);

		return $result;
	}

	public function moveFieldToEnd(string $fieldName)
	{
		//TODO
		/**
		 * perché per modificare un elemento della collection devo fare tutto sto giro osceno?
		 * test andati male, ritentare con più tempo
		 */
		$tempField = null;

		$result = [];

		foreach($this->getFieldsGroups() as $fieldsGroup)
		{
			foreach($fieldsGroup['fields'] as $_fieldName => $parameters)
				if($_fieldName == $fieldName)
				{
					$tempField = $parameters;
					unset($fieldsGroup['fields'][$_fieldName]);
				}

			$result[] = $fieldsGroup;
		}

		if($tempField)
		{
			$lastFieldsGroup = array_pop($result);
			$lastFieldsGroup['fields'][$fieldName] = $tempField;
			$result[] = $lastFieldsGroup;
		}

		$this->setFieldsGroups(collect($result));
	}
}
