<?php

namespace IlBronza\Datatables\DatatablesFields\ibDtV2\Traits;

use Illuminate\Support\Facades\Auth;

trait EditorPermissionsTrait
{
	public array $editorRoles = [];

	public function userCanEdit()
	{
		if (! $this->hasEditorRoles())
			return true;

		if (! $user = Auth::user())
			return false;

		foreach ($user->roles as $role)
			if (in_array($role->name, $this->editorRoles, true))
				return true;

		return false;
	}

	public function hasEditorRoles() : bool
	{
		return count($this->getEditorRoles()) > 0;
	}

	public function getEditorRoles() : array
	{
		return $this->editorRoles;
	}

}
