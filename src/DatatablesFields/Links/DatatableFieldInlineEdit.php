<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldInlineEdit extends DatatableFieldAjax
{
	/**
	 * When supplied, this URL takes precedence over the model method.
	 */
	public ?string $url = null;

	public $faIcon = 'pen-to-square';
	public $method = 'getInlineEditUrl';

	public $dataAttributes = [
		'type' => 'GET',
		'inline-edit' => true,
		'response-data-type' => 'html',
	];

	public function transformValue($value)
	{
		if (! $value)
			return null;

		if ($this->url)
			return $this->url;

		return parent::transformValue($value);
	}
}
