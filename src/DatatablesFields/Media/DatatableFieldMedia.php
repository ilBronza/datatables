<?php

namespace IlBronza\Datatables\DatatablesFields\Media;

use IlBronza\Datatables\DatatablesFields\DatatableField;

class DatatableFieldMedia extends DatatableField
{
	protected $collection = null;
	protected $conversionName = 'table';
	protected bool $lightbox = true;

	public function getCollection() : ? string
	{
		return $this->collection;
	}

	public function hasLightbox() : bool
	{
		return $this->lightbox;
	}

	public function getConversionName() : string
	{
		return $this->conversionName;
	}

    public function transformValue($value)
    {
	    $media = $value->getMainMedia(
			$this->getCollection()
	    );

	    if($this->hasLightbox())
		    return [
			    $media?->getUrl(),
				$media?->getUrl(
					$this->getConversionName()
				)
		    ];

	    return $media?->getUrl(
		    $this->getConversionName()
	    );
    }

	public function getCustomColumnDefSingleResult()
	{
		if($this->hasLightbox())
			return "if(item[0] === null) item = ''; else item = '<div uk-lightbox><a href=' + item[0] + ' data-lightbox=\"image-1\"><img src=' + item[1] + ' /></a></div>';";

		return "if(item === null) item = ''; else item = '<img src=' + item + ' />';";
	}
}