<?php

namespace IlBronza\Datatables\DatatablesFields\Clients;

use App\Color;
use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldColor extends DatatableField
{
    // public $parameter = 'color_id';
    public $method = 'getFirstColorId';
	public $width = '25px';

    public function transformValue($value)
    {
        if((! $value)||(! $value->{$this->method}())||($value->{$this->method}() == 1))
            return ;

        $color = Color::getCachedColor($value->{$this->method}());

        return [
            optional($color)->code,
            optional($color)->color
        ];
    }

    public function getCustomColumnDefSingleResult()
    {
        return "

            if(item)
            {
				item = '<strong style=\"color:' + item[1] + '; \" >' + item[0] + '</strong>';
            }

            else item = ''
		";
    }
}
