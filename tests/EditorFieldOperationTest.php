<?php

namespace IlBronza\Datatables\Tests;

use IlBronza\Datatables\DatatablesFields\Iterators\DatatableFieldEach;
use IlBronza\Datatables\DatatablesFields\Model\DatatableFieldKeyvalue;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EditorFieldOperationTest extends TestCase
{
    private Container $previousContainer;

    protected function setUp() : void
    {
        $this->previousContainer = Container::getInstance();
        $container = new Container();
        $container->instance('config', new Repository([
            'datatables' => ['replace_model_id_string' => '__ID__'],
        ]));
        $loader = new ArrayLoader();
        $loader->addMessages('it', 'operations', [
            'remind' => 'Confermi il promemoria?',
            'invert' => 'Confermi l\'inversione dei colori?',
        ]);
        $container->instance('translator', new Translator($loader, 'it'));
        Container::setInstance($container);
    }

    protected function tearDown() : void
    {
        Container::setInstance($this->previousContainer);
    }

    private function field() : DatatableFieldKeyvalue
    {
        $field = (new ReflectionClass(DatatableFieldKeyvalue::class))->newInstanceWithoutConstructor();
        $field->modelClass = EditorFieldOperationModel::class;
        $field->labelProperty = 'label';
        $field->fieldOperations = [
            'show' => ['type' => 'link', 'link' => '/products/__ID__'],
            'remind' => [
                'type' => 'editor.toggle',
                'link' => '/products/__ID__/update',
                'editorProperty' => 'reminding',
                'editorValueFunction' => 'hasBeenReminded',
                'fetcher' => ['urlMethod' => 'getSummaryUrl', 'target' => 'currentField'],
            ],
            'invert' => [
                'type' => 'editor.ajax',
                'link' => '/products/__ID__/update',
                'editorProperty' => 'id',
                'data' => ['ibaction' => 'invertColors'],
            ],
        ];

        return $field;
    }

    public function test_iterator_keeps_ids_labels_and_toggle_states_separate_for_each_model() : void
    {
        $iterator = (new ReflectionClass(DatatableFieldEach::class))->newInstanceWithoutConstructor();
        $iterator->child = $this->field();

        $result = $iterator->transformValue(collect([
            new EditorFieldOperationModel('first', 'First product', true),
            new EditorFieldOperationModel('second', 'Second product', false),
        ]))->all();

        $this->assertSame([
            ['first', 'First product', ['remind' => true]],
            ['second', 'Second product', ['remind' => false]],
        ], $result);
    }

    public function test_operation_parameters_resolve_the_fetcher_and_keep_the_ajax_payload() : void
    {
        $operations = $this->field()->getEditorFieldOperations();

        $this->assertSame(['remind', 'invert'], array_keys($operations));
        $this->assertSame('/products/__ID__/reminds', $operations['remind']['fetcher']['url']);
        $this->assertSame(['ibaction' => 'invertColors'], $operations['invert']['data']);
    }

    public function test_plain_and_null_cells_keep_their_original_shape() : void
    {
        $field = $this->field();
        $field->fieldOperations = ['show' => ['type' => 'link', 'link' => '/products/__ID__']];

        $this->assertSame(['first', 'First product'], $field->transformValue(new EditorFieldOperationModel('first', 'First product')));
        $this->assertSame([null, null], $field->transformValue(null));
    }

    public function test_confirmation_messages_are_translated_before_exporting_editor_operations() : void
    {
        $field = $this->field();
        $field->fieldOperations['remind']['confirmMessage'] = 'operations.remind';
        $field->fieldOperations['invert']['confirmMessage'] = 'operations.invert';

        $operations = $field->getEditorFieldOperations();

        $this->assertSame('Confermi il promemoria?', $operations['remind']['confirmMessage']);
        $this->assertSame('Confermi l\'inversione dei colori?', $operations['invert']['confirmMessage']);
        $this->assertSame('operations.remind', $field->fieldOperations['remind']['confirmMessage']);
    }

    public function test_confirmation_messages_can_be_literal_text() : void
    {
        $field = $this->field();
        $field->fieldOperations['invert']['confirmMessage'] = 'Invertire i colori?';

        $this->assertSame('Invertire i colori?', $field->getEditorFieldOperations()['invert']['confirmMessage']);
    }

    public function test_editor_operations_keep_absent_and_empty_confirmation_messages() : void
    {
        $field = $this->field();
        $this->assertArrayNotHasKey('confirmMessage', $field->getEditorFieldOperations()['remind']);

        $field->fieldOperations['remind']['confirmMessage'] = '';
        $field->fieldOperations['invert']['confirmMessage'] = null;

        $operations = $field->getEditorFieldOperations();

        $this->assertSame('', $operations['remind']['confirmMessage']);
        $this->assertNull($operations['invert']['confirmMessage']);
    }

    public function test_delete_operations_keep_their_separate_confirmation_path() : void
    {
        $field = $this->field();
        $field->fieldOperations['delete'] = [
            'type' => 'delete',
            'link' => '/products/__ID__',
            'confirmMessage' => 'operations.remind',
        ];

        $this->assertSame(['remind', 'invert'], array_keys($field->getEditorFieldOperations()));
    }

    public function test_toggle_can_read_its_property_without_a_value_function() : void
    {
        $field = $this->field();
        unset($field->fieldOperations['remind']['editorValueFunction']);

        $model = new EditorFieldOperationModel('first', 'First product', true);
        $this->assertSame(['remind' => true], $field->transformValue($model)[2]);
    }
}

class EditorFieldOperationModel
{
    public function __construct(public string $id = '', public string $label = '', public bool $reminding = false) {}
    public static function make() : static { return new static(); }
    public function getKeyName() : string { return 'id'; }
    public function getKey() : string { return $this->id; }
    public function hasBeenReminded() : bool { return $this->reminding; }
    public function getSummaryUrl() : string { return '/products/' . $this->id . '/reminds'; }
}
