<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use IlBronza\Datatables\Support\DecimalValue;
use InvalidArgumentException;

trait NumericOptionsTrait
{
	public ?int $decimals = null;
	public ?int $maxDecimals = null;
	public bool $fixedDecimals = true;
	public string $decimalSeparator = ',';
	public string $thousandsSeparator = '.';
	public int|float|string|null $min = null;
	public int|float|string|null $max = null;
	public int|float|string|null $step = null;
	public ?string $currency = null;

	public function getNumericOptions() : array
	{
		foreach (['decimals', 'maxDecimals'] as $name)
			if ($this->{$name} !== null && ($this->{$name} < 0 || $this->{$name} > 100))
				throw new InvalidArgumentException($name . ' must be null or between 0 and 100.');
		if (! in_array($this->decimalSeparator, ['.', ','], true))
			throw new InvalidArgumentException('decimalSeparator must be . or ,.');
		if (! in_array($this->thousandsSeparator, ['', '.', ',', ' ', "\u{00A0}", "\u{202F}", "'"], true)
			|| $this->decimalSeparator === $this->thousandsSeparator)
			throw new InvalidArgumentException('Invalid or conflicting thousandsSeparator.');
		if ($this->currency !== null && ! preg_match('/^[A-Z]{3}$/D', $this->currency))
			throw new InvalidArgumentException('currency must be an uppercase three-letter code, e.g. EUR.');
		$options = [
			'decimals' => $this->decimals, 'fixedDecimals' => $this->fixedDecimals, 'maxDecimals' => $this->maxDecimals,
			'decimalSeparator' => $this->decimalSeparator, 'thousandsSeparator' => $this->thousandsSeparator,
			'min' => DecimalValue::canonical($this->min), 'max' => DecimalValue::canonical($this->max),
			'step' => DecimalValue::canonical($this->step), 'currency' => $this->currency, 'nullable' => $this->isNullable(),
		];
		if ($options['min'] !== null && $options['max'] !== null && DecimalValue::compare($options['min'], $options['max']) > 0)
			throw new InvalidArgumentException('min cannot exceed max.');
		if ($options['step'] !== null && DecimalValue::compare($options['step'], '0') <= 0)
			throw new InvalidArgumentException('step must be greater than zero.');
		return $options;
	}
}
