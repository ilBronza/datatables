<?php

namespace IlBronza\Datatables\Traits;

use Illuminate\Support\Collection;

use IlBronza\Datatables\DatatableFieldsGroup;
use IlBronza\Datatables\DatatablesFields\DatatableField;

trait DatatableFieldsTrait
{
    private function parsePermissionsAndRoles()
    {
        foreach($this->fieldsGroups as $fieldGroup)
            foreach($fieldGroup->fields as $key => $field)
                if(! $field->isAllowed())
                {
                    $fieldGroup->fields->forget($key);
                    $this->fields->forget($key);
                }        
    }

    private function condensateIndexes()
    {
        $index = 0;

        foreach($this->getFields() as $field)
            $field->index = $index ++;

    }

    /**
     * add fieldsGroups to table, based on given fieldsGroups full array
     *
     * @param array $fieldsGroups 
     */
    private function addFieldsGroups(array $fieldsGroups)
    {
        foreach($fieldsGroups as $name => $fieldsGroup)
            $this->addFieldsGroup($name, $fieldsGroup);

        $this->parsePermissionsAndRoles();
        $this->condensateIndexes();
        $this->parseRowId();
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
            $this->fieldsGroups[$name] = new DatatableFieldsGroup($name);

        return $this->fieldsGroups[$name];
    }

    /**
     * add array of fields to a DatatablesFieldsGroup
     *
     * @param DatatableFieldsGroup $fieldsGroup
     * @param array $fields
     **/
    public function addFieldsToGroup(DatatableFieldsGroup $fieldsGroup, array $fields)
    {
        foreach($fields as $fieldName => $fieldParameters)
        {
            // mori($fieldParameters);
            $newField = $this->addField($fieldName, $fieldParameters);
            $fieldsGroup->addField($fieldName, $newField);
        }
    }

    /**
     * set permisssions to each datatablefield owned by given group
     **/
    public function setPermissionsToGroupFields(DatatableFieldsGroup $fieldsGroup, array $permissions)
    {
        if($groupRoles = $permissions['roles'] ?? false)
            $fieldsGroup->assignRoles($groupRoles);

        if($onlyRoles = $permissions['onlyRoles'] ?? false)
            $fieldsGroup->assignRolesToFields($onlyRoles);

        if($onlyRoles = $permissions['exceptRoles'] ?? false)
            $fieldsGroup->assignForbiddenRolesToFields($onlyRoles);
    }

    public function hasSummary()
    {
        return !! $this->summary;
    }

    private function hasSummaryType(string $type)
    {
        return isset($this->symmary[$type]);
    }

    private function assignSummaryTypes(array $summary)
    {
        if(! isset($this->summary))
            $this->summary = [];

        foreach($summary as $fieldName => $summaryType)
        {
            if(! $this->hasSummaryType($summaryType))
                $this->summary[$summaryType] = [];

            $this->summary[$summaryType][$fieldName] = true;
        }
    }

    private function setSummaryToGroupFields(DatatableFieldsGroup $fieldsGroup, array $summary)
    {
        $this->assignSummaryTypes($summary);

        $fieldsGroup->assignSummaryToFields($summary);
    }

    /**
     * add fieldsGroup to table, based on given fieldsGroup full array
     *
     * @param string $name
     * @param array $fieldsGroup
     */
    public function addFieldsGroup($name, $fieldsGroup)
    {
        $group = $this->createFieldsGroup($name);

        $fields = $fieldsGroup['fields'];
        $permissions = $fieldsGroup['permissions'] ?? [];
        $summary = $fieldsGroup['summary'] ?? [];

        $this->addFieldsToGroup($group, $fields);
        $this->setPermissionsToGroupFields($group, $permissions);
        $this->setSummaryToGroupFields($group, $summary);
    }

    private function TODO_ChangeAllViewsInTypeGetType(array $fieldParameters) : string
    {
        return $fieldParameters['view'] ?? $fieldParameters['type'];
    }

    /**
     * return fields collection
     *
     * @return Collection
     **/
    public function getFields() : Collection
    {
        return $this->fields->sortBy('index');
    }

    public function getFieldByName(string $fieldName)
    {
        return $this->fields->firstWhere('name', $fieldName);
    }

    /**
     * add fields to fields collection
     */
    public function addFields(array $fields)
    {
        foreach($fields as $fieldName => $parameters)
            $this->addField($fieldName, $parameters);
    }

    /**
     * check if parameters are already an array, otherwise is just view name and set it as array
     *
     * @param mixed $fieldParameters
     * @return array
     */
    private function prepareFieldParameters($fieldParameters) : array
    {
        if(is_array($fieldParameters))
        {
            if(isset($fieldParameters['childType']))
            {
                $fieldParameters['childParameters'] = $this->prepareFieldParameters($fieldParameters['childType']);
            }

            return $fieldParameters;
        }

        //if is just the view, set it as view parameter
        if(is_string($fieldParameters))
        {
            if(substr( $fieldParameters, 0, 4 ) === "_fn_")
                return [
                    'type' => 'function',
                    'function' => substr($fieldParameters, 4)
                ];

            if(substr( $fieldParameters, 0, 5 ) === "_btn_")
                return [
                    'type' => 'button',
                    'button' => substr($fieldParameters, 5)
                ];

            return [
                'type' => $fieldParameters
            ];

        }

        throw new \Exception('wrong field parameteres for table ' . json_encode($this->name));      
    }

    public function addField(string $fieldName, $parameters)
    {
        $fieldParameters = $this->prepareFieldParameters($parameters);

        $fieldType = $this->TODO_ChangeAllViewsInTypeGetType($fieldParameters);

        return $this->fields[$fieldName] = DatatableField::createByType(
            $fieldName,
            $fieldType,
            $fieldParameters,
            $this->getNextFieldIndex()
        );
    }

    public function setRowIdIndex(int $rowIdIndex)
    {
        $this->rowId = $rowIdIndex;
    }

    public function parseRowId()
    {
        foreach($this->fields as $field)
            if($field->isRowId())
            {
                $this->setRowIdIndex($field->getIndex());
                return ;
            }
    }

    public function getRowIdIndex()
    {
      return $this->rowId;
    }

    public function assignIndexToField(string $fieldName, int $index)
    {
        $incrementingFields = $this->fields->where('index', '>=', $index);

        foreach($incrementingFields as $incrementingField)
            $incrementingField->incrementIndex();

        $field = $this->getFieldByName($fieldName);
        $field->setIndex($index);
    }
}