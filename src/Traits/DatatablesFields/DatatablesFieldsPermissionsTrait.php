<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Auth;
use Spatie\Permission\Models\Role;

trait DatatablesFieldsPermissionsTrait
{
    public function assignRoles(array $roles)
    {
        $this->allowedForRoles = array_merge(
            $this->allowedForRoles ?? [],
            $roles
        );
    }

    public function assignForbiddenRoles(array $roles)
    {
        $this->forbiddenForRoles = array_merge(
            $this->forbiddenForRoles ?? [],
            $roles
        );
    }

    public function isAllowedForGuest()
    {
        if(isset($this->allowedForRoles))
            return false;

        if(isset($this->forbiddenForRoles))
            return false;

        return true;
    }

    public function isForbiddenForRole(Role $role)
    {
        if(! isset($this->forbiddenForRoles))
            return false;

        return in_array($role->name, $this->forbiddenForRoles);
    }

    public function isAllowedForRole(Role $role)
    {
        if(! isset($this->allowedForRoles))
            return true;

        return in_array($role->name, $this->allowedForRoles);
    }

    public function isAllowed()
    {
        if($this->isAllowedForGuest())
            return true;

        if(! $user = Auth::user())
            return false;

        foreach($user->roles as $role)
            if($this->isForbiddenForRole($role))
                return false;

        foreach($user->roles as $role)
            if($this->isAllowedForRole($role))
                return true;

        return false;
    }

    
}
