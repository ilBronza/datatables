# AJAX field operations

`fieldOperations` accepts `editor.ajax` and `editor.toggle` alongside links,
delete and filtering controls. Configure `link` with the model's update URL
containing `config('datatables.replace_model_id_string')`, `editorProperty`
with the submitted field, and optional `data` with the action payload.

Use `model.keyvalue` when the cell receives an already loaded model, including
an `iterators.each` child. Set `modelClass` and `labelProperty`. Each cell carries
the model ID, label, and the state of any toggle operations. Toggle state comes
from `editorValueFunction`, or from `editorProperty` when no function is given.

```php
'mySelfComponents.orderProducts' => [
    'type' => 'iterators.each',
    'childParameters' => [
        'type' => 'model.keyvalue',
        'modelClass' => OrderProduct::class,
        'labelProperty' => 'name',
        'fieldOperations' => [
            'invertColors' => [
                'type' => 'editor.ajax',
                'icon' => 'code',
                'link' => $placeholder->getPlaceholderRoute('update'),
                'editorProperty' => 'id',
                'data' => ['ibaction' => 'invertColors'],
            ],
            'remind' => [
                'type' => 'editor.toggle',
                'link' => $placeholder->getPlaceholderRoute('update'),
                'editorProperty' => 'reminding',
                'editorValueFunction' => 'hasBeenReminded',
                'fetcher' => ['urlMethod' => 'getRemindsSummaryUrl'],
            ],
        ],
    ],
],
```

Clicks send the existing editor POST request with `_method=PUT`; successful
updates refresh the owning row. The toolbar can disappear while the request is
pending without detaching the request target. Toggles use their own state and
never replace the key/value label. A configured fetcher keeps the existing
double-click summary modal, using the ID of the specific iterated model.

Rebuild the host application's JavaScript bundle after updating this package.
