<?php

namespace IlBronza\Datatables;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldsGroup
{
	public $fields;
	public $name;

	public function __construct(string $name)
	{
	    $this->name = $name;

	    $this->fields = collect();
	}

	public function addField(string $fieldName, DatatableField $field)
	{
		$this->fields[$fieldName] = $field;
	}

	public function getFieldByName(string $fieldName)
	{
		foreach($this->fields as $field)
			if($field->name == $fieldName)
				return $field;
	}

	public function assignRoles(array $roles)
	{
		$this->allowedForRoles = $roles;

		foreach($this->fields as $field)
			$field->assignRoles($roles);
	}

	public function assignForbiddenRolesToFields(array $groupRoles)
	{
		foreach($groupRoles as $fieldName => $forbiddenRoles)
		{
			$field = $this->getFieldByName($fieldName);

			$field->assignForbiddenRoles($forbiddenRoles['roles']);
		}
	}

	public function assignRolesToFields(array $groupRoles)
	{
		foreach($groupRoles as $fieldName => $roles)
		{
			$field = $this->getFieldByName($fieldName);

			$field->assignRoles($roles['roles']);
		}
	}

	public function assignSummaryToFields(array $summary)
	{
		foreach($summary as $fieldName => $_summary)
		{
			$field = $this->getFieldByName($fieldName);

			$field->assignSummary($_summary);				
		}		
	}
}