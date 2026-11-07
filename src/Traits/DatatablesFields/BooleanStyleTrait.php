<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

/** Stable, column-wide targets, independent of which boolean states are on the page. */
trait BooleanStyleTrait
{
    public function getBooleanStyleDefinitions() : array
    {
        return [
            'true' => ['icon' => $this->trueIcon, 'family' => $this->iconType ?? 'solid'],
            'false' => ['icon' => $this->falseIcon, 'family' => $this->iconType ?? 'solid'],
            'null' => ['icon' => $this->nullIcon, 'family' => $this->iconType ?? 'solid'],
        ];
    }

    protected function booleanStyleAttributes(string $state, string $icon) : string
    {
        return ' data-style-target="boolean-' . $state . '" data-style-state="' . $state
            . '" data-style-default-icon="' . e($icon) . '" ';
    }

    protected function booleanStyleIconExpression(string $state, string $icon) : string
    {
        $root = $this;
        while ($root->parent ?? null) $root = $root->parent;
        $column = json_encode($root->getCamelName());
        $target = json_encode('td [data-style-target="boolean-' . $state . '"]');
        $default = json_encode($icon);

        return "(window.ibDtColumnStyles ? window.ibDtColumnStyles.icon(meta.settings.nTable.id, {$column}, {$target}, {$default}) : {$default})";
    }
}
