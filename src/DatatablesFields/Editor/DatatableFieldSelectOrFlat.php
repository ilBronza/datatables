<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

/**
 * Select editor che si mostra solo quando il valore manca.
 * Se l'elemento ha già un valore la cella renderizza l'etichetta come testo flat,
 * altrimenti renderizza il select identico a editor.select.
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

		if(item && item[1] != null && item[1] !== '' && item[1] !== " . json_encode($this->nullValue) . ")
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
