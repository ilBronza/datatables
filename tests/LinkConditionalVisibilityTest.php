<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldLink;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LinkConditionalVisibilityTest extends TestCase
{
	private function makeField() : DatatableFieldLink
	{
		$field = (new ReflectionClass(DatatableFieldLink::class))->newInstanceWithoutConstructor();
		$field->name = 'order';
		$field->function = 'getShowUrl';
		$field->conditionallyDisabledMethod = 'isLinkDisabled';

		return $field;
	}

	private function makeRow(bool $disabled) : object
	{
		return new class($disabled) {
			public int $urlCalls = 0;
			public string $label = 'Order 42';

			public function __construct(public bool $disabled) {}

			public function isLinkDisabled() : bool
			{
				return $this->disabled;
			}

			public function getShowUrl() : string
			{
				$this->urlCalls++;
				return '/orders/42';
			}
		};
	}

	public function test_hidden_row_returns_no_link_and_does_not_generate_its_url() : void
	{
		$field = $this->makeField();
		$hidden = $this->makeRow(true);
		$visible = $this->makeRow(false);

		$this->assertNull($field->transformValue($hidden));
		$this->assertSame(0, $hidden->urlCalls);
		$this->assertSame('/orders/42', $field->transformValue($visible));
		$this->assertSame(1, $visible->urlCalls);
		$this->assertNull($field->transformValue(null));
	}

	public function test_hidden_row_with_text_returns_the_empty_pair_expected_by_the_renderer() : void
	{
		$field = $this->makeField();
		$field->textParameter = 'label';
		$hidden = $this->makeRow(true);
		$visible = $this->makeRow(false);

		$this->assertSame([null, null], $field->transformValue($hidden));
		$this->assertSame(0, $hidden->urlCalls);
		$this->assertSame(['/orders/42', 'Order 42'], $field->transformValue($visible));
		$this->assertStringContainsString('if(item[0])', $field->getCustomColumnDefSingleResult());
	}

	public function test_omitting_the_condition_keeps_the_existing_link_behavior() : void
	{
		$field = $this->makeField();
		$field->conditionallyDisabledMethod = null;
		$row = $this->makeRow(true);

		$this->assertSame('/orders/42', $field->transformValue($row));
		$this->assertSame(1, $row->urlCalls);
	}

	public function test_missing_condition_method_fails_clearly() : void
	{
		$field = $this->makeField();
		$field->conditionallyDisabledMethod = 'missingMethod';

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('conditionallyDisabledMethod');
		$field->transformValue($this->makeRow(true));
	}
}
