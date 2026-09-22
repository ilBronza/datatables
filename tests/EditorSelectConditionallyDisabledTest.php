<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldSelectConditionallyDisabled;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EditorSelectConditionallyDisabledTest extends TestCase
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

	private function makeField(bool $disabled) : SelectConditionallyDisabledProbe
	{
		$field = (new ReflectionClass(SelectConditionallyDisabledProbe::class))->newInstanceWithoutConstructor();
		$field->name = 'vehicle_id';
		$field->parameter = 'vehicle_id';
		$field->conditionallyDisabledMethod = 'isVehicleSelectDisabled';
		$field->element = new class($disabled) {
			public int $vehicle_id = 7;

			public function __construct(public bool $disabled) {}

			public function getKey() : int
			{
				return 42;
			}

			public function isVehicleSelectDisabled() : bool
			{
				return $this->disabled;
			}
		};

		return $field;
	}

	public function test_select_can_be_disabled_for_one_row_and_enabled_for_another() : void
	{
		$disabled = $this->makeField(true);
		$enabled = $this->makeField(false);

		$this->assertSame([42, 7, 'Golf', true], $disabled->transformValue($disabled->element));
		$this->assertSame([42, 7, 'Golf', false], $enabled->transformValue($enabled->element));
		$this->assertStringStartsWith('<select disabled ', $disabled->getInlineEditHtml());
		$this->assertStringStartsWith('<select ', $enabled->getInlineEditHtml());
		$this->assertStringNotContainsString('<select disabled ', $enabled->getInlineEditHtml());
		$this->assertFalse($disabled->isBulkEditable());
	}

	public function test_cell_renderer_uses_only_the_fourth_value_for_disabled() : void
	{
		$field = $this->makeField(true);
		$render = $field->getCustomColumnDefSingleResult();

		$this->assertStringContainsString('item[3] === true', $render);
		$this->assertStringContainsString("item.replace('<select ', '<select disabled ')", $render);
		$this->assertStringContainsString('data-populated=', $render);
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
			DatatableFieldSelectConditionallyDisabled::class,
			DatatableField::getClassNameByType('editor.selectConditionallyDisabled')
		);
	}
}

class SelectConditionallyDisabledProbe extends DatatableFieldSelectConditionallyDisabled
{
	public function getPossibleEnumValuesArray()
	{
		return [7 => 'Golf'];
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
