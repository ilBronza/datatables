<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\EditorSingleFieldTrait;
use LogicException;

/**
 * Select editor con valori possibili calcolati per ogni cella/riga.
 * Estende DatatableFieldSelect ma invece di usare possibleValues nell'header (uguali per tutte le righe),
 * i valori vengono calcolati per ogni elemento e passati nella cella via data-possible-values.
 *
 * Richiede possibleValuesRowLabelMethod, chiamato sull'elemento della riga per
 * fornire il testo del valore iniziale. possibleValuesMethod resta il fallback
 * per i valori precalcolati quando possibleValuesRowRoute non è configurata.
 *
 * Esempio:
 * 'vehicle_id' => [
 *     'type' => 'editor.selectCell',
 *     'possibleValuesRowLabelMethod' => 'getVehicleSelectLabel',
 *     'possibleValuesMethod' => 'getVehicleSelectPossibleValues',
 *     'refreshRow' => true,
 * ],
 */
class DatatableFieldSelectCell extends DatatableFieldSelect
{
	use EditorSingleFieldTrait;

	public ?string $possibleValuesRowRoute = null;
	public ?string $possibleValuesRowLabelMethod = null;

	protected function hasPossibleValuesRowRoute() : bool
	{
		return ! ! $this->possibleValuesRowRoute;
	}

	protected function getPossibleValuesRowRouteDataAttributes() : string
	{
		if (! $this->hasPossibleValuesRowRoute())
			return '';

		return ' data-possible-values-row-route="' . e($this->possibleValuesRowRoute) . '"'
			. ' data-possible-values-row-route-placeholder="'
			. e(config('datatables.replace_model_id_string')) . '"';
	}

	protected function getPossibleValuesRowIdDataAttribute() : string
	{
		if (! $this->hasPossibleValuesRowRoute())
			return '';

		return " data-row-id=\"' + item[0] + '\"";
	}

	protected function getPossibleValuesRowRouteInlineDataAttributes() : array
	{
		if (! $this->hasPossibleValuesRowRoute())
			return [];

		return [
			'possible-values-row-route' => $this->possibleValuesRowRoute,
			'possible-values-row-route-placeholder' => config('datatables.replace_model_id_string'),
			'row-id' => $this->element?->getKey(),
		];
	}

	protected function getInitialSelectLabel($selectedValue) : string
	{
		$method = $this->possibleValuesRowLabelMethod;

		if (! $method || ! method_exists($this->element, $method))
			throw new LogicException(sprintf(
				'editor.selectCell "%s" richiede "%s" valido sul model della riga',
				$this->name,
				$method
			));

		if ($selectedValue === null || $selectedValue === $this->nullValue)
			return $this->nullString;

		return (string) $this->element->{$method}();
	}

	public function parseFieldSpecificHeaderData()
	{
		// Non impostare possibleValues nell'header: ogni cella ha i propri
	}

	public function transformValue($value)
	{
		$baseResult = parent::transformValue($value);

		if ($this->hasPossibleValuesRowRoute())
		{
			$baseResult[] = null;

			return $baseResult;
		}

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

		$classes = $this->getHtmlClassesString() . $this->getSelect2ClassString();
		$selectLabel = json_encode(config('datatables.labels.select', 'select'));

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
				displayText = " . $selectLabel . " + ' (' + count + ')';
			}
			selected = '<option selected value=\"' + item[1] + '\">' + displayText + '</option>';
		}

		let possibleValuesAttr = '';
		if(item[3])
			possibleValuesAttr = ' data-possible-values=\"' + JSON.stringify(item[3]).replace(/\\\"/g, '&quot;') + '\"';

		item = '<select data-populated=\"false\"" . $this->getCustomValueModeDataAttribute() . $this->getPossibleValuesRowRouteDataAttributes() . $this->getPossibleValuesRowIdDataAttribute() . "' + possibleValuesAttr + ' " . $this->getValueString() . " class=\"" . $classes . " uk-select ib-editor-select\" data-url=\"' + url + '\" data-field=\"{$this->parameter}\">' + selected + '</select>';

		";
	}
}
