<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

trait EditorSaveSettingsTrait
{
	public bool $spin = true;
	public array $fieldExtraData = [];
	public ?bool $saveButton = null;
	public ?bool $nullable = true;
	public ?bool $refreshRow = null;
	public ?bool $reloadTable = null;

	public function editorHasSaveButton() : bool
	{
		return config('datatables.editor.saveButton', false);
	}

	public function addExtradata(string $key, mixed $value) : void { $this->fieldExtraData[$key] = $value; }
	public function getJsonAjaxExtraData() : string { return $this->javascriptLiteral($this->fieldExtraData); }

	public function hasSaveButton() : bool
	{
		if (! is_null($this->saveButton))
			return $this->saveButton;

		return $this->editorHasSaveButton();
	}

	public function setReloadTableExtraData(?bool $reloadTable = null)
	{
		if (is_null($reloadTable))
			$reloadTable = config('datatables.editor.reloadTable', false);

		$this->addExtradata('reloadTable', $reloadTable);
	}

	public function setRefreshRowExtraData(?bool $refreshRow = null)
	{
		if ($this->getReloadTable())
			return $this->addExtradata('refreshRow', false);

		if (is_null($refreshRow))
			$refreshRow = config('datatables.editor.refreshRow', false);

		$this->addExtradata('refreshRow', $refreshRow);
	}

	public function getReloadTable() : bool
	{
		if (! is_null($this->reloadTable))
			return $this->reloadTable;

		return config('datatables.editor.reloadTable', false);
	}

	public function isNullable() : bool
	{
		return ! ! $this->nullable;
	}

	public function hasSpinner()
	{
		return $this->spin;
	}
}
