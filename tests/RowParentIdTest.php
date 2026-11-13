<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Castables\Data;
use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\FieldFactory;
use IlBronza\Datatables\DatatablesFields\ibDtV2\DatatableFieldRowParentId;
use IlBronza\Datatables\DatatablesServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;

class RowParentIdTest extends TestCase
{
	private $previousContainer;
	private $previousFacade;

	protected function setUp() : void
	{
		$this->previousContainer = Container::getInstance();
		$this->previousFacade = Facade::getFacadeApplication();
		parent::setUp();
	}

	protected function tearDown() : void
	{
		try { parent::tearDown(); }
		finally {
			Container::setInstance($this->previousContainer);
			Facade::setFacadeApplication($this->previousFacade);
		}
	}

	protected function getPackageProviders($app) { return [DatatablesServiceProvider::class]; }

	public function test_factory_and_accessor_normalize_scalar_and_structured_cells() : void
	{
		$field = FieldFactory::createByType('row_parent_id', 'ibDtV2.rowParentId', [], 0);
		$this->assertInstanceOf(DatatableFieldRowParentId::class, $field);
		foreach ([null, 0, '001', 'order:12', 42] as $parent)
		{
			$model = new RowHierarchyRecord(['parent_reference' => $parent]);
			$this->assertSame(['value' => $parent, 'row_opened' => false], $field->transformValue($model));
			$model->parent_reference = ['value' => $parent, 'row_opened' => true];
			$this->assertSame(['value' => $parent, 'row_opened' => true], $field->transformValue($model));
		}
		$this->assertFalse($field->isEditor());
		$this->assertFalse($field->isFilterable());
		$this->assertFalse($field->getColumnDefs()['orderable']);
		$this->assertFalse($field->getColumnDefs()['searchable']);
		$this->assertTrue($field->getColumnDefs()['visible']);
		$this->assertStringContainsString('ibDtRowParent:', $field->getCustomColumnDef());
	}

	public function test_cell_rejects_non_boolean_default_state() : void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('row_opened must be a boolean');
		(new DatatableFieldRowParentId('row_parent_id'))->transformValue(
			new RowHierarchyRecord(['parent_reference' => ['value' => null, 'row_opened' => 'false']])
		);
	}

	public function test_cell_rejects_invalid_parent_reference() : void
	{
		$this->expectException(InvalidArgumentException::class);
		(new DatatableFieldRowParentId('row_parent_id'))->transformValue(
			new RowHierarchyRecord(['parent_reference' => ['row_opened' => true]])
		);
	}

	private function table(array $fields) : Datatables
	{
		$table = Datatables::create('hierarchy', ['main' => ['fields' => $fields]], fn () => collect([
			new RowHierarchyRecord(['id' => 1, 'name' => 'Root', 'parent_reference' => ['value' => null, 'row_opened' => true]]),
			new RowHierarchyRecord(['id' => 2, 'name' => 'Child', 'parent_reference' => 1]),
		]), false, null, RowHierarchyRecord::class);
		$table->datatableUserData = new Data();
		$table->canHideColumns = false;
		return $table;
	}

	public function test_presence_of_the_field_adds_a_hidden_primary_without_selection_or_controller_options() : void
	{
		$table = $this->table(['row_parent_id' => ['type' => 'ibDtV2.rowParentId'], 'name' => ['type' => 'flat']]);
		$this->assertNotNull($table->getRowIdIndex());
		$this->assertFalse($table->hasSelectRowCheckboxes());
		$table->setData();
		$this->assertSame(1, $table->getData()[0][$table->getRowIdIndex()]);
		$this->assertSame(['value' => null, 'row_opened' => true], $table->getData()[0][0]);
		$this->assertSame(['value' => 1, 'row_opened' => false], $table->getData()[1][0]);
		$table->parseColumnDefs();
		$table->parseOptions();
		$this->assertCount(3, $table->getFields());
		$this->assertCount(3, $table->customColumnDefs);
		$this->assertStringContainsString('data-field-name="row_parent_id"', view('datatables::uikit.header._header', ['table' => $table])->render());
	}

	public function test_existing_row_identity_is_preserved() : void
	{
		$table = $this->table(['mySelfPrimary' => ['type' => 'primary'], 'row_parent_id' => ['type' => 'ibDtV2.rowParentId']]);
		$this->assertSame(0, $table->getRowIdIndex());
		$this->assertCount(2, $table->getFields());
	}
}

class RowHierarchyRecord extends Model
{
	protected $guarded = [];
	public function getRowParentIdAttribute() { return $this->parent_reference; }
}
