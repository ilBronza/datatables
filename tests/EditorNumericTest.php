<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\FieldFactory;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldNumeric;
use IlBronza\Datatables\DatatablesFields\ibDtV2\Editor\DatatableFieldText;
use IlBronza\Datatables\Support\DecimalValue;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EditorNumericTest extends TestCase
{
	private $previousContainer;
	private $previousFacade;

	protected function setUp() : void
	{
		parent::setUp();
		$this->previousContainer = Container::getInstance();
		$this->previousFacade = Facade::getFacadeApplication();
		$container = new Container();
		$container->instance('config', new Repository(['datatables' => ['replace_model_id_string' => '__ROW_ID__']]));
		Container::setInstance($container);
		Facade::setFacadeApplication($container);
	}

	protected function tearDown() : void
	{
		Container::setInstance($this->previousContainer);
		Facade::setFacadeApplication($this->previousFacade);
		parent::tearDown();
	}

	private function field(array $parameters = []) : DatatableFieldNumeric
	{
		$table = new Datatables();
		$table->setRowIdIndex(2);
		$table->placeholderElement = new class {
			public function getEditorUpdateUrl() : string { return '/records/__ROW_ID__'; }
		};
		return FieldFactory::createByType('amount', 'ibDtV2.editor.numeric', $parameters, 1, null, $table);
	}

	public function test_numeric_reuses_text_and_serializes_decimal_strings_without_rounding() : void
	{
		$field = $this->field(['decimals' => 2]);
		$this->assertInstanceOf(DatatableFieldText::class, $field);
		foreach ([null, '0', '9007199254740993.123456789012345678900', '-0.00000000000000001'] as $value)
			$this->assertSame($value, $field->transformValue((object) ['amount' => $value]));
		$this->assertSame('0.0000001234500', DecimalValue::canonical('1.234500e-7'));
		$this->assertSame('1200000', DecimalValue::canonical('12e5'));
		$this->assertSame('-0.02', DecimalValue::canonical('-.02'));
		$this->assertSame(1, DecimalValue::compare('9007199254740993.01', '9007199254740993.001'));
		$this->assertSame(-1, DecimalValue::compare('-9007199254740993', '-9007199254740992'));
		$this->assertSame('ibDtV2.editor.numeric', $field->getInlineFieldType());
		$this->assertSame('ib-decimal', $field->datatableType);
	}

	public function test_numeric_options_are_shared_on_headers_and_inline_wrappers_with_raw_values() : void
	{
		$field = $this->field(['translatedName' => 'Amount', 'decimals' => 2, 'maxDecimals' => 6, 'fixedDecimals' => false,
			'min' => '9007199254740993.00', 'max' => '9007199254740994', 'step' => '0.05', 'currency' => 'EUR',
			'inputAttributes' => ['class' => 'custom']]);
		$options = json_decode($field->getEditorDataAttributes()['ib-dt-number-options'], true);
		$this->assertSame('9007199254740993.00', $options['min']);
		$this->assertSame('9007199254740994', $options['max']);
		$this->assertSame('0.05', $options['step']);
		$this->assertSame('decimal', $field->getEditorDataAttributes()['ib-dt-value-type']);
		$field->table->fields = collect([$field]);
		$field->table->bulkEdit = true;
		$this->assertSame($options, $field->table->getBulkEditableFieldsArray()[0]['numericOptions']);
		$html = $field->setInlineEditElement(new NumericEditorRecord('9007199254740993.123456'))->getInlineEditHtml();
		$this->assertStringContainsString('ib-dt-numeric-editor custom', $html);
		$this->assertStringContainsString('inputmode="decimal"', $html);
		$this->assertStringContainsString('value="9007199254740993.123456"', $html);
		$this->assertStringContainsString('data-ib-dt-number-options=', $html);
		$this->assertStringContainsString('EUR', $html);
		$this->assertStringNotContainsString('data-url=', $html);
		$this->assertStringContainsString('numbers.format', $field->getCustomColumnDef());
	}

	public function test_numeric_inherits_conditional_disabled_related_targets_and_inline_create() : void
	{
		$field = $this->field(['disabledIf' => 'isLocked', 'editorTarget' => 'element']);
		$model = new NumericEditorRecord('12.3456');
		$this->assertSame(['value' => '12.3456', 'disabled' => true, 'targetId' => 42], $field->transformValue($model));
		$this->assertFalse($field->isBulkEditable());
		$html = $field->setInlineCreateElement($model, '/records')->getInlineEditHtml();
		$this->assertStringContainsString('ib-datatable-inline-create-field', $html);
		$this->assertStringContainsString(' disabled=""', $html);
		$this->assertStringContainsString('12.3456', $html);
	}

	public function test_invalid_numeric_options_fail_explicitly() : void
	{
		foreach ([['decimals' => -1], ['maxDecimals' => 101], ['decimalSeparator' => ';'],
			['decimalSeparator' => '.', 'thousandsSeparator' => '.'], ['currency' => '€'],
			['step' => '0'], ['step' => '-0.01'], ['min' => '9007199254740993.01', 'max' => '9007199254740993.001'], ['min' => 'abc']] as $parameters)
		{
			try { $this->field($parameters); $this->fail('Invalid numeric options accepted: ' . json_encode($parameters)); }
			catch (InvalidArgumentException $exception) { $this->assertNotSame('', $exception->getMessage()); }
		}
	}
}

class NumericEditorRecord
{
	public function __construct(public ?string $amount) {}
	public function getKey() : int { return 42; }
	public function isLocked() : bool { return true; }
}
