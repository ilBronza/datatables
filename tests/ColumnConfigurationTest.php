<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\Datatables;
use IlBronza\Datatables\DatatablesFields\DatatableField;
use IlBronza\Datatables\DatatablesFields\DatatableFieldBoolean;
use IlBronza\Datatables\DatatablesFields\DatatableFieldBooleanAlarm;
use IlBronza\Datatables\DatatablesFields\Editor\DatatableFieldToggle;
use IlBronza\Datatables\Castables\Data;
use IlBronza\Datatables\Support\ColumnConfiguration;
use IlBronza\Datatables\Support\ColumnStyles;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ColumnConfigurationTest extends TestCase
{
    private function field(string $class = DatatableFieldBoolean::class)
    {
        $field = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        $field->name = 'active';
        $field->index = 2;
        $field->width = '300px';
        return $field;
    }

    public function test_boolean_state_definitions_include_absent_states_and_custom_backend_icons() : void
    {
        $field = $this->field();
        $field->trueIcon = 'star';
        $field->nullable = false;
        $config = ColumnConfiguration::field($field);
        $this->assertSame('300px', $config['width']);
        $this->assertSame(['true', 'false', 'null'], array_keys($config['states']));
        $this->assertSame('star', $config['states']['true']['icon']);
        $parent = $this->field(DatatableField::class);
        $parent->child = $field;
        $this->assertSame($config['states'], ColumnConfiguration::field($parent)['states']);
    }

    public function test_boolean_renderers_target_each_state_without_replacing_value_or_alarm_semantics() : void
    {
        foreach ([DatatableFieldBoolean::class, DatatableFieldBooleanAlarm::class] as $class) {
            $field = $this->field($class);
            $script = $field->getCustomColumnDefSingleResult();
            foreach (['true', 'false', 'null'] as $state) {
                $this->assertStringContainsString('boolean-' . $state, $script);
                $this->assertStringContainsString('data-style-state=\\"' . $state . '\\"', $script);
            }
            $this->assertStringContainsString('window.ibDtColumnStyles.icon(meta.settings.nTable.id, "active"', $script);
            $this->assertStringContainsString('data-style-applied-icon=', $script);
            if ($class === DatatableFieldBooleanAlarm::class) $this->assertStringContainsString('ib-alarm', $script);
        }
    }

    public function test_user_width_is_independent_of_the_backend_default_and_descendant_sizes() : void
    {
        $data = new Data();
        $field = $this->field();
        $table = $this->getMockBuilder(Datatables::class)->disableOriginalConstructor()->onlyMethods(['getDatatableUserData'])->getMock();
        $table->method('getDatatableUserData')->willReturn($data);
        $field->table = $table;
        $data->addColumnSettings('active', 2, ['selectors' => ColumnStyles::normalize(['td' => ['width' => 150]])]);
        $this->assertSame('150px', $field->getWidth());
        $this->assertSame('300px', $field->getUnstyledWidth());
        $data->addColumnSettings('active', 4, ['selectors' => ['td span' => ['width' => '900px']]]);
        $this->assertSame('300px', $field->getWidth());
        $data->addColumnSettings('active', 4, ['selectors' => []]);
        $this->assertSame('300px', $field->getWidth());
    }

    public function test_toggle_renderer_retains_data_value_and_uses_the_root_column_for_nested_state_icons() : void
    {
        $field = $this->getMockBuilder(DatatableFieldToggle::class)->disableOriginalConstructor()
            ->onlyMethods(['substituteUrlParameter', 'getHtmlDataAttributesString', 'getParentDataIndexString'])->getMock();
        $field->method('substituteUrlParameter')->willReturn('');
        $field->method('getHtmlDataAttributesString')->willReturn('');
        $field->method('getParentDataIndexString')->willReturn('');
        $field->name = 'child';
        $field->nullable = true;
        $field->parent = $this->field();
        $script = $field->getCustomColumnDefSingleResult();
        $this->assertStringContainsString("data-value=\"' + item[1] + '\"", $script);
        $this->assertStringContainsString('"active", "td [data-style-target=', $script);
        $this->assertStringContainsString('boolean-true', $script);
        $this->assertStringContainsString('boolean-false', $script);
        $this->assertStringContainsString('boolean-null', $script);
    }

    public function test_named_order_and_icon_overrides_round_trip_in_existing_user_preferences() : void
    {
        $data = new Data();
        $data->setUiSetting('columnOrder', ['amount', 'active']);
        $data->addColumnSettings('active', 7, ['selectors' => ColumnStyles::normalize([
            'td [data-style-target="boolean-null"]' => ['__icon' => 'circle-question'],
        ])]);
        $restored = new Data(json_encode($data));
        $this->assertSame(['amount', 'active'], $restored->getUiSetting('columnOrder'));
        $this->assertSame('circle-question', $restored->getColumnSettingsByField($this->field())['selectors']['td [data-style-target="boolean-null"]']['__icon']);
    }
}
