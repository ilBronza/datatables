<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Datatables;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use PHPUnit\Framework\TestCase;

class HeaderStylesToggleTest extends TestCase
{
    private Container $previousContainer;
    private string $cachePath;
    private Factory $views;
    private Datatables $table;
    private array $settings = [];

    protected function setUp() : void
    {
        parent::setUp();
        $this->previousContainer = Container::getInstance();
        $container = new Container();
        Container::setInstance($container);
        $container->instance('config', new Repository(['datatables' => ['defaultButtons' => []]]));

        $files = new Filesystem();
        $this->cachePath = sys_get_temp_dir() . '/datatables-header-toggle-' . bin2hex(random_bytes(8));
        $files->ensureDirectoryExists($this->cachePath);
        $loader = new FileLoader($files, dirname(__DIR__) . '/resources/lang');
        $loader->addNamespace('datatables', dirname(__DIR__) . '/resources/lang');
        $container->instance('translator', new Translator($loader, 'it'));
        $engines = new EngineResolver();
        $engines->register('blade', fn () => new CompilerEngine(new BladeCompiler($files, $this->cachePath), $files));
        $this->views = new Factory($engines, new FileViewFinder($files, []), new Dispatcher());
        $this->views->setContainer($container);
        $this->views->addNamespace('datatables', dirname(__DIR__) . '/resources/views');
        $this->table = $this->getMockBuilder(Datatables::class)->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getDatatableUserData'])->getMock();
        $this->table->method('getId')->willReturn('orders');
        $this->table->method('getDatatableUserData')->willReturnCallback(fn () => (object) ['uiSettings' => $this->settings]);
    }

    protected function tearDown() : void
    {
        (new Filesystem())->deleteDirectory($this->cachePath);
        Container::setInstance($this->previousContainer);
        parent::tearDown();
    }

    private function render(string $partial) : string
    {
        return $this->views->make('datatables::datatablesFields.__tableSingleSpecHeaderStyles' . $partial, ['table' => $this->table])->render();
    }

    public function test_button_and_saved_override_are_absent_unless_enabled() : void
    {
        $defaults = require dirname(__DIR__) . '/config/datatables.php';
        $this->assertFalse($defaults['defaultButtons']['headerStyles']);
        $this->settings = ['headerStylesExpanded' => true];
        $this->assertFalse($this->table->hasHeaderStylesToggleButton());
        $this->assertSame('', trim($this->render('Button')));
        $this->assertSame('', trim($this->render('Mode')));
        config(['datatables.defaultButtons.headerStyles' => false]);
        $this->assertSame('', trim($this->render('Button')));
    }

    public function test_enabled_button_starts_compact_and_can_expand_only_its_own_titles() : void
    {
        config(['datatables.defaultButtons.headerStyles' => true]);
        $this->assertFalse($this->table->areHeaderStylesExpanded());
        $button = $this->render('Button');
        $mode = $this->render('Mode');
        $this->assertStringContainsString('Estendi intestazioni', $button);
        $this->assertStringContainsString("'aria-pressed': 'false'", $button);
        $this->assertStringContainsString('ib-header-styles-orders', $mode);
        $this->assertStringContainsString('white-space:nowrap!important', $mode);
        $this->assertStringContainsString('text-overflow:ellipsis!important', $mode);
        $this->assertStringContainsString('table[data-realid="orders"] > thead', $mode);
        $this->assertStringNotContainsString('tbody', $mode);
        $this->assertStringNotContainsString('min-width', $mode);
    }

    public function test_saved_choice_wins_over_the_controller_and_survives_new_rendering() : void
    {
        config(['datatables.defaultButtons.headerStyles' => true]);
        $this->table->setHeaderStyles(['white-space' => 'normal']);
        $this->assertTrue($this->table->areHeaderStylesExpanded());
        foreach ([false, 0, '0'] as $value) {
            $this->settings = ['headerStylesExpanded' => $value];
            $this->assertFalse($this->table->areHeaderStylesExpanded());
            $this->assertStringContainsString('text-overflow:ellipsis!important', $this->render('Mode'));
        }
        $this->settings = ['headerStylesExpanded' => '1'];
        $this->assertTrue($this->table->areHeaderStylesExpanded());
        $this->assertStringContainsString('Compatta intestazioni', $this->render('Button'));
        $this->assertStringContainsString('white-space:normal!important', $this->render('Mode'));
    }
}
