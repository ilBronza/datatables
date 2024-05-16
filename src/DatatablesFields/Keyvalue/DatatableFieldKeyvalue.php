<?php

namespace IlBronza\Datatables\DatatablesFields\Keyvalue;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\FieldPlaceholderModelTrait;
use IlBronza\Datatables\DatatablesFields\FieldTypesTraits\IconTextContentTrait;
use Illuminate\Support\Str;

class DatatableFieldKeyvalue extends DatatableField
{
	use IconTextContentTrait;
	use FieldPlaceholderModelTrait;

	public $htmlTag = 'span';

	public function transformValue($value)
	{
		if(! $value)
			return [
				null,
				$this->getNullValue()
			];

		if($this->hasStrLimit())
			return [
				$value,
				Str::limit(
					$this->getModelClass()::findCachedAttribute($value, $this->property),
					$this->getStrLimit(),
					$end = '.'
				)
			];

		return [
			$value,
			$this->getModelClass()::findCachedAttribute($value, $this->property)
		];
	}

	public function getLinkArrayItem()
	{
		return "item[1]";
	}

	// public function getUrl() : string
	// {
	// 	$placeholderModel = $this->getFieldPlaceholderModel();

	// 	return $placeholderModel->getShowUrl();
	// }

	// public function getCustomColumnDefSingleResult()
	// {
	// 	$nullableString = $this->mustShowNull() ? 'if(item)' : 'if(item[1])';

	// 	return "

	// 		" . $nullableString . "
	// 		{
	// 			" . 
	// 			// $this->replaceUrlScript() .
	// 			 "

	// 			item = '<" . $this->getHtmlTagString() . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " " . $this->getTargetHtml() . " >" . $this->getPrefix() . "" . $this->getIconHtml() . "' + " . $this->getLinkTextString() . " + '" . $this->getSuffix() . "</" . $this->getHtmlTagString() . ">';
	// 		}

	// 		else item = '';
	// 	";
	// }

	public function getDisplayCustomColumnDefSingleResult()
	{
		// $nullableString = $this->mustShowNull() ? 'if(item)' : 'if(item[1])';

		return "

		if(item[1])

			item = '<" . $this->getHtmlTag() . " data-value=\'' + item[0] + '\'" . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . " " . $this->getHtmlClassesAttributeString() . " >" . $this->getPrefix() . "" . $this->getIconHtml() . "' + " . $this->getLinkArrayItem() . " + '" . $this->getSuffix() . "</" . $this->getHtmlTagString() . ">';
		";

	}

	public function getExportCustomColumnDefSingleResult()
	{
		return "
				item = item[1];
		";

	}

	public function getCustomColumnDefSingleSearchResult()
	{
		return "
				item = item[1];
		";

	}

    public function getCustomColumnDef()
    {
        // if(! $this->getEndingResultOptions())
        //     return ;

        return "
        {
            //" . $this->getName() . "
            targets: [" . $this->getIndex() . "],
            render: function ( item, type, row, meta )
            {
                if(type == 'display')
                {
                    " . $this->getDisplayCustomColumnDefSingleResult() . "
                    " . $this->getEndingResultOptions() . "

                    return item;
                }

                if(type == 'export')
                {
                    " . $this->getExportCustomColumnDefSingleResult() . "
                    " . $this->getEndingResultOptionsEditor() . "

                    return item;
                }

                if(type == 'filter')
                {
                    " . $this->getCustomColumnDefSingleSearchResult() . "

                    return item;
                }

                if(type == 'sort')
                {
                    " . $this->getCustomColumnDefSingleSearchResult() . "

                    return item;
                }

                return item;
            }
        }";
    }}
