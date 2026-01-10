<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\Models\DatatableUserData;
use Illuminate\Support\Str;

use function route;

trait DatatableColumnDisplayTrait
{
	public function getColumnDisplayRoute()
	{
		return route('datatables.columnShowing.update', ['tableKey' => $this->getColumnDisplayKey()]);
	}

	public function usesColumnDisplay()
    {
        return true;
    }

    public function setColumnDisplayKey(string $columnDisplayKey)
    {
        $this->columnDisplayKey = $columnDisplayKey;
    }

    private function getColumnDisplayKeyPieces() : array
    {
        if(! $routeName = request()->route()->getName())
            throw new \Exception('Dichiara routenname per questo indirizzo, o i campi della tabella non saranno selezionabili');

        return [
            request()->route()->getName(),
            class_basename(
                $this->getPlaceholderElement()
            )
        ];
    }

    private function calculateColumnDisplayKey() : string
    {
        $pieces = $this->getColumnDisplayKeyPieces();

        $columnDisplayKey = Str::slug(
                implode(" ", $pieces)
            );

        $this->setColumnDisplayKey($columnDisplayKey);

        return $this->getColumnDisplayKey();
    }

    public function getColumnDisplayKey() : string
    {
        if($this->columnDisplayKey)
            return $this->columnDisplayKey;

        return $this->calculateColumnDisplayKey();
    }

    public function getDatatableUserData()
    {
        if($this->datatableUserData ?? null)
            return $this->datatableUserData;

        $datatableUserDataModel =  DatatableUserData::provideByTableKey(
            $this->getColumnDisplayKey()
        );

        $this->datatableUserData = $datatableUserDataModel->data;

        return $this->datatableUserData;
    }
}