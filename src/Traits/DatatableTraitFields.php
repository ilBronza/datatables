<?php

namespace IlBronza\Datatables\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

function toArray($element)
{
	if(is_array($element))
		return $element;

	return [$element];
}


trait DatatableTraitFields
{

    public function getTableFieldsGroup($fullQualifiedClass, string $key)
    {
        if(($table = $fullQualifiedClass::$tables[$key]?? null) === null)
            return null;

        if(isset($table['fields']))
            return $table['fields'];

        return $table;
    }


	/**
	 * takes all the necessary fieldsGroups by key
	 *
	 * @param string|string $fullQualifiedClass
	 * @param array|string $keys
	 *
	 * @return array
	 */
	public function getTableFieldsGroups($fullQualifiedClass, $keys)
	{
		$keys = toArray($keys, true);

		$groups = [];

		foreach ($keys as $key)
			// if(($table = static::NEWgetTableFieldsGroup($key)) !== null)
			if(($table = $this->getTableFieldsGroup($fullQualifiedClass, $key)) !== null)
				$groups[$key] = $table;

		return $groups;
	}

	private function getModelController()
	{
		if($this->modelController)
			return $this->modelController;

		return Str::before(request()->route()->getAction()['controller'], '@');
	}

	/**
	 * add fieldsGroups to table, based on given fieldsGroups names
	 *
	 * @param array $fields 
	 */
	public function addFieldsGroups(array $fields)
	{
		// the whole array with groupNames as key and fields as value
		// $fieldsGroups = $this->modelClass::NEWgetTableFieldsGroups($fields);
		$fieldsGroups = $this->getTableFieldsGroups($this->getModelController(), $fields);

		$this->_addFieldsGroups($fieldsGroups);
	}

	/**
	 * add fieldsGroups to table, based on given fieldsGroups full array
	 *
	 * @param array $fieldsGroups 
	 */
	private function _addFieldsGroups(array $fieldsGroups)
	{
		foreach($fieldsGroups as $name => $fieldsGroup)
			$this->_addFieldsGroup($name, $fieldsGroup);
	}

	/**
	 * add fieldsGroup to table, based on given fieldsGroup full array
	 *
	 * @param string $name
	 * @param array $fieldsGroup
	 */
	private function _addFieldsGroup($name, $fieldsGroup)
	{
		$group = $this->createFieldsGroup($name);

		$group->populateFields($fieldsGroup);
	}

	/**
	 * if table has not jey created a given name's fieldsGroup, it creates one
	 *
	 * @param string $name
	 *
	 * @return \newFieldsGroup
	 */
	private function createFieldsGroup($name)
	{
		if(empty($this->fieldsGroups[$name]))
			// $this->fieldsGroups[$name] = $this->newFieldsGroup($name);
			$this->fieldsGroups[$name] = new \datatableFieldsGroup($name);

		$this->fieldsGroups[$name]->table = $this;

		return $this->fieldsGroups[$name];
	}



	/**
	 * once fieldsGroups are ready, parse them and set final absoluteIndexes
	 */
	public function parseTableAbsoluteIndexes()
	{
		//0
		$index = $this->indexStartingValue;

		// if($this->mustShowRowToggler())
		// 	$index++;

		foreach($this->fieldsGroups as $fieldGroup)
			$index = $fieldGroup->setAbsoluteIndexes($index);

		foreach($this->fieldsGroups as $fieldGroup)
			$this->indexes = array_merge($this->indexes, $fieldGroup->fields->pluck('absoluteIndex')->toArray());
	}






	// public function setFields($fields)
	// {
	// 	$this->fields = $this->modelClass::getTableFields($fields);

	// 	//if old kind of table, flatten it
	// 	if(isset($this->fields['fields']))
	// 		$this->fields = $this->fields['fields'];
	// }

	// public function addFields($fields)
	// {
	// 	$newFields = $this->modelClass::getTableFields($fields);

	// 	//if old kind of table, flatten it
	// 	if(isset($newFields['fields']))
	// 		$newFields = $newFields['fields'];

	// 	$this->fields = array_merge($this->fields, $newFields);

	// 	$this->addFieldsGroups($fields);
	// }

	// static public function getMainField($field)
	// {
	// 	return explode("|", $field)[0];
	// }

	// static public function getSubFields($field)
	// {
	// 	$fields = explode("|", $field)[1]?? null;

	// 	if(!$fields)
	// 		return [];

	// 	return explode(".", $fields);
	// }


	// public function addSelectColumnByIndex($index)
	// {
	// 	if(! isset($this->selectColumns))
	// 		$this->selectColumns = [];

	// 	if(is_int($index))
	// 		$index = [$index];

	// 	$this->selectColumns = array_merge($this->selectColumns, $index);
	// }

	// public function removeTogglerDefs()
	// {
	// 	if(isset($this->columnDefs['orderDataType']['dom-checkbox']))
	// 		if (($key = array_search(0, $this->columnDefs['orderDataType']['dom-checkbox'])) !== false)
	// 		    unset($this->columnDefs['orderDataType']['dom-checkbox'][$key]);
	// }

	// public function checkTogglerDefs()
	// {
	// 	if(!$this->toggler)
	// 		$this->removeTogglerDefs();

	// 	else
	// 		$this->setColumnDefsByIndex(0, ['orderDataType' => 'dom-checkbox']);
	// }


	private function hasFieldsGroups($name)
	{
		return in_array($name, $this->fieldsGroups);
	}

	public function addServiceFieldsGroup($service)
	{
		$service->loadMissing('servicerows');

		$fields = [];

		foreach($service->servicerows as $servicerow)
			$fields[$servicerow->alias] = $servicerow->getViewType();

		return $this->_addFieldsGroup($service->alias, $fields);

		// $_fields = [];

		// foreach ($service->servicerows as $detailsField => $type)
		// 	$_fields[] = new oldField($type->alias, $this->isHiddenField($this->id?? $this->name, $type->alias), 'flatCustomValues');

  //       $this->fieldsGroups[] = (object) [
  //           'name' => $service->name,
  //           'id' => $service->id,
  //           'columns' => (object) $_fields
  //       ];
	}

	public function addFieldsByDossiers(Collection $dossiers)
	{
		foreach($dossiers as $dossier)
		{
			if(! $this->hasFieldsGroups($dossier->service->name))
				$this->addServiceFieldsGroup($dossier->service);

			foreach($dossier->dossierrows as $dossierrow)
			{
				$field = $dossierrow->getAlias();

				$dossier->$field = $dossierrow->getValue();

				// $this->fields[$field] = 'flatCustomValues';
			}
		}
	}

	public function addFieldsByDetails(Collection $details)
	{
		foreach($details as $detail)
		{
			if(! $this->hasFieldsGroups($detail->data->name))
				$this->addDataFieldsGroup($detail->data);

			foreach($detail->detailrows as $detailrow)
			{
				$field = $detailrow->getAlias();

				$detail->$field = $detailrow->getValue();
			}
		}
	}

	// public function addFieldsByUseractivities()
	// {
	// 	$newFields = [];
	// 	$oldFields = [];

	// 	foreach($this->elements as $useractivity)
	// 	{
	// 		$values = json_decode($useractivity->values);

	// 		foreach($values[0] as $_attribute => $value)
	// 		{
	// 			if(empty($oldFields["old" . $_attribute]))
	// 				$oldFields["old" . $_attribute] = true;

	// 			if(empty($newFields["new" . $_attribute]))
	// 				$newFields["new" . $_attribute] = true;

	// 			$attribute = 'old' . $_attribute;
	// 			$useractivity->$attribute = $value;
	// 		}

	// 		foreach($values[1] as $_attribute => $value)
	// 		{
	// 			$attribute = 'new' . $_attribute;
	// 			$useractivity->$attribute = $value;
	// 		}
	// 	}

	// 	foreach ($newFields as $alias => $value)
	// 		$_newFields[] = new oldField($alias, false, 'flat');

	// 	$this->fieldsGroups[] = (object) [
	// 		'name' => 'newValues',
	// 		'id' => 'newValues',
	// 		'columns' => (object) $_newFields
	// 	];


	// 	foreach ($oldFields as $alias => $value)
	// 		$_oldFields[] = new oldField($alias, false, 'flat');

	// 	$this->fieldsGroups[] = (object) [
	// 		'name' => 'oldValues',
	// 		'id' => 'oldValues',
	// 		'columns' => (object) $_oldFields
	// 	];
	// }

}




























    // //returns the table merged by array
    // //if $table is 'first.second' it merge the two tables
    // //and return them together
    // static function getTableFields($keys, $toggler = false)
    // {
    //     if(is_string($keys))
    //         $keys = [$keys];

    //     $result = [];

    //     foreach ($keys as $key)
    //         if((isset(static::$tables[$key]))&&(null !== ($table = static::$tables[$key])))
    //             foreach($table as $_key => $_table)
    //                 $result[$_key] = array_merge($result[$_key]?? [], $_table);

    //     if($toggler)
    //         $result['toggler'] = true;

    //     return $result;
    // }

    // static function getTableFieldsGroups($keys, $toggler = false)
    // {
    //     if(is_string($keys))
    //         $keys = [$keys];

    //     $groups = [];

    //     foreach ($keys as $key)
    //     {
    //         $result = [];

    //         if((isset(static::$tables[$key]))&&(null !== ($table = static::$tables[$key])))
    //             foreach($table as $_key => $_table)
    //                 $result[$_key] = array_merge($result[$_key]?? [], $_table);

    //         $groups[$key] = $result;
    //     }

    //     return $groups;
    // }



