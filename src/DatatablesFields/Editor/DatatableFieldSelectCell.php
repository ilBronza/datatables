<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;

/**
 * Select editor con valori possibili calcolati per ogni cella/riga.
 * Estende DatatableFieldSelect ma invece di usare possibleValues nell'header (uguali per tutte le righe),
 * i valori vengono calcolati per ogni elemento e passati nella cella via data-possible-values.
 *
 * Richiede possibleValuesMethod che verrà chiamato con l'elemento della riga.
 *
 * Esempio:
 * 'vehicle_id' => [
 *     'type' => 'editor.selectCell',
 *     'possibleValuesMethod' => 'getVehicleSelectPossibleValues',
 *     'refreshRow' => true,
 * ],
 */
class DatatableFieldSelectCell extends DatatableFieldSelect
{
	use EditorSingleFieldTrait;

	public function parseFieldSpecificHeaderData()
	{
		// Non impostare possibleValues nell'header: ogni cella ha i propri
	}

	public function transformValue($value)
	{
		$baseResult = parent::transformValue($value);

		// Aggiungi i valori possibili per questa cella come 4° elemento
		$possibleValues = $this->getPossibleEnumValuesArray();

		if ($this->isNullable())
			$possibleValues = array_merge([$this->nullValue => $this->nullString], $possibleValues);

		$baseResult[] = $possibleValues;

		return $baseResult;
	}

	public function getCustomColumnDefSingleResult()
	{
		if (! $this->userCanEdit())
			return $this->returnFlat();

		$classes = $this->getHtmlClassesString();

		return "

		" . $this->substituteUrlParameter() . "

		let selected = '';

		if(item) {
			let displayText = item[2];
			if(item[1] === null || item[1] === 'null') {
				let count = 0;
				if(item[3]) {
					count = Object.keys(item[3]).length;
					if(item[3].hasOwnProperty(" . json_encode($this->nullValue) . "))
						count--;
				}
				displayText = 'select (' + count + ')';
			}
			selected = '<option selected value=\"' + item[1] + '\">' + displayText + '</option>';
		}

		let possibleValuesAttr = '';
		if(item[3])
			possibleValuesAttr = ' data-possible-values=\"' + JSON.stringify(item[3]).replace(/\\\"/g, '&quot;') + '\"';

		item = '<select data-populated=\"false\"' + possibleValuesAttr + ' " . $this->getValueString() . " class=\"" . $classes . " uk-select ib-editor-select\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\">' + selected + '</select>';

		";
	}
}
