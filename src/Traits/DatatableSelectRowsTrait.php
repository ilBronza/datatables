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

        $selectRowField = $this->addField('selectRow', ["type" => "selectRowCheckbox"]);
        $fieldsGroup->addField('selectRow', $selectRowField);

        $primaryField = $this->addField('primary', ["type" => "primary"]);
        $fieldsGroup->addField('primary', $primaryField);

        $this->selectRowCheckboxes = true;
    }
}