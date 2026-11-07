<?php

namespace IlBronza\Datatables\Support;

/** Scoped CSS shared by the initial render and the column inspector's wire format. */
class ColumnStyles
{
    public static function paths(string $signature) : array
    {
        if (strlen($signature) > 1000)
            return [];

        $token = '(?:[a-z][a-z0-9-]*(?:\.[a-zA-Z_][a-zA-Z0-9_-]*)*(?::nth-of-type\([1-9][0-9]*\))?|\[data-style-target="[a-zA-Z0-9_-]+"\])';
        $pattern = '~^(?:>\s*)?' . $token . '(?:(?:\s+>\s*|\s+)' . $token . ')*$~D';
        $paths = [];
        foreach (explode(' + ', $signature) as $part)
        {
            $path = trim(preg_replace('/^td(?:\s+|$)/', '', trim($part)));
            if ($path !== '' && ! preg_match($pattern, $path))
                return [];
            $paths[] = $path;
        }
        return $paths;
    }

    public static function normalize(array $selectors) : array
    {
        $result = [];
        $enums = [
            'font-style' => ['normal', 'italic'],
            'font-weight' => ['300', '400', '500', '600', '700', 'bold', 'normal'],
            'text-align' => ['left', 'center', 'right', 'start', 'end'],
            'text-transform' => ['none', 'uppercase', 'lowercase', 'capitalize'],
            'text-decoration' => ['none', 'underline', 'line-through'],
            'text-overflow-mode' => ['visible', 'wrap', 'clip', 'ellipsis', 'tooltip'],
        ];
        foreach (array_slice($selectors, 0, 100, true) as $selector => $rules)
        {
            if (! is_string($selector) || ! static::paths($selector) || ! is_array($rules))
                continue;
            $out = [];
            foreach ($rules as $key => $raw)
            {
                if ($key === '__tooltip')
                {
                    if ($raw === true) $out[$key] = true;
                    continue;
                }
                if (! is_string($raw) && ! is_numeric($raw)) continue;
                $value = trim((string) $raw);
                if ($key === '__icon')
                {
                    if (strlen($value) <= 80 && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $value)
                        && preg_match('/^td \[data-style-target="boolean-(true|false|null)"\]$/D', $selector)) $out[$key] = $value;
                }
                elseif (in_array($key, ['width', 'min-width', 'max-width', 'font-size', 'letter-spacing']))
                {
                    if (preg_match('/^-?\d+(?:\.\d+)?$/D', $value)) $value .= 'px';
                    if (preg_match('/^-?\d+(?:\.\d+)?(?:px|em|rem|%)$/D', $value) || (str_contains($key, 'width') && $value === 'auto')) $out[$key] = $value;
                }
                elseif (isset($enums[$key]) && in_array($value, $enums[$key], true)) $out[$key] = $value;
                elseif (in_array($key, ['color', 'background-color']) && preg_match('/^(?:#[\da-f]{3,8}|[a-z]+|(?:rgb|rgba|hsl|hsla)\([\d.,%\s-]+\))$/iD', $value)) $out[$key] = $value;
                elseif ($key === 'font-family' && preg_match('/^[a-zA-Z0-9\s,\'"_-]+$/D', $value)) $out[$key] = $value;
            }
            if (isset($out['width']))
            {
                $out['min-width'] = $out['width'];
                $out['max-width'] = $out['width'];
            }
            if ($out) $result[$selector] = $out;
        }
        return $result;
    }

    private static function quote(string $value) : string
    {
        return '"' . preg_replace_callback('/[\\\\"<>\r\n\f]/', fn ($match) => '\\' . dechex(ord($match[0])) . ' ', $value) . '"';
    }

    private static function declarations(array $rules) : string
    {
        $out = [];
        foreach ($rules as $key => $value)
        {
            if ($key === '__tooltip' || $key === '__icon') continue;
            if ($key === 'text-overflow-mode')
            {
                $out[] = 'white-space:' . ($value === 'wrap' ? 'normal' : 'nowrap') . '!important';
                $out[] = 'overflow:' . (in_array($value, ['visible', 'wrap']) ? 'visible' : 'hidden') . '!important';
                $out[] = 'text-overflow:' . (in_array($value, ['ellipsis', 'tooltip']) ? 'ellipsis' : 'clip') . '!important';
                $out[] = 'overflow-wrap:' . ($value === 'wrap' ? 'anywhere' : 'normal') . '!important';
            }
            else $out[] = $key . ':' . $value . '!important';
        }
        return implode(';', $out);
    }

    public static function css(string $tableId, string $column, array $selectors) : string
    {
        $base = '#' . implode('', array_map(fn ($char) => '\\' . dechex(mb_ord($char)) . ' ', preg_split('//u', $tableId, -1, PREG_SPLIT_NO_EMPTY)));
        $cell = 'td[class~=' . static::quote($column) . ']';
        $output = [];
        foreach (static::normalize($selectors) as $signature => $rules)
        {
            $targets = array_map(fn ($path) => $base . ' ' . $cell . ($path !== '' ? ' ' . $path : ''), static::paths($signature));
            $rule = implode(',', $targets) . '{' . static::declarations($rules) . '}';
            if ($signature !== 'td' && isset($rules['text-overflow-mode']))
                $rule .= implode(',', $targets) . '{display:block;max-width:100%;min-width:0}';
            if ($signature === 'td' && isset($rules['text-overflow-mode']))
            {
                $content = $base . ' ' . $cell . ' :is(a,span,div,li):not([data-style-default-icon]):not(.fieldoperations):not(.fieldoperations *)';
                $rule .= $content . '{' . static::declarations(['text-overflow-mode' => $rules['text-overflow-mode']]) . ';max-width:100%;min-width:0}'
                    . $content . ':is(a,span){display:inline-block;vertical-align:middle}';
            }
            if ($signature === 'td' && isset($rules['width']))
            {
                $header = 'th[class~=' . static::quote($column) . ']';
                $boxes = [];
                foreach ([$base, '[id=' . static::quote($tableId . '_wrapper') . ']', 'table[data-realid=' . static::quote($tableId) . ']'] as $scope)
                    foreach ([$header, $cell] as $box) $boxes[] = $scope . ' ' . $box;
                $rule .= implode(',', $boxes) . '{--ib-column-width:' . $rules['width'] . '!important;box-sizing:border-box;'
                    . static::declarations(['width' => $rules['width'], 'min-width' => $rules['width'], 'max-width' => $rules['width']]) . '}';
                if ($rules['width'] !== 'auto')
                    $rule .= implode(',', array_map(fn ($box) => $box . ' > :not(.fieldoperations):not(.ib-config-header-actions)', $boxes))
                        . '{min-width:0!important;max-width:calc(' . $rules['width'] . ' - 24px)!important;box-sizing:border-box;overflow-wrap:anywhere}';
            }
            $output[] = $rule;
        }
        return implode("\n", $output);
    }
}
