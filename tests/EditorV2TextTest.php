<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesServiceProvider;
use IlBronza\Datatables\Castables\Data;
use IlBronza\Datatables\Contracts\DatatableFieldContract;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\FieldFactory;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldText as LegacyText;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldText;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use LogicException;
use Orchestra\Testbench\TestCase;

class EditorV2TextTest extends TestCase
{
	private Container $previousContainer;
	private $previousFacadeApplication;

	protected function getPackageProviders($app)
	{
		return [DatatablesServiceProvider::class];
	}

	protected function defineRoutes($router)
	{
		$router->put('renamed-records/{editorV2Record}', fn () => null)->name('records.rename');
	}

	protected function setUp() : void
	{
		$this->previousContainer = Container::getInstance();
		$this->previousFacadeApplication = Facade::getFacadeApplication();
		parent::setUp();
		config(['datatables.replace_model_id_string' => '__ROW_ID__']);
	}

	protected function tearDown() : void
	{
		try { parent::tearDown(); }
		finally {
			Container::setInstance($this->previousContainer);
			Facade::setFacadeApplication($this->previousFacadeApplication);
		}
	}

	private function field(array $parameters = []) : DatatableFieldText
	{
		$table = new Datatables();
		$table->setMainModelElement(EditorV2Record::class);
		$table->setRowIdIndex(2);
		return FieldFactory::createByType('title', 'ibDtV2.editor.text', array_replace(['translatedName' => 'Title'], $parameters), 1, null, $table);
	}

	public function test_v2_has_its_own_class_hierarchy_and_traits_and_can_be_used_without_a_table() : void
	{
		$field = new DatatableFieldText('title', [
			'modelClass' => EditorV2Record::class, 'editorTarget' => 'element',
		]);
		$this->assertInstanceOf(DatatableFieldContract::class, $field);
		$this->assertNotInstanceOf(DatatableField::class, $field);
		foreach (array_merge(class_parents($field), class_uses_recursive($field)) as $dependency)
			$this->assertStringStartsWith('IlBronza\\Datatables\\DatatablesFields\\ibDtV2\\', $dependency);

		$this->assertSame(['value' => 'Independent', 'targetId' => 42],
			$field->transformValue(new EditorV2Record(['id' => 42, 'title' => 'Independent'])));
		$this->assertStringContainsString('item.targetId', $field->getCustomColumnDef());
		$this->assertStringContainsString('data-url="/records/42"', $field->getInlineEditHtml());
	}

	public function test_v2_rejects_legacy_parameters_instead_of_silently_inheriting_their_behaviour() : void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Unsupported V2 parameter "fetcher"');
		$this->field(['fetcher' => ['url' => '/legacy']]);
	}

	public function test_table_pipeline_accepts_both_families_and_renders_v2_headers_and_filters() : void
	{
		$model = new EditorV2Record(['id' => 42, 'title' => '001', 'legacy' => 'Old', 'locked' => true]);
		$table = Datatables::create('mixed-editors', ['main' => ['fields' => [
			'legacy' => ['type' => 'editor.text', 'translatedName' => 'Legacy'],
			'title' => ['type' => 'ibDtV2.editor.text', 'translatedName' => 'Title',
				'order' => ['type' => 'desc'], 'saveButton' => true, 'fieldsGroupsDefinitions' => ['details']],
			'lockedTitle' => ['type' => 'ibDtV2.editor.text', 'editorProperty' => 'title',
				'disabledIf' => 'isLocked', 'translatedName' => 'Locked title'],
		]]], fn () => collect([$model]), false, null, EditorV2Record::class, true);
		$table->datatableUserData = new Data();
		$table->canHideColumns = false;
		$table->setData();
		$table->parseColumnDefs();
		$table->parseOptions();

		$field = $table->getFieldByName('title');
		$row = $table->getData()[0];
		$this->assertSame(42, $row[$table->getRowIdIndex()]);
		$this->assertSame('001', $row[$field->getIndex()]);
		$this->assertSame([42, 'Old'], $row[$table->getFieldByName('legacy')->getIndex()]);
		$this->assertSame(['value' => '001', 'disabled' => true], $row[$table->getFieldByName('lockedTitle')->getIndex()]);
		$this->assertSame([[$field->getIndex(), 'desc']], $table->options['order']);
		$this->assertCount($table->getFields()->count(), $table->customColumnDefs);
		$this->assertCount(2, $table->getBulkEditableFieldsArray());

		$header = view('datatables::uikit.header._header', ['table' => $table])->render();
		$this->assertStringContainsString('data-name="title"', $header);
		$this->assertStringContainsString('data-editorsavebutton="true"', $header);
		$this->assertStringContainsString('ib-dt-fieldsgroup-details', $header);
		$this->assertStringContainsString('data-filtertype="text"', $header);
		$this->assertStringContainsString('refreshRow&quot;:true', $header);
		$this->assertStringContainsString('name="footertitle"', view('datatables::uikit.footer._footer', ['table' => $table])->render());
		$this->assertStringContainsString('125px', view('datatables::datatablesFields.__tableSingleSpecCss', ['table' => $table])->render());
	}

	public function test_saved_column_settings_use_the_shared_field_contract() : void
	{
		$field = $this->field(['visible' => false, 'sortable' => false]);
		$field->table->datatableUserData = new Data(json_encode([
			'columnsSettings' => ['title' => ['selectors' => ['td' => ['width' => '210px']]]],
		]));
		$this->assertSame('210px', $field->getWidth());
		$this->assertFalse($field->getColumnDefs()['visible']);
		$this->assertFalse($field->getColumnDefs()['orderable']);
	}

	public function test_same_row_cells_contain_only_the_value_including_null_zero_and_empty_text() : void
	{
		$field = $this->field();
		foreach ([null, '', 0, '0', '001', 'Città "<&\''] as $value)
			$this->assertSame($value, $field->transformValue(new EditorV2Record(['id' => 42, 'title' => $value])));

		$this->assertNull($field->getValuePosition());
		$this->assertNull($field->getStructuredDataIndexString());
		$this->assertTrue($field->isEditor());
		$this->assertTrue($field->isBulkEditable());
	}

	public function test_conditional_disabled_keeps_one_shape_for_every_row_without_duplicate_ids() : void
	{
		$field = $this->field(['disabledIf' => 'isLocked']);
		foreach ([false, true] as $locked)
		{
			$model = new EditorV2Record(['id' => 42, 'title' => 'Note', 'locked' => $locked]);
			$this->assertSame(['value' => 'Note', 'disabled' => $locked], $field->transformValue($model));
			$html = $field->setInlineEditElement($model)->getInlineEditHtml();
			$this->assertSame($locked, str_contains($html, ' disabled=""'));
		}
		$this->assertFalse($field->isBulkEditable());
		$this->assertSame('["value"]', $field->getStructuredDataIndexString());
	}

	public function test_static_disabled_uses_scalar_data_and_combines_with_the_model_condition() : void
	{
		$model = new EditorV2Record(['id' => 42, 'title' => 'Note', 'locked' => false]);
		$field = $this->field(['disabled' => true]);
		$this->assertSame('Note', $field->transformValue($model));
		$this->assertFalse($field->isBulkEditable());
		$this->assertStringContainsString(' disabled=""', $field->getInlineEditHtml());
		$field = $this->field(['disabled' => true, 'disabledIf' => 'isLocked']);
		$this->assertSame(['value' => 'Note', 'disabled' => true], $field->transformValue($model));
	}

	public function test_missing_disable_method_fails_with_the_field_and_method_names() : void
	{
		$field = $this->field(['disabledIf' => 'missingMethod']);
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Editor "title" requires callable disabledIf "missingMethod"');
		$field->transformValue(new EditorV2Record());
	}

	public function test_different_model_contains_its_target_id_even_when_equal_to_the_row_id() : void
	{
		$field = $this->field(['editorTarget' => 'element', 'disabledIf' => 'isLocked']);
		$this->assertSame(['value' => 'Child', 'disabled' => false, 'targetId' => 42],
			$field->transformValue(new EditorV2Record(['id' => 42, 'title' => 'Child'])));
		$this->assertSame(['value' => null, 'disabled' => true, 'targetId' => null], $field->transformValue(null));
		$this->assertFalse($field->isBulkEditable());
	}

	public function test_disabled_condition_must_be_public_even_on_models_with_magic_calls() : void
	{
		$field = $this->field(['disabledIf' => 'privateLocked']);
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('disabledIf "privateLocked"');
		$field->transformValue(new EditorV2Record());
	}

	public function test_read_property_and_submitted_parameter_remain_distinct_in_all_contexts() : void
	{
		$field = $this->field(['editorProperty' => 'description', 'parameter' => 'notes', 'data' => ['field' => 'wrong']]);
		$model = new EditorV2Record(['id' => 'key/a', 'description' => 'Original']);
		$model->setKeyType('string');
		$this->assertSame('Original', $field->transformValue($model));
		$html = $field->setInlineEditElement($model)->getInlineEditHtml();
		$this->assertSame(1, substr_count($html, 'data-field='));
		$this->assertStringContainsString('data-field="notes"', $html);
		$this->assertStringContainsString('data-url="/records/key%2Fa"', $html);
		$this->assertSame('Original', $field->getInlineEditValue());

		$field->inlineEditProperty = 'title';
		$model->title = 'Inline';
		$this->assertSame('Inline', $field->getInlineEditValue());
		$this->assertSame('Original', $field->transformValue($model));
	}

	public function test_computed_and_forced_values_use_the_same_binding_for_table_and_inline() : void
	{
		$model = new EditorV2Record(['id' => 42, 'title' => 'hello']);
		$field = $this->field(['editorValueFunction' => 'computedTitle']);
		$this->assertSame('HELLO', $field->transformValue($model));
		$this->assertSame('HELLO', $field->getInlineEditValue());
		$field->forceValue = 0;
		$this->assertSame(0, $field->transformValue($model));
		$this->assertSame('0', $field->getInlineEditValue());
	}

	public function test_inline_attributes_and_text_are_escaped_once_and_reserved_attributes_are_unique() : void
	{
		$field = $this->field([
			'inputAttributes' => ['placeholder' => '"<&\'', 'class' => ['custom'], 'data-url' => 'wrong'],
			'htmlClasses' => ['extra'], 'data' => ['context' => '"<&\'', 'flag' => false],
			'containerAttributes' => ['class' => 'wrapper'], 'prefix' => '<pre>', 'suffix' => '&end',
		]);
		$html = $field->setInlineEditElement(new EditorV2Record(['id' => 42, 'title' => '"<&\'']))->getInlineEditHtml();
		$this->assertStringContainsString('value="&quot;&lt;&amp;&#039;"', $html);
		$this->assertStringContainsString('placeholder="&quot;&lt;&amp;&#039;"', $html);
		$this->assertStringContainsString('data-flag="false"', $html);
		$this->assertStringContainsString('&lt;pre&gt;', $html);
		$this->assertStringContainsString('custom extra', $html);
		$this->assertSame(1, substr_count($html, 'data-url='));
		$this->assertStringNotContainsString('&amp;quot;', $html);
	}

	public function test_permissions_remove_controls_and_bulk_without_changing_the_value() : void
	{
		Auth::shouldReceive('user')->andReturn(null);
		$field = $this->field(['editorRoles' => ['admin']]);
		$this->assertFalse($field->isBulkEditable());
		$this->assertSame('', $field->getInlineEditHtml());
		$this->assertStringNotContainsString('<input', $field->getCustomColumnDefSingleResult());
		$this->assertSame('Note', $field->transformValue(new EditorV2Record(['title' => 'Note'])));
	}

	public function test_save_settings_and_inline_create_use_the_declared_contract() : void
	{
		$field = $this->field(['saveTrigger' => 'blur', 'reloadTable' => true, 'refreshRow' => true, 'saveButton' => true, 'nullable' => false]);
		$this->assertSame(['saveTrigger' => 'blur', 'cellFormat' => 'scalar'], $field->getSaveSettings());
		$this->assertSame(['reloadTable' => true, 'refreshRow' => false], $field->fieldExtraData);
		$this->assertTrue($field->hasSaveButton());
		$this->assertFalse($field->isNullable());
		$html = $field->setInlineCreateElement(new EditorV2Record(), '/records')->getInlineEditHtml();
		$this->assertStringContainsString('data-url="/records"', $html);
		$this->assertStringContainsString('ib-datatable-inline-create-field', $html);
		$this->assertStringContainsString('data-save-trigger="blur"', $html);
	}

	public function test_old_and_new_editors_are_both_detected_for_inline_and_bulk() : void
	{
		$field = $this->field();
		$table = $field->table;
		$old = new LegacyText('legacy', ['width' => '125px', 'translatedName' => 'Legacy'], 2, null, $table);
		$table->fields = collect([$field, $old]);
		$table->bulkEdit = true;
		$this->assertCount(2, $table->getInlineEditFields());
		$this->assertCount(2, $table->getBulkEditableFieldsArray());
		$this->assertTrue($table->getBulkEditableFieldsArray()[0]['nullable']);
		$model = new EditorV2Record(['id' => 42, 'title' => 'New', 'legacy' => 'Old']);
		$this->assertSame('New', $field->transformValue($model));
		$this->assertSame([42, 'Old'], $old->transformValue($model));
	}

	public function test_inline_derivation_keeps_v2_binding_and_disabled_condition() : void
	{
		$parameters = ['type' => 'ibDtV2.editor.text', 'parameter' => 'notes', 'editorProperty' => 'description',
			'disabledIf' => 'isLocked', 'translatedName' => 'Title', 'inputAttributes' => ['maxlength' => 50]];
		$group = Datatables::makeInlineEditFieldsGroup('records', [], ['fields' => ['title' => $parameters]], new EditorV2Record());
		$this->assertSame($parameters, $group['fields']['title']);
		$table = Datatables::createInlineEditTable('records', $group, new EditorV2Record(['description' => 'Inline', 'locked' => true]));
		$field = $table->getInlineEditFields()->first();
		$this->assertInstanceOf(DatatableFieldText::class, $field);
		$this->assertStringContainsString(' disabled=""', $field->getInlineEditHtml());
		$this->assertStringContainsString('value="Inline"', $field->getInlineEditHtml());
	}

	public function test_plain_column_does_not_emit_conditional_metadata_checks() : void
	{
		$plain = $this->field()->getCustomColumnDefSingleResult();
		$this->assertStringContainsString('const value = item;', $plain);
		$this->assertStringContainsString('const targetId = row[2];', $plain);
		$this->assertStringNotContainsString('item.disabled', $plain);
		$this->assertStringNotContainsString('item.targetId', $plain);
		$this->assertStringNotContainsString('return ', $plain);
	}

	public function test_custom_route_and_related_model_placeholder_are_used_for_the_update_url() : void
	{
		$field = $this->field(['customUpdateRouteName' => 'records.rename']);
		$this->assertStringEndsWith('/renamed-records/__ROW_ID__', $field->getEditorUpdateUrl());
		$field->setInlineEditElement(new EditorV2Record(['id' => 42]));
		$this->assertStringEndsWith('/renamed-records/42', $field->getInlineEditUpdateUrl());

		$child = $this->field(['editorTarget' => 'element', 'modelClass' => EditorV2ChildRecord::class]);
		$this->assertSame('/children/__ROW_ID__', $child->getEditorUpdateUrl());
	}

	public function test_conditional_state_requests_row_refresh_unless_explicitly_overridden() : void
	{
		$this->assertTrue($this->field(['disabledIf' => 'isLocked'])->fieldExtraData['refreshRow']);
		$this->assertFalse($this->field(['disabledIf' => 'isLocked', 'refreshRow' => false])->fieldExtraData['refreshRow']);
		$this->assertFalse($this->field(['disabledIf' => 'isLocked', 'reloadTable' => true])->fieldExtraData['refreshRow']);
	}

	public function test_inline_derivation_accepts_the_shorthand_type_declaration() : void
	{
		$group = Datatables::makeInlineEditFieldsGroup('records', [], ['fields' => ['title' => 'ibDtV2.editor.text']], new EditorV2Record());
		$this->assertSame(['type' => 'ibDtV2.editor.text'], $group['fields']['title']);
	}

	public function test_row_target_requires_a_declared_row_id_instead_of_guessing_an_array_position() : void
	{
		$field = $this->field();
		$field->table->rowId = null;
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('requires a rowId column');
		$field->getCustomColumnDef();
	}
}

class EditorV2Record extends Model
{
	protected $guarded = [];
	public function isLocked() : bool { return (bool) $this->locked; }
	public function computedTitle() : string { return strtoupper($this->title); }
	public function getEditorUpdateUrl() : string { return '/records/__ROW_ID__'; }
	private function privateLocked() : bool { return true; }
}

class EditorV2ChildRecord extends EditorV2Record
{
	public function getEditorUpdateUrl() : string { return '/children/__ROW_ID__'; }
}
