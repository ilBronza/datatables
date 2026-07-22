<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldPrice extends DatatableFieldNumeric
{
	public ? string $textAlign = 'right';
	public $digits = 2;

	public ? bool $cleave = false;

	public string $decimalSeparator = ',';
	public string $thousandsSeparator = '.';

	public $htmlClasses = ['dtprice', 'ib-dt-price-editor'];
	public $fieldType = 'text';

	public function getFieldSpecificData() : array
	{
		return array_merge(parent::getFieldSpecificData(), [
			'dt-price-decimals' => $this->digits,
			'dt-price-decimal-separator' => $this->decimalSeparator,
			'dt-price-thousands-separator' => $this->thousandsSeparator,
		]);
	}

	public function getValueString()
	{
		$decimalSeparator = json_encode($this->decimalSeparator);
		$thousandsSeparator = json_encode($this->thousandsSeparator);

		return " inputmode=\"decimal\"  data-originalvalue=\"' + (function(v){ if(v === null || v === '') return ''; var n = parseFloat(v); if(!isNaN(n) && n === 0) return ''; return n.toFixed({$this->digits}); })(item[1]) + '\" value=\"' + (function(v){ if(!v) return ''; var n = parseFloat(v); if(isNaN(n)) return v; var pieces = Math.abs(n).toFixed({$this->digits}).split('.'); var integer = pieces[0].replace(/\\B(?=(\\d{3})+(?!\\d))/g, {$thousandsSeparator}); return (n < 0 ? '-' : '') + integer + {$decimalSeparator} + pieces[1]; })(item[1]) + '\" ";
	}
}
