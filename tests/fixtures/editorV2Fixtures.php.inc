<?php

// Executed by editorV2.test.js. The .inc suffix keeps it out of PHPUnit discovery.
if (realpath($_SERVER['SCRIPT_FILENAME']) !== __FILE__)
	return;

require $argv[1];

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldText;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

$container = new Container();
$container->instance('config', new Repository(['datatables' => [
	'replace_model_id_string' => '__ROW_ID__', 'editor' => ['saveTrigger' => 'enter'],
]]));
$container->instance('auth', new class { public function user() { return null; } });
Container::setInstance($container);
Facade::setFacadeApplication($container);
if (! class_exists('Auth'))
	class_alias(\Illuminate\Support\Facades\Auth::class, 'Auth');

$table = new Datatables();
$table->setRowIdIndex(2);
$table->placeholderElement = new class {
	public function getEditorUpdateUrl() : string { return '/records/__ROW_ID__?from="table"&mode=edit'; }
};

$definitions = [
	'plain' => [],
	'conditional' => ['disabledIf' => 'isLocked'],
	'disabled' => ['disabled' => true],
	'foreign' => ['editorTarget' => 'element'],
	'denied' => ['editorRoles' => ['admin']],
	'attributes' => [
		'inputAttributes' => ['placeholder' => '"<b>&\'', 'maxlength' => 80, 'class' => ['custom']],
		'containerAttributes' => ['class' => 'outer'], 'cellAttributes' => ['class' => 'marked wide', 'data-kind' => '"<&'],
		'htmlClasses' => ['configured'], 'data' => ['flag' => false, 'context' => '"<script>&\''],
		'prefix' => '<pre>', 'suffix' => '&suffix', 'parameter' => 'notes', 'editorProperty' => 'description',
	],
];

$result = [];
foreach ($definitions as $name => $parameters)
{
	$field = new DatatableFieldText('title', $parameters, 1, null, $table);
	$result[$name] = $field->getCustomColumnDef();
}

echo json_encode($result, JSON_THROW_ON_ERROR);
