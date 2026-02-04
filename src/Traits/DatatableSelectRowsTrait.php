<?php

namespace IlBronza\Datatables\Traits;

trait DatatableSelectRowsTrait
{
    public function getFirstFieldsGroup()
    {
        return $this->fieldsGroups->first();
    }

    public function hasSelectRowCheckboxes()
    {
        return $this->selectRowCheckboxes ?? false;
    }

    public function setRowSelectCheckboxes()
    {
        $fieldsGroup = $this->createFieldsGroup('selectRow');

        $selectRowField = $this->addField('selectRow', [
			"type" => "selectRowCheckbox"
        ]);

        $fieldsGroup->addField('selectRow', $selectRowField);

        $primaryField = $this->addField('primary', ["type" => "primary"]);
        $fieldsGroup->addField('primary', $primaryField);

        $this->selectRowCheckboxes = true;
    }

    public function fieldsGroupsRequiresSelectRowCheckboxes(array $fieldsGroups) : bool
    {
        foreach($fieldsGroups as $fieldsGroup)
            foreach($fieldsGroup['fields'] as $name => $parameters)
                if($this->craeteField($name, $parameters)->requiresSelectRowCheckboxes())
                    return true;

        return false;
    }
}