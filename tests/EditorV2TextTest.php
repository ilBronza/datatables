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
use IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldTextarea;
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

	private function inlineRoute(string $html) : array
	{
		$this->assertStringNotContainsString('data-url=', $html);
		$this->assertSame(1, preg_match('/<input\b([^>]*)>/', $html, $input));
		foreach (['data-save-trigger', 'data-field=', 'data-ib-dt-value-type', 'data-ib-dt-cell-format'] as $attribute)
			$this->assertStringNotContainsString($attribute, $input[1]);
		$this->assertSame(1, preg_match('/data-ib-dt-editor-route="([^"]+)"/', $html, $matches));
		return json_decode(htmlspecialchars_decode($matches[1], ENT_QUOTES), true, 512, JSON_THROW_ON_ERROR);
	}

	public function test_textarea_reuses_text_binding_and_renders_inline_content_with_escaped_newlines() : void
	{
		config(['datatables.editor.saveTrigger' => 'enter']);
		$field = FieldFactory::createByType('title', 'ibDtV2.editor.textarea', [
			'parameter' => 'notes', 'inputAttributes' => ['rows' => 5, 'maxlength' => 400, 'type' => 'text', 'value' => 'ignored'],
		], 1, null, $this->field()->table);
		$this->assertInstanceOf(DatatableFieldText::class, $field);
		$this->assertInstanceOf(DatatableFieldTextarea::class, $field);
		$value = "\nPrima riga\n</textarea><script>test</script>\n";
		$model = new EditorV2Record(['id' => 42, 'title' => $value]);
		$this->assertSame($value, $field->transformValue($model));
		$this->assertTrue($field->isBulkEditable());
		$this->assertSame('text', $field->getEditorDataAttributes()['ib-dt-value-type']);
		$this->assertSame('blur', $field->getSaveSettings()['saveTrigger']);
		$this->assertSame('notes', $field->getFieldSpecificData()['field']);
		$html = $field->setInlineEditElement($model)->getInlineEditHtml();
		$this->assertStringContainsString('<textarea', $html);
		$this->assertStringContainsString('rows="5"', $html);
		$this->assertStringContainsString('maxlength="400"', $html);
		$this->assertStringContainsString('uk-textarea', $html);
		$this->assertStringContainsString(">\n\nPrima riga\n&lt;/textarea&gt;&lt;script&gt;test&lt;/script&gt;\n</textarea>", $html);
		$this->assertStringNotContainsString('<input', $html);
		$this->assertStringNotContainsString('<script>', $html);
		$this->assertSame(1, preg_match('/<textarea\\b([^>]*)>/', $html, $matches));
		$this->assertStringNotContainsString('type=', $matches[1]);
		$this->assertStringNotContainsString(' value=', $matches[1]);
		$this->assertStringNotContainsString('data-url=', $matches[1]);
		$this->assertSame('250px', $field->getUnstyledWidth());
	}

	public function test_textarea_inline_derivation_keeps_the_type_attributes_permissions_and_create_route() : void
	{
		$parameters = ['type' => 'ibDtV2.editor.textarea', 'parameter' => 'notes', 'editorProperty' => 'description',
			'disabledIf' => 'isLocked', 'translatedName' => 'Notes', 'inputAttributes' => ['rows' => 4], 'saveTrigger' => 'tab'];
		$model = new EditorV2Record(['id' => 42, 'description' => "Uno\nDue", 'locked' => true]);
		$group = Datatables::makeInlineEditFieldsGroup('records', [], ['fields' => ['title' => $parameters]], $model);
		$this->assertSame($parameters, $group['fields']['title']);
		$table = Datatables::createInlineEditTable('records', $group, $model);
		$field = $table->getInlineEditFields()->first();
		$this->assertInstanceOf(DatatableFieldTextarea::class, $field);
		$this->assertSame('ibDtV2.editor.textarea', $field->getInlineFieldType());
		$this->assertSame(['value' => "Uno\nDue", 'disabled' => true], $field->transformValue($model));
		$this->assertFalse($field->isBulkEditable());
		$this->assertStringContainsString(' disabled=""', $field->getInlineEditHtml());
		$this->assertStringContainsString('rows="4"', $field->getInlineEditHtml());
		$html = $field->setInlineCreateElement($model, '/records')->getInlineEditHtml();
		$this->assertStringContainsString('ib-datatable-inline-create-field', $html);
		$this->assertStringContainsString('data-save-trigger="tab"', $html);
		$this->assertSame(1, preg_match('/data-ib-dt-editor-route="([^"]+)"/', $html, $matches));
		$this->assertSame(['storeUrl' => '/records'], json_decode(htmlspecialchars_decode($matches[1], ENT_QUOTES), true));
		$field->editorRoles = ['admin'];
		$this->assertSame('', $field->getInlineEditHtml());
	}

	public function test_textarea_inherits_tab_defaults_and_rejects_enter_to_keep_native_newlines() : void
	{
		config(['datatables.editor.saveTrigger' => 'tab']);
		$field = new DatatableFieldTextarea('title', ['modelClass' => EditorV2Record::class]);
		$this->assertSame('tab', $field->getSaveSettings()['saveTrigger']);
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('saveTrigger must be blur, tab');
		new DatatableFieldTextarea('title', ['saveTrigger' => 'enter']);
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
		$this->assertSame(42, $this->inlineRoute($field->getInlineEditHtml())['targetId']);
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
				'order' => ['type' => 'desc'], 'saveTrigger' => 'tab', 'saveButton' => true, 'fieldsGroupsDefinitions' => ['details']],
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
		$footer = view('datatables::uikit.footer._footer', ['table' => $table])->render();
		$this->assertStringContainsString('name="footertitle"', $footer);
		$table->summary = ['sum' => []];
		$summary = view('datatables::uikit.header._headerSummary', ['table' => $table])->render();
		$dom = new \DOMDocument();
		@$dom->loadHTML('<table>' . $header . '<thead>' . $summary . '</thead>' . $footer . '</table>');
		$xpath = new \DOMXPath($dom);
		$this->assertSame(2, $xpath->query('//thead//th[@data-field-name="title" and @data-save-trigger="tab"]')->length);
		$this->assertSame(2, $xpath->query('//thead//th[@data-field-name="title" and @data-field="title" and @data-ib-dt-value-type="text" and @data-ib-dt-cell-format="scalar"]')->length);
		$this->assertSame(2, $xpath->query('//thead//th[@data-field-name="lockedTitle" and @data-ib-dt-cell-format="value"]')->length);
		$this->assertSame(0, $xpath->query('//thead//th[@data-field-name="legacy" and @data-save-trigger]')->length);
		foreach ($table->getFields() as $column) {
			$nodes = $xpath->query('//th[@data-field-name="' . $column->getFieldName() . '"]');
			// Title, header filter, footer filter and both summaries, for both field families.
			$this->assertSame(5, $nodes->length);
			foreach ($nodes as $node) {
				$this->assertSame((string) $column->getIndex(), $node->getAttribute('data-original-column-index'));
				$this->assertSame((string) $column->getIndex(), $node->getAttribute('data-column'));
			}
		}
		$table->getFieldByName('legacy')->mainHeader = ['label' => 'Details', 'colspan' => 2];
		$mainHeader = view('datatables::uikit.header._mainHeader', ['table' => $table])->render();
		$this->assertStringContainsString('colspan="2"', $mainHeader);
		$this->assertStringNotContainsString('data-original-column-index', $mainHeader);
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
		$this->assertSame('key/a', $this->inlineRoute($html)['targetId']);
		$this->assertSame('/records/__ROW_ID__', $this->inlineRoute($html)['urlTemplate']);
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
		$this->assertFalse($field->getEditorRouteConfiguration()['history']);
		$this->assertSame('HELLO', $field->transformValue($model));
		$this->assertSame('HELLO', $field->getInlineEditValue());
		$field->forceValue = 0;
		$this->assertFalse($field->getEditorRouteConfiguration()['history']);
		$this->assertFalse($this->field(['forceValue' => 0])->getEditorRouteConfiguration()['history']);
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
		$this->assertStringNotContainsString('data-url=', $html);
		$this->assertSame(1, substr_count($html, 'data-ib-dt-inline-control='));
		$this->assertSame(42, $this->inlineRoute($html)['targetId']);
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
		$this->assertSame(['storeUrl' => '/records'], $this->inlineRoute($html));
		$this->assertStringContainsString('ib-datatable-inline-create-field', $html);
		$this->assertStringContainsString('data-save-trigger="blur"', $html);
		$this->assertSame('blur', $field->getHeaderSaveTrigger());
		$this->assertSame('blur', $field->getHeaderData()['save-trigger']);
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

	public function test_route_templates_are_column_metadata_and_are_not_compiled_into_inputs() : void
	{
		$field = $this->field(['customUpdateRouteName' => 'records.rename', 'data' => ['url' => '/wrong']]);
		$route = $field->getEditorRouteConfiguration();
		$this->assertStringEndsWith('/renamed-records/__ROW_ID__', $route['urlTemplate']);
		$this->assertSame('__ROW_ID__', $route['idPlaceholder']);
		$this->assertSame('row', $route['target']);
		$this->assertStringContainsString('ibDtEditor:', $field->getCustomColumnDef());
		$this->assertStringNotContainsString('data-url', $field->getCustomColumnDef());
		$this->assertStringNotContainsString('renamed-records', $field->getCustomColumnDefSingleResult());
		$this->assertStringNotContainsString('encodeURIComponent', $field->getCustomColumnDefSingleResult());
		$this->assertSame('element', $this->field(['editorTarget' => 'element'])->getEditorRouteConfiguration()['target']);
	}

	public function test_conditional_state_requests_row_refresh_unless_explicitly_overridden() : void
	{
		$this->assertTrue($this->field(['disabledIf' => 'isLocked'])->fieldExtraData['refreshRow']);
		$this->assertFalse($this->field(['disabledIf' => 'isLocked', 'refreshRow' => false])->fieldExtraData['refreshRow']);
		$this->assertFalse($this->field(['disabledIf' => 'isLocked', 'reloadTable' => true])->fieldExtraData['refreshRow']);
	}

	public function test_optional_update_url_method_is_evaluated_on_each_record_and_empty_urls_keep_a_stable_shape() : void
	{
		$field = $this->field(['updateUrlMethod' => 'customCellUpdateUrl']);
		foreach (['/special/a?mode=one&next=%2F', '/special/b', null, '', '  '] as $url)
		{
			$model = new EditorV2Record(['id' => 42, 'title' => '001', 'cell_url' => $url]);
			$this->assertSame(['value' => '001', 'updateUrl' => trim($url ?? '') === '' ? null : $url], $field->transformValue($model));
		}
		$this->assertSame(['value' => null, 'updateUrl' => null], $field->transformValue(null));
		$this->assertSame('value', $field->getValuePosition());
		$this->assertTrue($field->getEditorRouteConfiguration()['updateUrlFromCell']);
		$this->assertSame('/records/__ROW_ID__', $field->getEditorRouteConfiguration()['urlTemplate']);
		$this->assertArrayNotHasKey('updateUrlFromCell', $this->field()->getEditorRouteConfiguration());
		$this->assertTrue($field->fieldExtraData['refreshRow']);
		$this->assertFalse($this->field(['updateUrlMethod' => 'customCellUpdateUrl', 'refreshRow' => false])->fieldExtraData['refreshRow']);
	}

	public function test_custom_cell_url_combines_with_child_targets_and_disabled_metadata() : void
	{
		$field = $this->field(['updateUrlMethod' => 'customCellUpdateUrl', 'editorTarget' => 'element', 'disabledIf' => 'isLocked']);
		$model = new EditorV2Record(['id' => 42, 'title' => 0, 'cell_url' => '/special/child', 'locked' => true]);
		$this->assertSame(['value' => 0, 'updateUrl' => '/special/child', 'disabled' => true, 'targetId' => 42], $field->transformValue($model));
		$this->assertStringNotContainsString('data-url', $field->getCustomColumnDef());
	}

	public function test_custom_inline_update_url_uses_the_record_method_and_create_keeps_its_store_route() : void
	{
		$field = $this->field(['updateUrlMethod' => 'customCellUpdateUrl']);
		$model = new EditorV2Record(['id' => 42, 'title' => 'Note', 'cell_url' => '/special/42?mode=one&next=%2F']);
		$field->setInlineEditElement($model);
		$this->assertSame($model->cell_url, $field->getInlineEditUpdateUrl());
		$this->assertSame($model->cell_url, $this->inlineRoute($field->getInlineEditHtml())['updateUrl']);
		$model->cell_url = null;
		$this->assertNull($this->inlineRoute($field->getInlineEditHtml())['updateUrl']);
		$this->assertSame('/records/42', $field->getInlineEditUpdateUrl());
		$field->updateUrlMethod = 'missingMethod';
		$field->setInlineCreateElement($model, '/records/store');
		$this->assertSame(['storeUrl' => '/records/store'], $this->inlineRoute($field->getInlineEditHtml()));
		$this->assertSame('/records/store', $field->getInlineEditUpdateUrl());
	}

	public function test_update_url_method_must_exist_on_the_model() : void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Editor "title" requires callable updateUrlMethod "missingMethod"');
		$this->field(['updateUrlMethod' => 'missingMethod'])->transformValue(new EditorV2Record());
	}

	public function test_update_url_method_must_be_public_even_with_eloquent_magic_calls() : void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('requires callable updateUrlMethod "privateUpdateUrl"');
		$this->field(['updateUrlMethod' => 'privateUpdateUrl'])->transformValue(new EditorV2Record());
	}

	public function test_update_url_method_rejects_non_string_results() : void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('updateUrlMethod "customCellUpdateUrl" must return a string or null');
		$this->field(['updateUrlMethod' => 'customCellUpdateUrl'])->transformValue(new EditorV2Record(['cell_url' => 42]));
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
	public function customCellUpdateUrl() { return $this->cell_url; }
	private function privateUpdateUrl() : string { return '/private'; }
	private function privateLocked() : bool { return true; }
}

class EditorV2ChildRecord extends EditorV2Record
{
	public function getEditorUpdateUrl() : string { return '/children/__ROW_ID__'; }
}
