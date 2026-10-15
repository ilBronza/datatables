<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

/** Defaults for optional features requested by the existing table views. */
trait TableIntegrationTrait
{
	public function isEditor() : bool { return false; }
	public function isBulkEditable() : bool { return false; }
	public function requiresSelectRowCheckboxes() : bool { return false; }
	public function getSaveSettings() : array { return []; }
	public function getRenderAsType() : ?string { return null; }
	public function getInlineFieldType() : string { return 'ibDtV2.editor.text'; }
	public function getCreatedRowScripts() : string { return ''; }
	public function hasDoubler() : bool { return false; }
	public function hasRangeFilter() : bool { return false; }
	public function hasFieldOperations() : bool { return false; }
	public function getFilteredTable() : ?string { return null; }
	public function getFetcherHeaderIconString() : ?string { return null; }
	public function getSummaryType() : ?string { return null; }
	public function getSummaryDataAttributes() : array { return []; }
	public function getSummaryResult() { return null; }
	public function transformValueWithSummary($value) { return $this->transformValue($value); }
	public function getFormId() : ?string { return $this->table?->getForm()?->getId(); }
}
