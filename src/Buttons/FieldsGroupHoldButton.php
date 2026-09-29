<?php

namespace IlBronza\Datatables\Buttons;

/**
 * Shows a logical group of columns only while the button is held down.
 */
class FieldsGroupHoldButton extends FieldsGroupToggleButton
{
    public function getDatatablesDefinition() : array
    {
        return array_replace(parent::getDatatablesDefinition(), [
            'extend' => 'fieldsGroupHold',
        ]);
    }
}
