<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\Keyvalue\DatatableFieldKeyvalue;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DeleteFieldOperationTest extends TestCase
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
        $container->instance('translator', new class {
            public function get(string $key, array $replace = [], ?string $locale = null, bool $fallback = true) : string
            {
                return $key;
            }
        });
        Container::setInstance($container);
    }

    protected function tearDown() : void
    {
        Container::setInstance($this->previousContainer);
        parent::tearDown();
    }

    public function test_delete_operation_uses_the_model_delete_url_with_a_row_placeholder() : void
    {
        $field = (new ReflectionClass(DatatableFieldKeyvalue::class))->newInstanceWithoutConstructor();
        $field->modelClass = DeleteFieldOperationModel::class;
        $field->fieldOperations = ['delete'];

        $operation = $field->getFieldOperations()['delete'];

        $this->assertSame('delete', $operation->getType());
        $this->assertSame('/orders/__ROW_ID__', $operation->getLink());
        $this->assertSame('trash', $operation->getFaIcon());
    }

    public function test_delete_operation_can_use_an_explicit_url_and_icon() : void
    {
        $field = (new ReflectionClass(DatatableField::class))->newInstanceWithoutConstructor();
        $field->fieldOperations = [
            'delete' => [
                'type' => 'delete',
                'link' => '/custom/__ROW_ID__',
                'faIcon' => 'xmark',
            ],
        ];

        $operation = $field->getFieldOperations()['delete'];

        $this->assertSame('/custom/__ROW_ID__', $operation->getLink());
        $this->assertSame('xmark', $operation->getFaIcon());
    }

    public function test_delete_operation_without_a_model_or_explicit_url_is_rejected() : void
    {
        $field = (new ReflectionClass(DatatableField::class))->newInstanceWithoutConstructor();
        $field->fieldOperations = ['delete'];

        $this->expectException(InvalidArgumentException::class);
        $field->getFieldOperations();
    }

    public function test_delete_operation_renders_a_confirmed_delete_link() : void
    {
        $field = (new ReflectionClass(DatatableFieldKeyvalue::class))->newInstanceWithoutConstructor();
        $field->modelClass = DeleteFieldOperationModel::class;
        $field->fieldOperations = ['delete'];

        $source = file_get_contents(dirname(__DIR__) . '/resources/views/datatablesFields/___tableSingleSpecFieldOperation.blade.php');
        $compiler = new BladeCompiler(new Filesystem(), __DIR__);
        $__env = new class {
            public function addLoop($items) : void {}
            public function incrementLoopIndices() : void {}
            public function popLoop() : void {}
            public function getLastLoop() : ?object { return null; }
        };

        ob_start();
        try
        {
            eval('?>' . $compiler->compileString($source));
            $html = ob_get_contents();
        }
        finally
        {
            ob_end_clean();
        }

        $this->assertStringContainsString("href='/orders/__ROW_ID__'", $html);
        $this->assertStringContainsString("data-type='DELETE'", $html);
        $this->assertStringContainsString("data-returnconfirm='datatables::messages.areYouSureToDeleteThisObject'", $html);
        $this->assertStringContainsString("data-send-ajax-payload='false'", $html);
        $this->assertStringContainsString("class='fa-solid fa-trash'", $html);
    }
}

class DeleteFieldOperationModel
{
    public string $id;

    public static function make() : static
    {
        return new static();
    }

    public function getKeyName() : string
    {
        return 'id';
    }

    public function getDeleteUrl() : string
    {
        return '/orders/' . $this->id;
    }
}
