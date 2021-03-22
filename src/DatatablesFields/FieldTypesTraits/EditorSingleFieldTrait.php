<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;


trait EditorSingleFieldTrait
{
    public function getEditorFieldType()
    {
        return $this->fieldType;
    }

    public function getValueString()
    {
        return " data-originalvalue=\"' + item[1] + '\" value=\"' + item[1] + '\" ";
    }

    public function getTypeString()
    {
        return " type=\"" . $this->getEditorFieldType() . "\" ";
    }
}

