<?php

namespace IlBronza\Datatables\Traits;

use function config;
use function is_null;

trait DatatableButtonsTrait
{
	public function hasDoublerButton() : bool
	{
		if (! $this->hasDoublerFields())
			return false;

		return config('datatables.defaultButtons.doubler', true);
	}

	public function hasSelectFilteredButton() : bool
	{
		if (! $this->hasSelectRowCheckboxes())
			return false;

		return config('datatables.defaultButtons.selectFiltered', true);
	}

	public function hasSearchButton() : bool
	{
		return config('datatables.defaultButtons.search', true);
	}

	public function hasReloadButton() : bool
	{
		if (isset($this->reloadButton))
			return $this->reloadButton;

		return config('datatables.defaultButtons.reload', true);
	}

	public function hasCopyButton() : bool
	{
		if (! is_null($this->copyButton))
			return $this->copyButton;

		return config('datatables.defaultButtons.copy', true);
	}

	public function hasCsvButton() : bool
	{
		if (! is_null($this->csvButton))
			return $this->csvButton;

		return config('datatables.defaultButtons.csv', true);
	}

	public function setButtons(array $buttons)
	{
		return $this->buttons = $buttons;
	}

	public function removeButton($removingButton)
	{
		foreach ($this->buttons as $key => $button)
			if ($button == $removingButton)
				unset($this->buttons[$key]);
	}

	public function addButton($addingButton)
	{
		foreach ($this->buttons as $key => $button)
			if ($button == $addingButton)
				return false;

		$addingButton->setTableId($this->getId());

		$this->buttons[] = $addingButton;
	}

	public function getButtons()
	{
		return $this->buttons;
	}

	private function initializeButtons()
	{
		$this->buttons = [];
	}
}