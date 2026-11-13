<?php

// Executed by editorV2.test.js. The .inc suffix keeps it out of PHPUnit discovery.
if (realpath($_SERVER['SCRIPT_FILENAME']) !== __FILE__)
	return;

require $argv[1];

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldText;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldTextarea;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldNumeric;
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
	'customUrl' => ['updateUrlMethod' => 'customCellUpdateUrl'],
	'denied' => ['editorRoles' => ['admin']],
	'attributes' => [
		'inputAttributes' => ['placeholder' => '"<b>&\'', 'maxlength' => 80, 'class' => ['custom'], 'data-save-trigger' => 'blur'],
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
	$result[$name . 'HeaderData'] = $field->getEditorDataAttributes();
}

foreach ([
	'textarea' => [],
	'textareaConditional' => ['disabledIf' => 'isLocked'],
	'textareaForeign' => ['editorTarget' => 'element'],
	'textareaDenied' => ['editorRoles' => ['admin']],
	'textareaAttributes' => ['inputAttributes' => ['rows' => 5, 'maxlength' => 400, 'class' => 'notes', 'value' => 'ignored', 'type' => 'text'], 'prefix' => '<pre>', 'parameter' => 'notes'],
] as $name => $parameters)
{
	$field = new DatatableFieldTextarea('title', $parameters, 1, null, $table);
	$result[$name] = $field->getCustomColumnDef();
	$result[$name . 'HeaderData'] = $field->getEditorDataAttributes();
}

$inlineModel = new class {
	public string $title = 'Inline';
	public function getKey() : string { return 'key/a'; }
	public function customCellUpdateUrl() : string { return '/special/inline?next=%2F&mode=one'; }
};
$inlineField = new DatatableFieldText('title', [
	'parameter' => 'notes', 'prefix' => '<pre>', 'containerAttributes' => ['class' => 'inline-wrapper'],
], 1, null, $table);
$result['inlineEdit'] = $inlineField->setInlineEditElement($inlineModel)->getInlineEditHtml();
$result['inlineCreate'] = $inlineField->setInlineCreateElement($inlineModel, '/records?create=1')->getInlineEditHtml();
$inlineField->inlineCreateStoreUrl = null;
$inlineField->updateUrlMethod = 'customCellUpdateUrl';
$result['inlineCustomUrl'] = $inlineField->setInlineEditElement($inlineModel)->getInlineEditHtml();

$textarea = new DatatableFieldTextarea('title', ['parameter' => 'notes'], 1, null, $table);
$inlineModel->title = "\nPrima riga\n</textarea><script>test</script>\n";
$result['textareaInlineEdit'] = $textarea->setInlineEditElement($inlineModel)->getInlineEditHtml();
$result['textareaInlineCreate'] = $textarea->setInlineCreateElement($inlineModel, '/records?create=1')->getInlineEditHtml();

$customField = new DatatableFieldText('title', ['updateUrlMethod' => 'customCellUpdateUrl'], 1, null, $table);
$result['customUrlCells'] = [];
foreach (['/special/one?next=%2F&mode=one', null] as $url)
{
	$model = new class($url) {
		public string $title = '001';
		public function __construct(private ?string $url) {}
		public function customCellUpdateUrl() : ?string { return $this->url; }
	};
	$result['customUrlCells'][] = $customField->transformValue($model);
}

foreach ([
	'numeric' => ['decimals' => 2, 'currency' => 'EUR'],
	'numericConstraints' => ['decimals' => 2, 'maxDecimals' => 2, 'min' => '0.03', 'max' => '100.13', 'step' => '0.05'],
	'numericUs' => ['decimals' => 3, 'fixedDecimals' => false, 'decimalSeparator' => '.', 'thousandsSeparator' => ','],
	'numericDenied' => ['decimals' => 2, 'editorRoles' => ['admin']],
	'numericForeign' => ['decimals' => 2, 'editorTarget' => 'element', 'disabledIf' => 'isLocked'],
	'numericCustomUrl' => ['decimals' => 2, 'updateUrlMethod' => 'customCellUpdateUrl'],
] as $name => $parameters)
{
	$field = new DatatableFieldNumeric('title', $parameters, 1, null, $table);
	$result[$name] = $field->getCustomColumnDef();
	$result[$name . 'HeaderData'] = $field->getEditorDataAttributes();
	foreach ($name === 'numericConstraints' ? ['0.08', '12.13'] : ['9007199254740993.123456789012345678900', '12.3456'] as $index => $value)
	{
		$model = new class($value, $index) {
			public function __construct(public string $title, private int $id) {}
			public function getKey() : int { return $this->id + 42; }
			public function isLocked() : bool { return false; }
			public function customCellUpdateUrl() : string { return '/special/numeric/' . $this->id; }
		};
		$result[$name . 'Cells'][] = $field->transformValue($model);
	}
}
$numeric = new DatatableFieldNumeric('title', ['parameter' => 'notes', 'decimals' => 2, 'currency' => 'EUR'], 1, null, $table);
$inlineModel->title = '9007199254740993.123456789012345678900';
$result['numericInlineEdit'] = $numeric->setInlineEditElement($inlineModel)->getInlineEditHtml();
$result['numericInlineCreate'] = $numeric->setInlineCreateElement($inlineModel, '/records?create=1')->getInlineEditHtml();

echo json_encode($result, JSON_THROW_ON_ERROR);
