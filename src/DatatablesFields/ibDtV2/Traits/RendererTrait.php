<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

trait RendererTrait
{
	public function getCustomColumnDef()
	{
		$display = $this->getCustomColumnDefSingleResult();
		$raw = $this->getCustomColumnDefSingleSearchResult();
		$sort = $this->getCustomColumnDefSingleSortResult();
		$export = $this->getCustomColumnDefSingleResultExport();
		$cellAttributes = $this->compileCellAttributes();

		return "{
			targets: [{$this->getIndex()}],
			{$cellAttributes}
			render: function(item, type, row, meta) {
				if (type === 'display') { {$display} }
				else if (type === 'sort') { {$sort} }
				else if (type === 'export') { {$export} }
				else { {$raw} }
				return item;
			}
		}";
	}

	protected function getCellValueExpression() : string
	{
		$position = $this->getValuePosition();
		return $position === null ? 'item' : 'item[' . $this->javascriptLiteral($position) . ']';
	}

	public function getCustomColumnDefSingleSearchResult()
	{
		return 'item = ' . $this->getCellValueExpression() . ';';
	}

	public function getCustomColumnDefSingleSortResult()
	{
		return $this->getCustomColumnDefSingleSearchResult();
	}

	public function getCustomColumnDefSingleResultExport()
	{
		return $this->getCustomColumnDefSingleSearchResult();
	}

	public function getCustomColumnDefSingleResultEditor()
	{
		return $this->getCustomColumnDefSingleSearchResult();
	}

	public function getStructuredDataIndexString() : ?string
	{
		$position = $this->getValuePosition();
		return $position === null ? null : '[' . $this->javascriptLiteral($position) . ']';
	}

	// The display body already wraps the control, including when embedded in an iterator.
	public function getEndingResultOptions()
	{
		return '';
	}
}
