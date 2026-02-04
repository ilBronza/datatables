<?php

namespace IlBronza\Datatables\Providers;

use function array_keys;
use function array_search;
use function array_slice;
use function array_splice;
use function dd;

abstract class FieldsGroupParametersFile
{
	public $parameters;

	abstract static function getFieldsGroup() : array;

	static function getTracedFieldsGroup()
	{
		app('uikittemplate')->addFieldsGroupsNames(static::class);

		return static::getFieldsGroup();
	}

	static function create()
	{
		$fieldsGroup = new static();

		$fieldsGroup->setParameters(
			static::getTracedFieldsGroup()
		);

		return $fieldsGroup;
	}

	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}

	public function getParameters() : array
	{
		return $this->parameters;
	}

	static function mergeAfter(array $fieldGroup) : static
	{
		$fieldsGroup = static::create();

		$baseFields = $fieldsGroup->getParameters();

        foreach($baseFields as $key => $value)
            if($_value = ($fieldGroup[$key] ?? false))
            {
                $baseFields[$key] = array_merge(
                    $value,
                    $_value
                );

                unset($fieldGroup[$key]);
            }

        foreach($fieldGroup as $key => $value)
            $baseFields[$key] = $value;

        $fieldsGroup->setParameters(
        	$baseFields
        );

        return $fieldsGroup;
	}

	static function getMergedAfter(array $parameters) : array
	{
		return static::mergeAfter($parameters)->getParameters();
	}
	
	static function insertAfter(array $fieldsGroup, string $key, array $elements)
	{
		$fields = $fieldsGroup['fields'];

		$keys = array_keys($fields);
		$pos = array_search($key, $keys);

		$resultFields = array_slice($fields, 0, $pos, true) + $elements + array_slice($fields, $pos, null, true);

		$fieldsGroup['fields'] = $resultFields;

		return $fieldsGroup;
	}
}