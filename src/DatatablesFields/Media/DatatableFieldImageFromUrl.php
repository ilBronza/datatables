<?php

namespace IlBronza\Datatables\DatatablesFields\Media;

use IlBronza\Datatables\DatatablesFields\DatatableField;

/**
 * Rende un'immagine da una stringa URL. Usare {@see $imageWidth} / {@see $imageHeight} per il CSS;
 * la chiave `width` nei parametri colonna resta per la larghezza cella Datatables.
 *
 * Tipo colonna: <code>media.imageFromUrl</code>
 */
class DatatableFieldImageFromUrl extends DatatableField
{
	public string $imageWidth = '80px';

	public ?string $imageHeight = null;

	public function transformValue($value)
	{
		return $value;
	}

	public function getCustomColumnDefSingleResult()
	{
		$style = 'width:' . $this->imageWidth . ';object-fit:cover;';
		if ($this->imageHeight !== null)
			$style .= 'height:' . $this->imageHeight . ';';

		$styleAttr = htmlspecialchars($style, ENT_COMPAT, 'UTF-8');

		return "if(item === null || item === '') item = ''; else item = '<img src=\"' + item + '\" alt=\"\" style=\"" . $styleAttr . "\" class=\"ib-dt-image-from-url\" />';";
	}

	public function getCustomColumnDefSingleResultExport()
	{
		return "if(item === null || item === '') item = ''; else item = item;";
	}
}
