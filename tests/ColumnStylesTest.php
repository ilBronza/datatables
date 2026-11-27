<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Support\ColumnStyles;
use PHPUnit\Framework\TestCase;

class ColumnStylesTest extends TestCase
{
    public function test_shared_browser_and_server_normalization_fixtures() : void
    {
        $fixtures = json_decode(file_get_contents(__DIR__ . '/fixtures/columnStyles.json'), true);
        foreach ($fixtures as $fixture)
            $this->assertSame($fixture['normalized'], ColumnStyles::normalize($fixture['selectors']), $fixture['name']);
    }

    public function test_legacy_branches_are_scoped_independently() : void
    {
        $css = ColumnStyles::css('orders', 'customer', ['td a i + td a span' => ['color' => '#123456']]);
        $this->assertSame(2, substr_count($css, 'td[class~="customer"]'));
        $this->assertStringContainsString(' a i,', $css);
        $this->assertStringContainsString(' a span{color:#123456!important}', $css);
        $this->assertStringNotContainsString(' + ', $css);
    }

    public function test_overflow_and_fixed_header_widths_compile_to_real_css() : void
    {
        $css = ColumnStyles::css('orders', 'customer', ['td' => ['width' => 240, 'text-overflow-mode' => 'tooltip', '__tooltip' => true]]);
        $this->assertStringContainsString('text-overflow:ellipsis!important', $css);
        $this->assertStringContainsString('white-space:nowrap!important', $css);
        $this->assertStringContainsString('table[data-realid="orders"] th[class~="customer"]', $css);
        $this->assertStringNotContainsString('__tooltip', $css);
        $this->assertStringNotContainsString('text-overflow-mode', $css);
    }

    public function test_styles_cannot_escape_the_style_element() : void
    {
        $css = ColumnStyles::css('orders</style>', 'customer"><script>', ['td' => ['color' => 'red']]);
        $this->assertStringNotContainsString('<', $css);
        $this->assertStringNotContainsString('>', $css);
        $this->assertSame('', ColumnStyles::css('orders', 'customer', []));
    }

    public function test_field_background_accepts_a_single_color() : void
    {
        $this->assertNull(ColumnStyles::fieldBackgroundColor(null));
        $this->assertSame('rgba(12, 34, 56, .5)', ColumnStyles::fieldBackgroundColor(' rgba(12, 34, 56, .5) '));
        $this->expectException(\InvalidArgumentException::class);
        ColumnStyles::fieldBackgroundColor('red; display: none');
    }

    public function test_visible_overflow_keeps_fixed_width_and_overrides_link_truncation() : void
    {
        $css = ColumnStyles::css('users', 'email', ['td' => ['width' => 150, 'text-overflow-mode' => 'visible']]);
        $this->assertStringContainsString('width:150px!important', $css);
        $this->assertStringContainsString('white-space:nowrap!important;overflow:visible!important;text-overflow:clip!important;overflow-wrap:normal!important', $css);
        $this->assertStringContainsString(':is(a,span,div,li)', $css);
        $this->assertStringNotContainsString('overflow:hidden', $css);
    }
}
