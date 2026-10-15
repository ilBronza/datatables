<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Editor;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\Contracts\DatatableFieldContract;
use IlBronza\Datatables\DatatablesFields\ibDtV2\DatatableField;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\DisabledStateTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\EditorBindingTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\EditorPermissionsTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\EditorRoutesTrait;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Traits\EditorSaveSettingsTrait;

abstract class DatatableFieldEditor extends DatatableField
{
	use EditorBindingTrait;
	use DisabledStateTrait;
	use EditorPermissionsTrait;
	use EditorRoutesTrait;
	use EditorSaveSettingsTrait;

	public function requiresSelectRowCheckboxes() : bool { return true; }

	public function __construct(string $name, array $parameters = [], ?int $index = null, ?DatatableFieldContract $parent = null, ?Datatables $table = null)
	{
		parent::__construct($name, $parameters, $index, $parent, $table);
		$this->initializeEditorBinding();
	}
}
