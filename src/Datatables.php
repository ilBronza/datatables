<?php

namespace IlBronza\Datatables;

use IlBronza\Datatables\Traits\DatatableTrait;

class Datatables
{
	use DatatableTrait;

    public $request;
    public $rowId = 0;
    public $pageLength = 100;
    public $datatableKeys = true;
    public $showTitle = false;
    public $saveState = true;
    public $showColumnToggler = true;
    public $showHeader = true;
    public $showFooter = true;
    public $showRowToggler = false;
    public $showUtilityButtons = true;
    public $showCounters = true;
    public $deferRender = false;
    public $columnReorder = false;
    public $select = false;
    public $mustRenderBody = false;
    public $keyTable = true;
    public $indexes = [];
    public $views = [];
    public $footerViews = [];
    public $customColumnDefs = [];
    public $cellClasses = [];
    public $defaultFrameworkClasses = "uk-table uk-table-hover uk-table-striped";
    public $fieldsGroupsArray = [];
    public $indexStartingValue = 0;
    public $id;

    public $form;
    public $viewParameters = [];

    public $scrollX = true;

    public $topViews = [];
    public $bottomViews = [];
    
    public $buttons = [];
    public $secondNavbarButtons = [];

	public $dom = '<"table-wrap" <"control-wrap" <"searching" <"uk-grid" <"uk-width-2-3@m filter" B><"uk-width-1-3@m search" f>>><"navigation" <"uk-grid" <"uk-width-1-3@m information" i><"uk-width-1-3@m pagination" p><"uk-width-1-3@m length" l>>>>t>';
    public $minimalDom = '<"table-wrap" <"control-wrap" <"navigation" <"uk-grid" <"uk-width-1-3@m information" ><"uk-width-1-3@m pagination" p><"uk-width-1-3@m length" l>>>>t>';

	public function setcontrollerTableTrait(string $controllerTableTrait)
	{
		$this->controllerTableTrait = $controllerTableTrait;
	}

	public function fieldsGroups(array $fieldsGroups)
	{
		$this->fieldsGroupsArray = array_merge($this->fieldsGroupsArray, $fieldsGroups);
	}

	public function setElementsGetter(callable $elementsGetter)
	{
		$this->elementsGetter = $elementsGetter;
	}

	public function renderTable()
    {
        // if($this->json)
        //     return $this->elementsToJson();

        return $this->renderStructure();
    }

	/**
	 * once fieldsGroups are ready, parse them and set final absoluteIndexes
	 */
	//parseTableAbsoluteIndexes
	public function parseAbsoluteIndexes()
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

	// /**
	//  * add fieldsGroups to table, based on given fieldsGroups names
	//  *
	//  * @param array $fields 
	//  */
	// public function addFieldsGroups(array $fields)
	// {
	// 	// the whole array with groupNames as key and fields as value
	// 	// $fieldsGroups = $this->modelClass::NEWgetTableFieldsGroups($fields);
	// 	$fieldsGroups = $this->getTableFieldsGroups($this->getControllerTableTrait(), $fields);

	// 	mori($fieldsGroups);

	// 	$this->_addFieldsGroups($fieldsGroups);
	// }

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
			$this->fieldsGroups[$name] = new DatatableFieldsGroup($name);

		$this->fieldsGroups[$name]->table = $this;

		return $this->fieldsGroups[$name];
	}

	private function getControllerTableTrait()
	{
		if($this->controllerTableTrait)
			return $this->controllerTableTrait;

		return Str::before(request()->route()->getAction()['controller'], '@');
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

	public function getTableFieldsGroup($fullQualifiedClass, string $key)
	{
		if(($table = $fullQualifiedClass::$tables[$key]?? null) === null)
			return null;

		if(isset($table['fields']))
			return $table['fields'];

		return $table;
	}

	private function parseFieldsGroups()
	{
		// the whole array with groupNames as key and fields as value
		// $fieldsGroups = $this->modelClass::NEWgetTableFieldsGroups($fields);
		$fieldsGroups = $this->getTableFieldsGroups($this->getControllerTableTrait(), $this->fieldsGroupsArray);

		$this->_addFieldsGroups($fieldsGroups);
	}

    public function needsRawCallback()
    {
        return true;
    }

    public function renderStructure()
    {
    	$this->parseFieldsGroups();
        $this->parseAbsoluteIndexes();
        $this->parseColumnDefs();
        $this->parseCellClasses();
        $this->parseOptions();

        $table = $this;

        return view('datatables::datatables.table', compact('table'));    	
    }

    public function hasKeyTable()
    {
        return $this->keyTable;
    }

    public function mustRenderBody()
    {
        return $this->mustRenderBody;
    }

    public function saveState()
    {
        return $this->saveState;
    }

    public function getRowId()
    {
        return $this->rowId;
    }

    public function datatableKeys()
    {
        return $this->datatableKeys;
    }

}
