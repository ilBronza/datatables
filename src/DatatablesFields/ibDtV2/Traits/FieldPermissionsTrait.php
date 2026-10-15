<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use Illuminate\Support\Facades\Auth;

trait FieldPermissionsTrait
{
	public ?array $allowedForRoles = null;
	public ?array $forbiddenForRoles = null;

	public function assignRoles(array $roles) : void
	{
		$this->allowedForRoles = array_merge($this->allowedForRoles ?? [], $roles);
	}

	public function assignForbiddenRoles(array $roles) : void
	{
		$this->forbiddenForRoles = array_merge($this->forbiddenForRoles ?? [], $roles);
	}

	public function isAllowed() : bool
	{
		if ($this->allowedForRoles === null && $this->forbiddenForRoles === null)
			return true;
		if (! $user = Auth::user())
			return false;

		$roles = $user->roles->pluck('name')->all();
		if (array_intersect($roles, $this->forbiddenForRoles ?? []))
			return false;

		return $this->allowedForRoles === null || (bool) array_intersect($roles, $this->allowedForRoles);
	}
}
