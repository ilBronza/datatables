<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

/**
 * Select editor che mostra un valore libero come testo flat.
 * Se il valore del campo (o di editorProperty) e' tra le opzioni del select, la
 * cella continua a renderizzare il select; altrimenti mostra il valore come testo
 * piano.
 *
 * Esempio:
 * 'stato' => [
 *     'type' => 'editor.selectOrFlat',
 *     'possibleValuesMethod' => 'getStatoSelectPossibleValues',
 *     'select2' => true, //opzionale, tendina con ricerca testuale
 * ],
 */
class DatatableFieldSelectOrFlat extends DatatableFieldSelect
{
	protected int $selectFallbackFlagPosition = 3;

	/**
	 * editorProperty e' il valore da mostrare in flat, non necessariamente il
	 * valore che deve essere preselezionato nel select. Quando manca, torna al
	 * campo originale e forza il rendering del select.
	 */
	public function transformValue($value)
	{
		$result = parent::transformValue($value);

		while (count($result) < $this->selectFallbackFlagPosition)
			$result[] = null;

		$result[] = false;

		$flatValue = $this->editorProperty
			? $value->{$this->editorProperty} ?? null
			: $value->{$this->name} ?? null;

		if ($flatValue !== null && $flatValue !== '' && $flatValue !== $this->nullValue)
		{
			if ($this->shouldRenderValueAsSelect($flatValue))
				$result[$this->selectFallbackFlagPosition] = true;

			return $result;
		}

		$selectedValue = $value->{$this->name} ?? $this->default;

		$result[1] = $selectedValue;
		$result[2] = $this->getInitialSelectLabel($selectedValue);
		$result[$this->selectFallbackFlagPosition] = true;

		return $result;
	}

	protected function shouldRenderValueAsSelect($value) : bool
	{
		return $this->isSelectValue($value);
	}

	/**
	 * Le opzioni possono essere una mappa semplice o raggruppate sotto "groups",
	 * come previsto da getSelectOptionLabel().
	 */
	protected function isSelectValue($value) : bool
	{
		if (! is_int($value) && ! is_string($value))
			return false;

		$possibleValues = $this->getPossibleEnumValuesArray();

		if (isset($possibleValues['groups']) && is_array($possibleValues['groups']))
		{
			foreach ($possibleValues['groups'] as $options)
				if (is_array($options) && array_key_exists($value, $options))
					return true;

			return false;
		}

		return array_key_exists($value, $possibleValues);
	}

	//il select mostra l'etichetta (item[2]), non la chiave (item[1])
	public function returnFlat()
	{
		return "
			item = '<span>' + (typeof item[1] === 'undefined' ? item[1] : item[1]) + '</span>';
		";
	}

	//cosa renderizzare quando il valore c'è
	protected function returnFilled() : string
	{
		return $this->returnFlat();
	}

	public function getCustomColumnDefSingleResult()
	{
		if(! $this->userCanEdit())
			return $this->returnFlat();

		//!= null intenzionale: intercetta sia null che undefined
		return "

		if(item && item[" . $this->selectFallbackFlagPosition . "] !== true && item[1] != null && item[1] !== '' && item[1] !== " . json_encode($this->nullValue) . ")
		{
			" . $this->returnFilled() . "
		}
		else
		{
			" . parent::getCustomColumnDefSingleResult() . "
		}

		";
	}
}
