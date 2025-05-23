<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFunction extends DatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;


	public function transformValue($value)
	{
		if(! $value)
			return null;
		
		if($this->requiresKey())
			return $this->transformValueWithKey($value);

		if(isset($this->staticVariableValue))
			return $value->{$this->function}($this->staticVariableValue);

		if(! isset($this->variable))
			return $value->{$this->function}();

		$variableValue = $this->table->getVariable($this->variable);
		return $value->{$this->function}($variableValue);
	}

	public function transformValueWithKey($value)
	{
		if(! $value)
			return;

		if(isset($this->staticVariableValue))
			return 
			[
				$value->getKey(),
				$value->{$this->function}($this->staticVariableValue)
			];

		if(! isset($this->variable))
			return 
			[
				$value->getKey(),
				$value->{$this->function}()
			];

		$variableValue = $this->table->getVariable($this->variable);

		return 
			[
				$value->getKey(),
				$value->{$this->function}($variableValue)
			];
	}	
}