<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use IlBronza\Datatables\Contracts\DatatableFieldContract;
use IlBronza\Datatables\DatatableFieldsGroup;
use IlBronza\Datatables\Datatables;
use Illuminate\Support\Str;

trait FieldIdentityTrait
{
	public string $name;
	public string $id;
	public int $index;
	public ?string $type = null;
	public ?Datatables $table;
	public ?DatatableFieldsGroup $fieldsGroup = null;
	public ?DatatableFieldContract $parent;
	public bool $rowId = false;

	public function getName() : ?string { return $this->name; }
	public function getFieldName() : string { return $this->name; }
	public function getId() : string { return $this->id; }
	public function getIndex() : int { return $this->index; }
	public function setIndex(?int $index = null) : void { $this->index = $index ?? 0; }
	public function getType() : ?string { return $this->type; }
	public function getTable() : ?Datatables { return $this->table; }
	public function setFieldsGroup(DatatableFieldsGroup $fieldsGroup) : void { $this->fieldsGroup = $fieldsGroup; }
	public function hasParent() : bool { return $this->parent !== null; }
	public function isRowId() : bool { return $this->rowId; }
	public function getCamelName() : string { return Str::camel(str_replace('.', ' ', $this->name)); }
}
