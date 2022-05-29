<?php

namespace IlBronza\Datatables\Castables;

class Data
{
	public $columns;

	public function __construct(string $value = null)
	{
		$this->columns = collect();

		if(! $value)
			return ;

		$data = json_decode($value, true);

		foreach($data['columns'] ?? [] as $column)
			$this->columns->push(DatatableColumn::refresh($column));
	}

	public function getcolumnsItem(string $columnName)
	{
		if($column = $this->columns->firstWhere('name', $columnName))
			return $column;

		$column = new DatatableColumn($columnName);

		$this->columns->push($column);

		return $column;
	}

	public function hideColumn(string $columnName)
	{
		$column = $this->getcolumnsItem($columnName);
		$column->hide();
	}

	public function showColumn(string $columnName)
	{
		$column = $this->getcolumnsItem($columnName);
		$column->show();
	}
}

class DatatableColumn
{
	public $name;
	public $visible;

	public function __construct(string $name, array $parameters = [])
	{
		$this->name = $name;

		foreach($parameters as $key => $value)
			$this->$key = $value;
	}

	static function refresh(array $parameters)
	{
		if(! ($name = $parameters['name'] ?? null))
			throw new \Exception('missing name for column: ' . json_encode($parameters));

		unset($parameters['name']);

		return new static($name, $parameters);
	}

	public function hide()
	{
		$this->visible = false;
	}

	public function show()
	{
		$this->visible = true;
	}
}