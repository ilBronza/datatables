<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Buttons\FieldsGroupHoldButton;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class FieldsGroupHoldButtonTest extends TestCase
{
    public function test_it_exposes_the_hold_button_definition() : void
    {
        $container = Container::getInstance();
        $previousTranslator = $container->bound('translator')
            ? $container->make('translator') : null;
        $container->instance('translator', new class
        {
            public function get(string $key) : string
            {
                return $key;
            }
        });

        try {
            $button = new FieldsGroupHoldButton('gross_costs', [
                'activeText' => 'Rilascia per nascondere',
                'inactiveText' => 'Tieni premuto per mostrare',
            ]);

            $this->assertSame([
                'extend' => 'fieldsGroupHold',
                'fieldGroup' => 'gross_costs',
                'activeText' => 'Rilascia per nascondere',
                'inactiveText' => 'Tieni premuto per mostrare',
            ], $button->getDatatablesDefinition());
        } finally {
            if ($previousTranslator)
                $container->instance('translator', $previousTranslator);
            else
                $container->forgetInstance('translator');
        }
    }
}
