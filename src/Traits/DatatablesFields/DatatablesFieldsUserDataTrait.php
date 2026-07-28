<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Auth;

trait DatatablesFieldsUserDataTrait
{
    public function getDatatableUserData()
    {
        if($this->userData ?? false)
            return $this->userData;

        if(! $this->table)
            return null;

        if(! Auth::user())
            return null;

        $this->userData = $this->table->getDatatableUserData()->getcolumnsItem($this->getName());

        return $this->userData;
    }

    public function getDatatableUserDataParameter(string $key)
    {
        // When users cannot choose column visibility, do not let an old
        // per-user preference keep a column hidden with no way to restore it.
        if ($key === 'visible' && $this->table && ! $this->table->canHideColumns())
            return null;

        $userData = $this->getDatatableUserData();

        return $userData->$key ?? null;
    }

    public function isVisible()
    {
        if(($value = $this->getDatatableUserDataParameter('visible')) !== null)
            return $value;

        if(! is_null($this->visible ?? null))
            return !! $this->visible;

        return true;
    }
}
