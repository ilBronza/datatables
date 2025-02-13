<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use function dd;

trait DatatablesFieldsColumnDefsTrait
{
	public function getColumnDefs()
	{
		$this->setClassnameColumnDef();

		return $this->columnDefs;
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
                    " . $this->getCustomColumnDefSingleResult() . "
                    " . $this->getEndingResultOptions() . "

                    return item;
                }

                if(type == 'export')
                {
                    " . $this->getCustomColumnDefSingleResultEditor() . "
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
                    " . $this->getCustomColumnDefSingleSortResult() . "

                    return item;
                }

                return item;
            }
        }";
	}

	public function getCustomColumnDefSingleResult()
	{
		if ($this->getParentDataIndexString() || ($this->getHtmlDataAttributesString()) || $this->getHtmlClassesAttributeString())
			return "
                if(item === null)
                    item = '';
                else 
                {
                    //manzissimo;
                    item = '<span " . $this->getParentDataIndexString() . $this->getHtmlDataAttributesString() . $this->getHtmlClassesAttributeString() . " >' + " . $this->getItemString() . " + '</span>';
                }
            ";

		return "
            if(item === null)
                item = '';
        ";
		// $result .= $this->getEndingResultOptions();
	}

	public function getItemString()
	{
		if ($this->requiresKey())
			return 'item[1]';

		return 'item';
	}

	public function getEndingResultOptions()
	{
		$result = [];

		if ($this->getSuffix())
			$result[] = "
            if(item)
                item = item + '" . $this->getSuffix() . "';
        ";

		if ($this->getPrefix())
			$result[] = "
            if(item)
                item = '" . $this->getPrefix() . "' + item;
        ";

		return implode(" ", $result);
	}

	public function getSuffix()
	{
		return $this->suffix ?? false;
	}

	public function getPrefix()
	{
		return $this->prefix ?? false;
	}

	public function getCustomColumnDefSingleResultEditor()
	{
		return $this->getCustomColumnDefSingleResult();
	}

	public function getEndingResultOptionsEditor()
	{
		return $this->getEndingResultOptions();
	}

	public function getCustomColumnDefSingleSearchResult()
	{
		return "
            return item;
        ";
	}

	public function getCustomColumnDefSingleSortResult()
	{
		return "
            return item;
        ";
	}

	public function getColumnDefSingleResult()
	{
		return $this->getEndingResultOptions();
	}

	public function getCreatedRowScripts()
	{
		$result = [];

		if ($script = $this->getValueAsRowClassScript())
			$result[] = $script;

		if ($script = $this->getCompiledAsRowClassScript())
			$result[] = $script;

		return implode(" ", $result);
	}

	public function getValueAsRowClassScript()
	{
		if (! $this->valueAsRowClass)
			return null;

		/****
		 *
		 *
		 *  COME FARE QUA
		 *
		 * getStructuredDataIndexString dovrebbe ritornare vuoto se il campo ha il valore dentro al dato, tipo un 'flat'
		 * getStructuredDataIndexString dovrebbe ritornare [1] se il campo è tipo un editor toh
		 * getStructuredDataIndexString dovrebbe ritornare ['nomeChiave'] se il campo ha i valori codificati con chiavi diverse da 0 e 1
		 *
		 *
		 */

			return "
        //" . $this->name . "
        window.valueAsClass = data[" . $this->getIndex() . "]" . $this->getStructuredDataIndexString() . ";

        if(typeof window.valueAsClass !== 'undefined')
        {
            if(typeof window.valueAsClass !== 'string')
                window.valueAsClass = JSON.stringify(window.valueAsClass);

            $(row).addClass('" . $this->getValueAsRowClassPrefix() . "' + window.valueAsClass.replace(/[^a-zA-Z0-9 ]/g, ' '));
        }

        ";
	}

	public function getCompiledAsRowClassScript()
	{
		if ($this->compiledAsRowClass)
			return "
        //" . $this->name . "

        " . $this->getCompiledAsRowClassConditionScript() . "
        
        if(window.compiledAsClass)
            $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "compiled');
        else
            $(row).addClass('" . $this->getCompiledAsRowClassPrefix() . "notcompiled');
        ";
	}

	public function getCompiledAsRowClassConditionScript()
	{
		return "window.compiledAsClass = (typeof data[" . $this->getIndex() . "] !== 'undefined');";
	}

	/**
	 * if exists in object, add columnDef to field
	 */
	public function setColumnDef(string $ilBronzaDefinition, string $datatablesDefinition)
	{
		if (! is_null($value = $this->getDatatableUserDataParameter($datatablesDefinition)))
			$this->addColumnDef($datatablesDefinition, $value);

		else if (($value = $this->$ilBronzaDefinition ?? null) !== null)
			$this->addColumnDef($datatablesDefinition, $value);
	}

	/**
	 * add columnDef to field
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 */
	public function addColumnDef(string $columnDef, $value)
	{
		if ($columnDef == 'datatableType')
			$columnDef = 'type';

		$this->columnDefs[$columnDef] = $value;
	}

	private function setClassnameColumnDef()
	{
		//add className to field columnDefs
		if (! isset($this->columnDefs['className']))
			$this->columnDefs['className'] = $this->getCamelName();

		$this->columnDefs['className'] .= " " . $this->getTDHtmlClassesString();
	}

	/**
	 * set field own columnDefs
	 */
	private function setColumnDefs()
	{
		$this->columnDefs = [];

		//parse through available columnDefs parameters and id set, store it
		foreach ($this->availableColumnDefs as $ilBronzaDefinition => $datatablesDefinition)
		{
			$this->setColumnDef($ilBronzaDefinition, $datatablesDefinition);
		}
	}
}
