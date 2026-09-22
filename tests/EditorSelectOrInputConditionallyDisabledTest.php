<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldSelectOrInputConditionallyDisabled;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EditorSelectOrInputConditionallyDisabledTest extends TestCase
{
	private Container $previousContainer;

	protected function setUp() : void
	{
		parent::setUp();

		$this->previousContainer = Container::getInstance();
		$container = new Container();
		$container->instance('config', new Repository([
			'datatables' => ['replace_model_id_string' => '__ROW_ID__'],
		]));
		Container::setInstance($container);
	}

	protected function tearDown() : void
	{
		Container::setInstance($this->previousContainer);
		parent::tearDown();
	}

	private function makeField(?string $displayCode, string $rawEntry, bool $disabled) : SelectOrInputConditionallyDisabledProbe
	{
		$field = (new ReflectionClass(SelectOrInputConditionallyDisabledProbe::class))->newInstanceWithoutConstructor();
		$field->name = 'code';
		$field->parameter = 'code';
		$field->nullString = 'nd';
		$field->editorProperty = 'display_code';
		$field->conditionallyDisabledMethod = 'isCodeDisabled';
		$field->element = new class($displayCode, $rawEntry, $disabled) {
			public string $code = 'A';
			public ?string $display_code;
			public string $raw_entry_code;

			public function __construct(?string $displayCode, string $rawEntry, public bool $disabled)
			{
				$this->display_code = $displayCode;
				$this->raw_entry_code = $rawEntry;
			}

			public function getKey() : int
			{
				return 42;
			}

			public function isCodeDisabled() : bool
			{
				return $this->disabled;
			}
		};

		return $field;
	}

	public function test_disabled_flag_follows_the_existing_input_and_select_payloads() : void
	{
		$input = $this->makeField('LOCKED', 'manual', true);
		$select = $this->makeField(null, '', true);
		$enabled = $this->makeField('LOCKED', 'manual', false);

		$this->assertSame([42, 'LOCKED', 'Locked option', 'manual', false, true],
			$input->transformValue($input->element));
		$this->assertSame([42, 'A', 'Option A', '', true, true],
			$select->transformValue($select->element));
		$this->assertSame([42, 'LOCKED', 'Locked option', 'manual', false, false],
			$enabled->transformValue($enabled->element));
		$this->assertFalse($input->isBulkEditable());
	}

	public function test_cell_renderer_disables_whichever_control_is_rendered() : void
	{
		$field = $this->makeField('LOCKED', 'manual', true);
		$render = $field->getCustomColumnDefSingleResult();

		$this->assertStringContainsString('item[5] === true', $render);
		$this->assertStringContainsString('item[4] !== true', $render);
		$this->assertStringContainsString("item.replace('<input ', '<input disabled ')", $render);
		$this->assertStringContainsString("item.replace('<select ', '<select disabled ')", $render);
	}

	public function test_inline_editor_disables_its_select_only_when_required() : void
	{
		$disabled = $this->makeField('LOCKED', 'manual', true);
		$enabled = $this->makeField('LOCKED', 'manual', false);

		$this->assertStringStartsWith('<select disabled ', $disabled->getInlineEditHtml());
		$this->assertStringStartsWith('<select ', $enabled->getInlineEditHtml());
		$this->assertStringNotContainsString('<select disabled ', $enabled->getInlineEditHtml());
	}

	public function test_missing_method_fails_clearly() : void
	{
		$field = $this->makeField('LOCKED', 'manual', true);
		$field->conditionallyDisabledMethod = 'missingMethod';

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('conditionallyDisabledMethod');
		$field->transformValue($field->element);
	}

	public function test_field_type_resolves_to_the_new_subclass() : void
	{
		$this->assertSame(
			DatatableFieldSelectOrInputConditionallyDisabled::class,
			DatatableField::getClassNameByType('editor.selectOrInputConditionallyDisabled')
		);
	}
}

class SelectOrInputConditionallyDisabledProbe extends DatatableFieldSelectOrInputConditionallyDisabled
{
	public function getPossibleEnumValuesArray()
	{
		return ['A' => 'Option A', 'LOCKED' => 'Locked option'];
	}

	protected function substituteUrlParameter()
	{
		return "let url = '/update';";
	}

	public function getInlineEditUpdateUrl() : string
	{
		return '/update/42';
	}

	public function getHtmlClassesString()
	{
		return '';
	}
}
