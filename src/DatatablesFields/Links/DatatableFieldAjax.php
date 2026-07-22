<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

class DatatableFieldAjax extends DatatableFieldLink
{
	/**
	 * Controls whether the table-header and clicked-element data attributes are
	 * appended to the Ajax request.
	 */
	public bool $sendAjaxPayload = true;

	public $dataAttributes = [
		'type' => 'POST'
	];

    public $actionHtmlClass = 'ib-cell-ajax-button';

	public function getHtmlClassesAttributeString()
	{
		$this->addHtmlClass(
			$this->actionHtmlClass
		);

		return parent::getHtmlClassesAttributeString();
	}

	public function getFieldSpecificData() : array
	{
		return array_merge(
			parent::getFieldSpecificData(),
			[
				'send-ajax-payload' => $this->sendAjaxPayload ? 'true' : 'false',
			]
		);
	}
}
