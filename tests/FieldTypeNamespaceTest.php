<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use PHPUnit\Framework\TestCase;

class FieldTypeNamespaceTest extends TestCase
{
	public function test_new_field_types_preserve_the_ib_dt_v2_namespace_case() : void
	{
		$this->assertSame(
			'IlBronza\\Datatables\\DatatablesFields\\ibDtV2\\Editor\\DatatableFieldText',
			DatatableField::getClassNameByType('ibDtV2.editor.text')
		);

		$this->assertSame(
			'ibDtV2\\Editor\\DatatableFieldText',
			DatatableField::getClassNameByType('ibDtV2.editor.text', false)
		);
	}

	public function test_existing_field_types_keep_their_namespaces() : void
	{
		$this->assertSame(
			'IlBronza\\Datatables\\DatatablesFields\\Editor\\DatatableFieldText',
			DatatableField::getClassNameByType('editor.text')
		);

		$this->assertSame(
			'IlBronza\\Datatables\\DatatablesFields\\DatatableFieldText',
			DatatableField::getClassNameByType('text')
		);
	}

	public function test_app_field_types_can_use_the_same_namespace() : void
	{
		$this->assertSame(
			'App\\Providers\\DatatablesFields\\ibDtV2\\Editor\\DatatableFieldText',
			DatatableField::getClassNameByType('app::ibDtV2::editor::text')
		);
	}
}
