<?php

namespace IlBronza\Datatables\Http\Middleware;

use IlBronza\CRUD\Middleware\CRUDBasePackageMiddlewareRolesPermissions;

/**
 * Resolves allowed roles for Datatables routes from config (datatables.defaultRoles / datatables.routeRoles).
 */
class DatatablesMiddlewareRolesPermissions extends CRUDBasePackageMiddlewareRolesPermissions
{
    protected string $configPackageName = 'datatables';
}
