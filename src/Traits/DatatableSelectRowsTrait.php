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
        $newField = $this->addField('selectRow', ["type" => "selectRowCheckbox"]);

        $fieldsGroup = $this->createFieldsGroup('selectRow');
        $fieldsGroup->addField('selectRow', $newField);

        $this->assignIndexToField('selectRow', 0);

        $this->selectRowCheckboxes = true;
    }
}