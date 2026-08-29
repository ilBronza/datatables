<?php

namespace IlBronza\Datatables\Buttons;

use IlBronza\Buttons\Button;
use InvalidArgumentException;

/**
 * A declarative DataTables button that toggles one logical group of columns.
 *
 * The group itself is declared by fields through `fieldsGroupsDefinitions`; this
 * class only identifies which already-declared group the button controls.
 */
class FieldsGroupToggleButton extends Button
{
    protected string $fieldGroup;
    protected ?string $activeText = null;
    protected ?string $inactiveText = null;

    public function __construct(string $fieldGroup, array $parameters = [])
    {
        $activeText = $parameters['activeText'] ?? null;
        $inactiveText = $parameters['inactiveText'] ?? null;

        unset($parameters['activeText'], $parameters['inactiveText']);

        parent::__construct($parameters);

        $this->setFieldGroup($fieldGroup);
        $this->setActiveText($activeText);
        $this->setInactiveText($inactiveText);
    }

    public static function forGroup(string $fieldGroup, array $parameters = []) : static
    {
        return new static($fieldGroup, $parameters);
    }

    public function setFieldGroup(string $fieldGroup) : static
    {
        $fieldGroup = trim($fieldGroup);

        if ($fieldGroup === '')
            throw new InvalidArgumentException('A fields-group toggle button requires a non-empty group name.');

        $this->fieldGroup = $fieldGroup;

        return $this;
    }

    public function getFieldGroup() : string
    {
        return $this->fieldGroup;
    }

    public function setActiveText(?string $activeText) : static
    {
        $this->activeText = $activeText;

        return $this;
    }

    public function getActiveText() : string
    {
        if ($this->activeText === null)
            return $this->getText();

        return trans($this->activeText);
    }

    public function setInactiveText(?string $inactiveText) : static
    {
        $this->inactiveText = $inactiveText;

        return $this;
    }

    public function getInactiveText() : string
    {
        if ($this->inactiveText === null)
            return $this->getText();

        return trans($this->inactiveText);
    }

    /**
     * DataTables-specific configuration. Presentation options are deliberately
     * left to the inherited Button API and added by the table renderer.
     */
    public function getDatatablesDefinition() : array
    {
        return [
            'extend' => 'fieldsGroupToggle',
            'fieldGroup' => $this->getFieldGroup(),
            'activeText' => $this->getActiveText(),
            'inactiveText' => $this->getInactiveText(),
        ];
    }

    /**
     * This button is handled by the registered DataTables extension, not by a
     * JavaScript snippet rendered from the PHP application.
     */
    public function renderJsMethod() : ?string
    {
        return null;
    }
}
