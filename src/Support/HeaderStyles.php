<?php

namespace IlBronza\Datatables\Support;

use InvalidArgumentException;

/** Controller-defined styles for column titles, including scrolling/fixed clones. */
class HeaderStyles
{
    public static function modeCss(string $tableId, bool $expanded) : string
    {
        return static::css($tableId, [
            'white-space' => $expanded ? 'normal' : 'nowrap',
            'text-wrap' => $expanded ? 'wrap' : 'nowrap',
            'overflow-wrap' => $expanded ? 'anywhere' : 'normal',
            'overflow' => $expanded ? 'visible' : 'hidden',
            'text-overflow' => $expanded ? 'clip' : 'ellipsis',
        ]);
    }

    public static function css(string $tableId, array $styles) : string
    {
        $declarations = [];
        foreach ($styles as $property => $raw)
        {
            if (! is_string($property) || ! preg_match('/^(?:--[a-zA-Z0-9_-]+|-?[a-zA-Z][a-zA-Z0-9-]*)$/D', $property)
                || (! is_string($raw) && ! is_int($raw) && ! is_float($raw)))
                throw new InvalidArgumentException('headerStyles must contain CSS property/value pairs.');

            $value = trim((string) $raw);
            if ($value === '' || preg_match('/[{}<>;\x00-\x1f]/', $value))
                throw new InvalidArgumentException('Invalid CSS value in headerStyles: ' . $property);

            $declarations[] = $property . ':' . $value . (preg_match('/!important$/iD', $value) ? '' : '!important');
        }

        if (! $declarations)
            return '';

        $id = '"' . preg_replace_callback('/[\\\\"<>\x00-\x1f]/', fn ($match) => '\\' . dechex(ord($match[0])) . ' ', $tableId) . '"';
        $targets = [];
        foreach (['table[id=' . $id . ']', 'table[data-realid=' . $id . ']'] as $table)
        {
            $title = $table . ' > thead > tr.columnsintestations > th .dt-column-title';
            $targets[] = $title;
            $targets[] = $title . ' > div';
        }

        return implode(",\n", $targets) . '{' . implode(';', $declarations) . '}';
    }
}
