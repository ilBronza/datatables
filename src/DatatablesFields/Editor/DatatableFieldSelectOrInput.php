<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

/**
 * Come editor.selectOrFlat, ma quando il valore c'è renderizza un input di testo
 * invece del testo flat. Senza valore renderizza il select identico a editor.select.
 *
 * L'input non mostra item[1] (la chiave del select) ma item[3], aggiunto qui in transformValue.
 *
 * Esempio:
 * 'codice' => [
 *     'type' => 'editor.selectOrInput',
 *     'possibleValuesMethod' => 'getCodiceSelectPossibleValues',
 * ],
 */
class DatatableFieldSelectOrInput extends DatatableFieldSelectOrFlat
{
	public ? string $inputFieldName = null;

	public function getInputFieldName()
	{
		if($this->inputFieldName)
			return $this->inputFieldName;

		return "raw_entry_{$this->name}";
	}

	public function transformValue($value)
	{
		$result = parent::transformValue($value);

		$result[] = $value->{$this->getInputFieldName()};

		return $result;
	}

	//stessi data attributes del select ma con field sostituito, altrimenti data-field esce doppio
	protected function getInputDataAttributesString() : string
	{
		$attributes = $this->getDataAttributes();

		$attributes['field'] = $this->getInputFieldName();

		$result = [];

		foreach($attributes as $data => $value)
			$result[] = " data-" . $data . "=\"" . $value . "\" ";

		return implode(" ", $result);
	}

	//value e data-originalvalue vengono da item[3], data-field da getInputFieldName()
	protected function returnFilled() : string
	{
		$classes = $this->getHtmlClassesString();

		return "

			" . $this->substituteUrlParameter() . "

			item = '<input " . $this->getInputDataAttributesString() . " data-originalvalue=\"' + item[3] + '\" value=\"' + item[3] + '\" " . $this->getTypeString() . " class=\"" . $classes . " uk-input ib-editor-text\" />';

		";
	}
}
