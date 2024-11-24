<?php

namespace IlBronza\Datatables\DatatablesFields\Notifications;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldAlerts extends DatatableField
{
	public $width = '35px';

	public function transformValue($value)
	{
		if (! $value)
			return $value;

		return $value;
	}

	public function getCustomColumnDefSingleResult()
	{
		return "

            if((item)&&(item.length > 0))
            {
            	let rand = 'modal' + Math.random();
            	
            	rand = rand.replace(/\./g, \"\");
            
            	let tooltipText = '<div id=\"' + rand + '\" uk-modal><div class=\"uk-modal-dialog uk-modal-body\"><h2 class=\"uk-modal-title\">Problemi</h2><ol>';
            	
            	item.forEach(function(item, index)
            	{
            		tooltipText += '<li>' + item.label + ' <a class=\"uk-button uk-button-primary uk-button-small\" href=\'' + item.href + '\'>Risolvi</a></li>';
            	});

            	tooltipText += '</ol></div></div>';

				item = tooltipText + '<a href=\"#' + rand + '\" uk-toggle class=\"ib-alertbutton uk-button uk-button-small uk-button-danger\"><i class=\"fa-solid fa-exclamation-triangle\"></i>' + item.length + '</a>';
			}

            else item = '';
		";
	}
}