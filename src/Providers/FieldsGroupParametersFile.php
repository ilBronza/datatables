<?php

namespace IlBronza\Datatables\Providers;

abstract class FieldsGroupParametersFile
{
	public $parameters;

	abstract static function getFieldsGroup() : array;

	static function create()
	{
		$fieldsGroup = new static();

		$fieldsGroup->setParameters(
			static::getFieldsGroup()
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
}