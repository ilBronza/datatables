<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\Support\HeaderStyles;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HeaderStylesTest extends TestCase
{
    public function test_overrides_are_opt_in_per_table_and_can_be_reset() : void
    {
        $table = (new ReflectionClass(Datatables::class))->newInstanceWithoutConstructor();
        $other = (new ReflectionClass(Datatables::class))->newInstanceWithoutConstructor();
        $this->assertSame([], $table->getHeaderStyles());
        $this->assertSame('', HeaderStyles::css('orders', $table->getHeaderStyles()));

        $styles = ['white-space' => 'normal', 'text-wrap' => 'wrap'];
        $this->assertSame($table, $table->setHeaderStyles($styles));
        $this->assertSame($styles, $table->getHeaderStyles());
        $this->assertSame([], $other->getHeaderStyles());
        $table->setHeaderStyles([]);
        $this->assertSame([], $table->getHeaderStyles());
        $table->bind(['headerStyles' => $styles]);
        $this->assertSame($styles, $table->getHeaderStyles());
    }

    public function test_blade_emits_scoped_styles_for_titles_and_fixed_header_copies() : void
    {
        $files = new Filesystem();
        $cache = sys_get_temp_dir() . '/datatables-header-styles-' . bin2hex(random_bytes(8));
        $files->ensureDirectoryExists($cache);

        try {
            $engines = new EngineResolver();
            $engines->register('blade', fn () => new CompilerEngine(new BladeCompiler($files, $cache), $files));
            $views = new Factory($engines, new FileViewFinder($files, []), new Dispatcher());
            $views->setContainer(new Container());
            $views->addNamespace('datatables', dirname(__DIR__) . '/resources/views');
            $table = $this->getMockBuilder(Datatables::class)->disableOriginalConstructor()
                ->onlyMethods(['getId', 'getFields', 'getDatatableUserData'])->getMock();
            $table->method('getId')->willReturn('orders');
            $table->method('getFields')->willReturn(collect());
            $table->method('getDatatableUserData')->willReturn((object) ['columnsSettings' => []]);
            $render = fn () => $views->make('datatables::datatablesFields.__tableSingleSpecCss', ['table' => $table])->render();
            $default = $render();

            $table->setHeaderStyles(['white-space' => 'normal', 'text-wrap' => 'wrap', 'font-family' => '"Open Sans", sans-serif']);
            $html = $render();
            $this->assertStringContainsString('table[id="orders"] > thead > tr.columnsintestations > th .dt-column-title', $html);
            $this->assertStringContainsString('table[data-realid="orders"] > thead > tr.columnsintestations > th .dt-column-title > div', $html);
            $this->assertStringContainsString('white-space:normal!important;text-wrap:wrap!important', $html);
            $this->assertStringContainsString('font-family:"Open Sans", sans-serif!important', $html);
            $this->assertStringNotContainsString('tbody', $html);
            $this->assertStringNotContainsString('tr.columns >', $html);
            $this->assertStringNotContainsString('tr.mainheader >', $html);
            $table->setHeaderStyles([]);
            $this->assertSame($default, $render());
        } finally {
            $files->deleteDirectory($cache);
        }
    }

    public function test_table_ids_are_escaped_and_existing_important_is_not_duplicated() : void
    {
        $css = HeaderStyles::css('orders"></style>', ['color' => 'red !important']);
        $this->assertStringNotContainsString('<', $css);
        $this->assertStringNotContainsString('>', substr($css, 0, strpos($css, ']')));
        $this->assertSame(1, substr_count($css, '!important'));
    }

    public function test_values_cannot_inject_another_rule_or_close_the_style_element() : void
    {
        foreach (['red;display:none', 'red}</style>', "red\nbody{}"] as $value) {
            try {
                HeaderStyles::css('orders', ['color' => $value]);
                $this->fail('Invalid headerStyles value was accepted.');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('headerStyles', $exception->getMessage());
            }
        }
    }
}
