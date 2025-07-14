<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldJson extends DatatableField
{
    public $beautified = false;

    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        if(! $this->beautified)
            return json_encode($value);

        if(! is_array($value))
            $value = json_decode($value, true);

        $prettyJson = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Escape per HTML
        $escaped = htmlspecialchars($prettyJson);

        // Inseriscilo in una <pre> per mantenere la formattazione
        return "<pre style='max-height:300px; overflow:auto; font-size:11px; margin:0'>" . $escaped . "</pre>";
    }
}