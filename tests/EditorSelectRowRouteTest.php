<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldSelect;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldSelectCell;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EditorSelectRowRouteTest extends TestCase
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

	private function makeField(string $class = SelectRowRouteProbe::class) : DatatableFieldSelect
	{
		$field = (new ReflectionClass($class))->newInstanceWithoutConstructor();
		$field->name = 'vehicle_id';
		$field->parameter = 'vehicle_id';
		$field->nullString = 'Nessun veicolo';
		$field->element = new class {
			public int $vehicle_id = 7;

			public function getKey() : int
			{
				return 42;
			}

			public function getVehicleSelectLabel() : string
			{
				return 'Golf';
			}
		};

		return $field;
	}

	public function test_select_row_route_renders_the_current_label_without_loading_shared_options() : void
	{
		$field = $this->makeField();
		$field->possibleValuesRowRoute = '/orders/__ROW_ID__/vehicles';
		$field->possibleValuesRowLabelMethod = 'getVehicleSelectLabel';

		$this->assertSame([42, 7, 'Golf'], $field->transformValue($field->element));
		$this->assertSame([], $field->getHeaderData());
		$this->assertFalse($field->isBulkEditable());
		$this->assertSame(0, $field->possibleValuesCalls);

		$render = $field->getCustomColumnDefSingleResult();
		$this->assertStringContainsString('data-possible-values-row-route="/orders/__ROW_ID__/vehicles"', $render);
		$this->assertStringContainsString('data-possible-values-row-route-placeholder="__ROW_ID__"', $render);
		$this->assertStringContainsString("data-row-id=\"' + item[0] + '\"", $render);

		$inline = $field->getInlineEditHtml();
		$this->assertStringContainsString('data-row-id="42"', $inline);
		$this->assertStringContainsString('<option value="7" selected>Golf</option>', $inline);
		$this->assertSame(0, $field->possibleValuesCalls);
	}

	public function test_select_without_row_route_keeps_the_shared_options() : void
	{
		$field = $this->makeField();
		$field->nullable = false;

		$this->assertSame([42, 7, 'Golf'], $field->transformValue($field->element));
		$this->assertTrue($field->isBulkEditable());
		$this->assertSame(1, $field->possibleValuesCalls);

		$header = $field->getHeaderData();
		$this->assertSame([7 => 'Golf'], json_decode($header['possibleValuesLegacy'], true));
	}

	public function test_row_route_requires_a_label_method_on_the_row() : void
	{
		$field = $this->makeField();
		$field->possibleValuesRowRoute = '/orders/__ROW_ID__/vehicles';

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('possibleValuesRowLabelMethod');
		$field->transformValue($field->element);
	}

	public function test_select_cell_keeps_its_row_label_behavior_without_a_route() : void
	{
		$field = $this->makeField(DatatableFieldSelectCell::class);
		$field->possibleValuesRowLabelMethod = 'getVehicleSelectLabel';
		$field->possibleValuesArray = [7 => 'Golf'];
		$field->nullable = false;

		$this->assertSame([42, 7, 'Golf', [7 => 'Golf']],
			$field->transformValue($field->element));
	}

	public function test_select_cell_still_supports_the_row_route() : void
	{
		$field = $this->makeField(DatatableFieldSelectCell::class);
		$field->possibleValuesRowRoute = '/orders/__ROW_ID__/vehicles';
		$field->possibleValuesRowLabelMethod = 'getVehicleSelectLabel';

		$this->assertSame([42, 7, 'Golf', null], $field->transformValue($field->element));
		$this->assertSame([], $field->getHeaderData());
	}
}

class SelectRowRouteProbe extends DatatableFieldSelect
{
	public int $possibleValuesCalls = 0;

	public function getPossibleEnumValuesArray()
	{
		$this->possibleValuesCalls++;

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
