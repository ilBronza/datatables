<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;

class DatatableFieldSave extends DatatableField
{
	use IconTextContentTrait;
	public $faIcon = 'link';

	public ? string $forcedStandardName = 'save';

	public ? string $translationPrefix = 'datatables::fields';

	public $width = '2em';

	public function getCustomColumnDefSingleResult()
	{
		return "
				item = '<span class=\"ib-editor-save fas fa-save\"></span>';
		";
	}

//	public $icon = 'trash';
//	public $confirmMessage = 'datatables::messages.areYouSureToDeleteThisObject';
//	public $textParameter = false;
//	public $dataAttributes = [
//		'type' => 'DELETE'
//	];

	public function transformValue($value)
	{
		return null;
	}


}
