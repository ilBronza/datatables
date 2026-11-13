<?php

namespace IlBronza\Datatables\Support;

use InvalidArgumentException;

/** Decimal strings cross the JSON boundary without a float conversion. */
class DecimalValue
{
	public static function canonical(mixed $value) : ?string
	{
		if ($value === null || $value === '')
			return null;
		if (is_float($value))
			$value = json_encode($value, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
		if (! is_string($value) && ! is_int($value))
			throw new InvalidArgumentException('Expected a decimal string or number.');
		$source = trim((string) $value);
		if (! preg_match('/^([+-]?)(\d*)(?:\.(\d*))?(?:[eE]([+-]?\d+))?$/D', $source, $parts)
			|| (($parts[2] ?? '') === '' && ($parts[3] ?? '') === ''))
			throw new InvalidArgumentException('Invalid decimal value: ' . $source);
		$exponent = $parts[4] ?? '0';
		if (strlen(ltrim($exponent, '+-0')) > 5 || abs((int) $exponent) > 10000)
			throw new InvalidArgumentException('Decimal exponent is too large.');
		$digits = $parts[2] . ($parts[3] ?? '');
		$position = strlen($parts[2]) + (int) $exponent;
		if ($position <= 0)
			$result = '0.' . str_repeat('0', -$position) . $digits;
		elseif ($position >= strlen($digits))
			$result = $digits . str_repeat('0', $position - strlen($digits));
		else
			$result = substr($digits, 0, $position) . '.' . substr($digits, $position);
		[$integer, $fraction] = array_pad(explode('.', $result, 2), 2, '');
		$integer = ltrim($integer, '0') ?: '0';
		$sign = $parts[1] === '-' && trim($integer . $fraction, '0') !== '' ? '-' : '';
		return $sign . $integer . ($fraction !== '' ? '.' . $fraction : '');
	}

	public static function compare(string $left, string $right) : int
	{
		$negativeLeft = str_starts_with($left, '-');
		$negativeRight = str_starts_with($right, '-');
		if ($negativeLeft !== $negativeRight)
			return $negativeLeft ? -1 : 1;
		[$li, $lf] = array_pad(explode('.', ltrim($left, '-'), 2), 2, '');
		[$ri, $rf] = array_pad(explode('.', ltrim($right, '-'), 2), 2, '');
		$length = max(strlen($lf), strlen($rf));
		$result = (strlen($li) <=> strlen($ri)) ?: strcmp($li, $ri)
			?: strcmp(str_pad($lf, $length, '0'), str_pad($rf, $length, '0'));
		return ($negativeLeft ? -1 : 1) * ($result <=> 0);
	}
}
