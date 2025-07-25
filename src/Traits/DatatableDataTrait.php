<?php

namespace IlBronza\Datatables\Traits;

use Exception;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Throwable;

use function dd;
use function get_class_methods;

trait DatatableDataTrait
{
	public function prepareCachedData()
	{
		return cache()->remember(
			$this->getCachedTableKey(), 213300, function ()
		{
			return $this->calculateData();
		}
		);
	}

	public function setData(array $data = null)
	{
		$this->data = $data ?? $this->calculateData();
	}

	public function getData()
	{
		return $this->data;
	}

	public function getTableCellDataValue(string $fieldName, $element)
	{
		$properties = explode(".", $fieldName);

		do
		{
			$property = array_shift($properties);

			if (strpos($property, 'mySelf') === false)
				$element = $element->$property ?? false;
		} while (count($properties));

		return $element;
	}

	//UNA VOLTA ERA
	// public function getCellDataValue(string $fieldName, $element)

	public function calculateData()
	{
		// if($this->hasSummary())
		//     return $this->calculateDataWithSummary();

		return $this->_calculateData();
	}

	public function calculateDataWithSummary()
	{
		$data = [];

		foreach ($this->elements as $element)
		{
			$row = [];

			foreach ($this->getFields() as $field)
				$row[] = $this->getCellDataWithSummary($field, $element);

			$data[] = $row;
		}

		$summaryRow = [];

		foreach ($this->getFields() as $field)
			$summaryRow[] = $field->getSummaryResult();

		$data[] = $summaryRow;

		return $data;
	}

	public function _calculateData()
	{
		$data = [];

		foreach ($this->elements as $element)
		{
			$row = [];

			foreach ($this->getFields() as $field)
				$row[] = $this->getCellData($field, $element);

			$data[] = $row;
		}

		return $data;
	}

	public function getCachedTableKey()
	{
		if ($this->cachedTableKey)
			return $this->cachedTableKey;

		$this->setCachedTableKey();

		return $this->cachedTableKey;
	}

	public function hasDoublerFields()
	{
		foreach ($this->fields as $field)
			if ($field->hasDoubler())
				return true;

		return false;
	}

	private function getCellData(DatatableField $field, Model $element)
	{
		if($overridingMethod = $field->getOverridingValueMethod())
		{
			$value = $field->getFieldCellDataValue($field->name, $element);

				try
				{
					return $field->transformValue(
						$value->{$overridingMethod}($field->staticVariableValue)
					);
				}
				catch(Throwable $e)
				{
					Log::critical($e->getMessage());
					return null;
				}
		}

		try
		{
			if ($field->requireElement())
				return $field->transformValue($element);

			$value = $field->getFieldCellDataValue($field->name, $element);

			// $value = $this->getCellDataValue($field->name, $element);

			return $field->transformValue($value);
		}
		catch (Exception $e)
		{
			return $this->handleError($e);
		}
	}

	private function getCellDataWithSummary(DatatableField $field, Model $element)
	{
		$value = $this->getTableCellDataValue($field->name, $element);

		if ($field->requireElement())
			return $field->transformValueWithSummary($element);

		return $field->transformValueWithSummary($value);
	}

	private function setCachedTableKey()
	{
		$this->cachedTableKey = Str::slug($this->getName() . request()?->route()?->getName() . (\Auth::id() ?? Str::random()), '');
	}
}