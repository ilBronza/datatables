<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldTextConditionallyDisabled;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EditorTextConditionallyDisabledTest extends TestCase
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

	private function makeField(bool $disabled) : TextConditionallyDisabledProbe
	{
		$field = (new ReflectionClass(TextConditionallyDisabledProbe::class))->newInstanceWithoutConstructor();
		$field->name = 'title';
		$field->parameter = 'title';
		$field->conditionallyDisabledMethod = 'isTitleDisabled';
		$field->element = new class($disabled) {
			public string $title = 'Example';

			public function __construct(public bool $disabled) {}

			public function getKey() : int
			{
				return 42;
			}

			public function isTitleDisabled() : bool
			{
				return $this->disabled;
			}
		};

		return $field;
	}

	public function test_text_can_be_disabled_for_one_row_and_enabled_for_another() : void
	{
		$disabled = $this->makeField(true);
		$enabled = $this->makeField(false);

		$this->assertSame([42, 'Example', true], $disabled->transformValue($disabled->element));
		$this->assertSame([42, 'Example', false], $enabled->transformValue($enabled->element));
		$this->assertStringStartsWith('<input disabled ', $disabled->getInlineEditHtml());
		$this->assertStringStartsWith('<input ', $enabled->getInlineEditHtml());
		$this->assertStringNotContainsString('<input disabled ', $enabled->getInlineEditHtml());
		$this->assertFalse($disabled->isBulkEditable());
	}

	public function test_cell_renderer_uses_the_third_value_for_disabled() : void
	{
		$field = $this->makeField(true);
		$render = $field->getCustomColumnDefSingleResult();

		$this->assertStringContainsString('item[2] === true', $render);
		$this->assertStringContainsString("item.replace('<input ', '<input disabled ')", $render);
		$this->assertStringContainsString('ib-editor-text', $render);
	}

	public function test_missing_method_fails_clearly() : void
	{
		$field = $this->makeField(true);
		$field->conditionallyDisabledMethod = 'missingMethod';

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('conditionallyDisabledMethod');
		$field->transformValue($field->element);
	}

	public function test_field_type_resolves_to_the_new_subclass() : void
	{
		$this->assertSame(
			DatatableFieldTextConditionallyDisabled::class,
			DatatableField::getClassNameByType('editor.textConditionallyDisabled')
		);
	}
}

class TextConditionallyDisabledProbe extends DatatableFieldTextConditionallyDisabled
{
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
