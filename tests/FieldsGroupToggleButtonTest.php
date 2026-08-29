<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Buttons\FieldsGroupToggleButton;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FieldsGroupToggleButtonTest extends TestCase
{
    public function test_it_exposes_a_declarative_datatables_definition() : void
    {
        $button = new FieldsGroupToggleButton('economic_minor_details', [
            'text' => 'Dettagli economici',
            'activeText' => 'Mostra dettagli economici',
            'inactiveText' => 'Nascondi dettagli economici',
            'classes' => ['uk-button', 'uk-button-default'],
            'attributes' => ['title' => 'Mostra o nascondi i dettagli'],
        ]);

        $button->setTableId('orders');

        $this->assertSame('economic_minor_details', $button->getFieldGroup());
        $this->assertSame([
            'extend' => 'fieldsGroupToggle',
            'fieldGroup' => 'economic_minor_details',
            'activeText' => 'Mostra dettagli economici',
            'inactiveText' => 'Nascondi dettagli economici',
        ], $button->getDatatablesDefinition());
        $this->assertSame('orders', $button->getData()['tableid']);
        $this->assertNull($button->renderJsMethod());
    }

    public function test_it_rejects_an_empty_group_name() : void
    {
        $this->expectException(InvalidArgumentException::class);

        new FieldsGroupToggleButton('   ');
    }
}
