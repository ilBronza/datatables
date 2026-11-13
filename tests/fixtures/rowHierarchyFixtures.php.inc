<?php

if (realpath($_SERVER['SCRIPT_FILENAME']) !== __FILE__) return;
require $argv[1];

use IlBronza\Datatables\DatatablesFields\ibDtV2\DatatableFieldRowParentId;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

$container = new Container();
$container->instance('config', new Repository());
$loader = new FileLoader(new Filesystem(), __DIR__ . '/../../resources/lang');
$loader->addNamespace('datatables', __DIR__ . '/../../resources/lang');
$container->instance('translator', new Translator($loader, 'it'));
Container::setInstance($container);
$field = new DatatableFieldRowParentId('row_parent_id', [], 1);
echo json_encode(['column' => $field->getCustomColumnDef(), 'options' => $field->getColumnDefs()], JSON_THROW_ON_ERROR);
