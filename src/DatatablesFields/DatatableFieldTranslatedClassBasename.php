<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldTranslatedClassBasename extends DatatableField
{
    public function transformValue($value)
    {
    	if(! $value)
    		return ;

        try
        {
            return $value->getTranslatedClassname();
        }
        catch(\Exception $e)
        {
            return class_basename($value);            
        }
    }
}