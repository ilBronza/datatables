<?php

namespace IlBronza\Datatables\DatatablesFields\Dates;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\CarbonTrait;

class DatatableFieldCarbon extends DatatableField
{
    use CarbonTrait;

	/**
	 * When enabled, the cell data is [date value, validity], where validity is
	 * 0 for a past date, 1 for a future date, and null for an empty date.
	 */
	public bool $checkValidity = false;
	public null|int|string $validityPosition = 1;

    public $defaultFilterType = 'date';
    public $defaultWidth = '80px';

	public $rangeFilter = true;

    public function transformValue($value)
    {
        if(! $value)
            return $this->transformValueWithValidity(null, null);

        $date = $value->format('Y-m-d'); // QUI: prendi il giorno "di calendario" che vuoi preservare

		$date = \Carbon\Carbon::createFromFormat('Y-m-d', $date, 'UTC')
            ->startOfDay();

		return $this->transformValueWithValidity($date->timestamp, $date);
    }

	public function setParameters(array $parameters)
	{
		parent::setParameters($parameters);

		if (! $this->checkValidity)
			return;

		$this->valueAsRowClass = true;
		$this->valueAsRowClassPrefix = true;
	}

	public function getValueAsRowClassDataIndexString() : ?string
	{
		if ($this->checkValidity)
			return "[{$this->validityPosition}]";

		return parent::getValueAsRowClassDataIndexString();
	}

	public function getCustomColumnDefItemPreparation() : string
	{
		if (! $this->checkValidity)
			return '';

		return "
					if (Array.isArray(item))
						item = item[0];
		";
	}

	protected function transformValueWithValidity($transformedValue, $dateValue)
	{
		if (! $this->checkValidity)
			return $transformedValue;

		return [$transformedValue, $this->getValidityValue($dateValue)];
	}

	protected function getValidityValue($dateValue) : ?int
	{
		if (! $dateValue)
			return null;

		return $dateValue->isPast() ? 0 : 1;
	}

	public function getCompiledAsRowClassScript()
	{
		if ($this->compiledAsRowClass)
			return '
        //' . $this->name . "
        
        if(data[" . $this->getIndex() . "] != null)
            $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "compiled');
        else
            $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "notcompiled');
        ";
	}
}
