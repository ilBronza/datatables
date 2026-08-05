<?php

namespace IlBronza\Datatables\DatatablesFields\Html;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;

/**
 * Cella che apre un modale UIkit al click, mostrando il contenuto del record.
 *
 * 'popup' => [
 *     'fieldType' => 'html.modal',
 *     'modalTitle' => 'Dettagli',
 *     'faIcon' => 'circle-info',
 *     'triggerText' => 'Apri'
 * ]
 */
class DatatableFieldModal extends DatatableField
{
	use IconTextContentTrait;

	public $width = '4em';

	public ? string $modalTitle = null;
	public ? string $triggerText = null;

	public string $triggerClasses = 'uk-button uk-button-small uk-button-default';
	public string $dialogClasses = 'uk-modal-dialog uk-modal-body';

	public bool $modalClose = true;

	public function getModalTitleHtml() : string
	{
		if(! $this->modalTitle)
			return '';

		return '<h2 class="uk-modal-title">' . __($this->modalTitle) . '</h2>';
	}

	public function getModalCloseHtml() : string
	{
		if(! $this->modalClose)
			return '';

		return '<button class="uk-modal-close-default" type="button" uk-close></button>';
	}

	public function getTriggerTextHtml() : string
	{
		if(! $this->triggerText)
			return '';

		return ' ' . __($this->triggerText);
	}

	public function getTriggerHtml() : string
	{
		return $this->getIconHtml() . $this->getTriggerTextHtml();
	}

	public function getCustomColumnDefSingleResult()
	{
		return "

			if(item)
			{
				let modalId = 'ibdtmodal' + Math.random().toString(36).substring(2);

				let modalHtml = '<div id=\"' + modalId + '\" uk-modal><div class=\"" . $this->dialogClasses . "\">" .
			$this->getModalCloseHtml() . $this->getModalTitleHtml() . "<div class=\"ib-dt-modal-content\">' + item + '</div></div></div>';

				item = modalHtml + '<a href=\"#' + modalId + '\" uk-toggle class=\"" . $this->triggerClasses . "\">" . $this->getTriggerHtml() . "</a>';
			}

			else item = '';
		";
	}
}
