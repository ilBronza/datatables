<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldInlineEdit extends DatatableFieldAjax
{
	/**
	 * Reload the DataTable after a successful inline batch update.
	 *
	 * This follows the same field-extra-data contract used by editor fields.
	 */
	public ?bool $reloadTable = null;

	/**
	 * When supplied, this URL takes precedence over the model method.
	 */
	public ?string $url = null;

	public $faIcon = 'pen-to-square';
	public $method = 'getInlineEditUrl';
	public bool $sendAjaxPayload = false;

	public $dataAttributes = [
		'type' => 'GET',
		'inline-edit' => true,
		'response-data-type' => 'html',
	];

	public function __construct(string $name, array $parameters = [], ?int $index = null, ?DatatableField $parent = null, ?Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);

		$this->setReloadTableExtraData($this->reloadTable);
	}

	public function setReloadTableExtraData(?bool $reloadTable = null) : void
	{
		if (is_null($reloadTable))
			$reloadTable = config('datatables.editor.reloadTable', false);

		$this->addExtradata('reloadTable', $reloadTable);
	}

	public function transformValue($value)
	{
		if (! $value)
			return null;

		if ($this->url)
			return $this->url;

		return parent::transformValue($value);
	}
}
