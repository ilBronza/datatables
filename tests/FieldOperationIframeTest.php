<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFieldOperation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\TestCase;

class FieldOperationIframeTest extends TestCase
{
    public function test_iframe_link_adds_the_query_flag_before_a_fragment() : void
    {
        $operation = DatatablesFieldOperation::createFromParameters([
            'type' => 'link',
            'target' => 'iframe',
            'link' => '/notes/__ROW_ID__?tab=all#form',
        ], 'note');

        $this->assertSame('/notes/__ROW_ID__?tab=all&iframed=true#form', $operation->getRenderedLink());
        $this->assertSame('/notes/__ROW_ID__?tab=all#form', $operation->getLink());

        $operation->setLink('/notes/__ROW_ID__?iframed=true');

        $this->assertSame('/notes/__ROW_ID__?iframed=true', $operation->getRenderedLink());

        $operation->setLink('/notes/__ROW_ID__?iframed=false&tab=all');

        $this->assertSame('/notes/__ROW_ID__?iframed=true&tab=all', $operation->getRenderedLink());
    }

    public function test_iframe_operation_renders_a_uikit_lightbox_link() : void
    {
        $operation = DatatablesFieldOperation::createFromParameters([
            'type' => 'link',
            'faIcon' => 'clipboard',
            'target' => 'iframe',
            'link' => '/notes/__ROW_ID__',
        ], 'note');

        $field = new class($operation) {
            public function __construct(private DatatablesFieldOperation $operation) {}
            public function getFieldOperations() : array { return ['note' => $this->operation]; }
        };
        $__env = new class {
            public function addLoop($items) : void {}
            public function incrementLoopIndices() : void {}
            public function popLoop() : void {}
            public function getLastLoop() : ?object { return null; }
        };
        $compiler = new BladeCompiler(new Filesystem(), __DIR__);
        $source = file_get_contents(dirname(__DIR__) . '/resources/views/datatablesFields/___tableSingleSpecFieldOperation.blade.php');

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

        $this->assertMatchesRegularExpression('/<span uk-lightbox>\s*<a/', $html);
        $this->assertStringContainsString("href='/notes/__ROW_ID__?iframed=true'", $html);
        $this->assertStringContainsString("data-type='iframe'", $html);
        $this->assertStringContainsString("class='fa-solid fa-clipboard'", $html);
        $this->assertStringNotContainsString("target='iframe'", $html);
    }
}
