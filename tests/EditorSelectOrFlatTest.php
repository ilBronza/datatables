<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldSelectOrFlat;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EditorSelectOrFlatTest extends TestCase
{
	private function makeField(?string $code, ?string $displayCode = null, bool $usesEditorProperty = false) : SelectOrFlatProbe
	{
		$field = (new ReflectionClass(SelectOrFlatProbe::class))->newInstanceWithoutConstructor();
		$field->name = 'code';
		$field->parameter = 'code';
		$field->nullString = 'nd';

		if ($usesEditorProperty)
			$field->editorProperty = 'display_code';

		$field->element = new class($code, $displayCode) {
			public ?string $code;
			public ?string $display_code;

			public function __construct(?string $code, ?string $displayCode)
			{
				$this->code = $code;
				$this->display_code = $displayCode;
			}

			public function getKey() : int
			{
				return 42;
			}
		};

		return $field;
	}

	public function test_a_known_value_continues_to_render_as_a_select() : void
	{
		$field = $this->makeField('A');

		$this->assertSame([42, 'A', 'Option A', true], $field->transformValue($field->element));
		$this->assertStringContainsString('item[3] !== true', $field->getCustomColumnDefSingleResult());
		$this->assertStringContainsString('data-populated="false"', $field->getCustomColumnDefSingleResult());
	}

	public function test_a_free_value_remains_flat() : void
	{
		$field = $this->makeField('MANUAL');

		$this->assertSame([42, 'MANUAL', 'nd', false], $field->transformValue($field->element));
	}

	public function test_an_empty_value_falls_back_to_the_select_value() : void
	{
		$field = $this->makeField('A', null, true);

		$this->assertSame([42, 'A', 'Option A', true], $field->transformValue($field->element));
	}
}

class SelectOrFlatProbe extends DatatableFieldSelectOrFlat
{
	public function getPossibleEnumValuesArray()
	{
		return ['A' => 'Option A', 'B' => 'Option B'];
	}

	protected function substituteUrlParameter()
	{
		return "let url = '/update';";
	}

	public function getHtmlClassesString()
	{
		return '';
	}
}
