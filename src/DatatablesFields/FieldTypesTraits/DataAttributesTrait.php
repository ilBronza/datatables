<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;


trait DataAttributesTrait
{
	public function getUrlData() : array
	{
        if(empty($this->ajax))
		  return [];

        return [
            'url' => "' + url + '"
        ];
	}

	public function getFieldSpecificData() : array
	{
		return [];
	}

	public function getDataAttributes() : array
	{
		return array_merge(
			$this->dataAttributes ?? [],
			$this->data ?? [],
			$this->getFieldSpecificData(),
			$this->getUrlData()
		);
	}

	public function getHtmlDataAttributesString() : string
	{
		$result = [];

		foreach($this->getDataAttributes() as $data => $value)
			$result[] = " data-" . $data . "=\"" . $value . "\" ";

		return implode(" ", $result);
	}    
}