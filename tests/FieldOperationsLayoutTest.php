<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\Iterators\DatatableFieldEach;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FieldOperationsLayoutTest extends TestCase
{
    private string $cachePath;
    private Factory $views;

    protected function setUp() : void
    {
        parent::setUp();

        $files = new Filesystem();
        $this->cachePath = sys_get_temp_dir() . '/datatables-field-operations-' . uniqid();
        $files->ensureDirectoryExists($this->cachePath);
        $compiler = new BladeCompiler($files, $this->cachePath);
        $engines = new EngineResolver();
        $engines->register('blade', fn () => new CompilerEngine($compiler, $files));
        $this->views = new Factory($engines, new FileViewFinder($files, []), new Dispatcher());
        $this->views->setContainer(new Container());
        $this->views->addNamespace('datatables', dirname(__DIR__) . '/resources/views');
    }

    protected function tearDown() : void
    {
        (new Filesystem())->deleteDirectory($this->cachePath);
        parent::tearDown();
    }

    private function field() : DatatableField
    {
        $field = (new ReflectionClass(DatatableField::class))->newInstanceWithoutConstructor();
        $field->name = 'product';
        $field->fieldOperations = [
            'show' => ['type' => 'link', 'link' => '/products/__ID__'],
            'edit' => ['type' => 'link', 'link' => '/products/__ID__/edit'],
            'notes' => ['type' => 'link', 'target' => 'iframe', 'link' => '/products/__ID__/notes'],
        ];

        return $field;
    }

    private function render(DatatableField $field) : string
    {
        $table = new class($field) {
            public function __construct(private DatatableField $field) {}
            public function getId() : string { return 'products'; }
            public function getFields() : array { return [$this->field]; }
        };

        return $this->views->make('datatables::datatablesFields.__tableSingleSpecFieldOperations', ['table' => $table])->render();
    }

    private function operationMarkup(string $script) : string
    {
        $this->assertSame(1, preg_match('/\.html\((.*?)\);/', $script, $matches));

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_popup_is_horizontal_by_default_and_when_explicitly_disabled() : void
    {
        $field = $this->field();
        $default = $this->render($field);
        $field->setParameters(['fieldOperationsVertical' => false]);

        $this->assertSame($default, $this->render($field));
        $this->assertStringContainsString(".toggleClass('fieldoperations-vertical', false)", $default);
        $this->assertStringContainsString('.css(\'width\', "90px")', $default);
    }

    public function test_enabled_option_renders_a_vertical_popup_with_all_operations() : void
    {
        $field = $this->field();
        $field->setParameters(['fieldOperationsVertical' => true]);
        $html = $this->render($field);

        $this->assertStringContainsString(".toggleClass('fieldoperations-vertical', true)", $html);
        $this->assertStringContainsString('.css(\'width\', "30px")', $html);
        $markup = $this->operationMarkup($html);
        $this->assertStringContainsString("href='/products/__ID__'", $markup);
        $this->assertStringContainsString("href='/products/__ID__/edit'", $markup);
        $this->assertStringContainsString("href='/products/__ID__/notes?iframed=true'", $markup);
        $this->assertStringContainsString('<span uk-lightbox>', $markup);
    }

    public function test_iterated_popup_uses_the_child_orientation_independently() : void
    {
        $field = (new ReflectionClass(DatatableFieldEach::class))->newInstanceWithoutConstructor();
        $field->name = 'products';
        $field->child = $this->field();
        $field->child->setParameters(['fieldOperationsVertical' => true]);

        $html = $this->render($field);

        $this->assertStringContainsString("td.products [data-cellindex]", $html);
        $this->assertStringContainsString(".toggleClass('fieldoperations-vertical', true)", $html);
        $this->assertStringContainsString('.css(\'width\', "30px")', $html);

        $field->setParameters(['fieldOperationsVertical' => true]);
        $field->child->setParameters(['fieldOperationsVertical' => false]);
        $horizontal = $this->render($field);

        $this->assertStringContainsString(".toggleClass('fieldoperations-vertical', false)", $horizontal);
        $this->assertStringContainsString('.css(\'width\', "90px")', $horizontal);
    }

    public function test_labels_are_escaped_and_the_popup_fits_their_text() : void
    {
        $field = $this->field();
        $label = "Apri \"scheda\" & <dettagli>\nseconda riga";
        $field->fieldOperations['show']['label'] = $label;

        foreach ([false, true] as $vertical)
        {
            $field->fieldOperationsVertical = $vertical;
            $script = $this->render($field);
            $markup = $this->operationMarkup($script);

            $this->assertSame($label, $field->getFieldOperations()['show']->getLabel());
            $this->assertStringContainsString('.css(\'width\', "max-content")', $script);
            $this->assertStringContainsString(e($label), $markup);
            $this->assertStringNotContainsString('<dettagli>', $markup);
            $this->assertStringNotContainsString($label, $script);
        }
    }

    public function test_see_field_operations_use_the_owning_row_id() : void
    {
        $field = $this->field();
        $field->type = 'links.see';

        $this->assertStringContainsString("item = [datatable.row($(this).closest('tr')).id(), item];", $this->render($field));
        $field->type = 'model.keyvalue';
        $this->assertStringNotContainsString("item = [datatable.row(", $this->render($field));
    }

    public function test_iterated_labels_use_the_child_configuration() : void
    {
        $field = (new ReflectionClass(DatatableFieldEach::class))->newInstanceWithoutConstructor();
        $field->name = 'products';
        $field->child = $this->field();
        $field->child->fieldOperationsVertical = true;
        $field->child->fieldOperations['notes']['label'] = 'Note';

        $script = $this->render($field);

        $this->assertStringContainsString(".toggleClass('fieldoperations-vertical', true)", $script);
        $this->assertStringContainsString('.css(\'width\', "max-content")', $script);
        $this->assertStringContainsString('Note', $this->operationMarkup($script));
    }
}
