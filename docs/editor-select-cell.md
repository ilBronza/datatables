# `editor.selectCell`

`editor.selectCell` is an inline editor for a select whose allowed values can
vary from one table row to another. Use it when the options depend on the
model displayed in that row: for example, the vehicles available for a given
order.

It is different from `editor.select`: the latter has one common list of
options for the whole table, while `editor.selectCell` resolves the list in
the context of each row.

## Basic configuration

At minimum, configure the field type and a method that returns the label of
the current value for the row.

```php
'vehicle_id' => [
    'type' => 'editor.selectCell',
    'possibleValuesRowLabelMethod' => 'getVehicleSelectLabel',
],
```

`possibleValuesRowLabelMethod` is required. It is called on the row model and
must return the text shown before the user opens the select. It does not
receive arguments, so it should use the model's current field value.

```php
class Order extends Model
{
    public function getVehicleSelectLabel(): string
    {
        return $this->vehicle?->name ?? 'nd';
    }
}
```

When the value is `null` (or the configured `nullValue`), the editor displays
`nullString` instead and does not call this method.

## Option lists already available on the model

Set `possibleValuesMethod` when the values can be calculated while the table
row is being rendered. The method is called on each row model and must return
an associative array of `value => label`.

```php
'vehicle_id' => [
    'type' => 'editor.selectCell',
    'possibleValuesRowLabelMethod' => 'getVehicleSelectLabel',
    'possibleValuesMethod' => 'getVehicleSelectPossibleValues',
    'refreshRow' => true,
],
```

```php
class Order extends Model
{
    public function getVehicleSelectPossibleValues(): array
    {
        return Vehicle::query()
            ->where('company_id', $this->company_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
```

The option list is sent with the row data. This is the most direct choice for
small lists and avoids an extra request when the select opens.

## Lazy loading from a route

Set `possibleValuesRowRoute` when the list should be fetched only when the
user opens the select. This is useful for large or expensive per-row lists.

```php
'vehicle_id' => [
    'type' => 'editor.selectCell',
    'possibleValuesRowLabelMethod' => 'getVehicleSelectLabel',
    'possibleValuesRowRoute' => route('orders.vehicle-options', [
        'order' => config('datatables.replace_model_id_string'),
    ]),
    'select2' => true,
    'refreshRow' => true,
],
```

With the default configuration, the placeholder is
`replace_model_id_string`. For a route such as
`/orders/{order}/vehicle-options`, the browser replaces it with the row id
before making the request. It also sends these query parameters:

| Parameter | Meaning |
| --- | --- |
| `row_id` | Row model key |
| `field` | Datatable field name |
| `table_id` | Rendered table id |

The endpoint must authorize access to the resolved model and return either a
plain options map or an object containing it under `list`:

```php
Route::get('/orders/{order}/vehicle-options', function (Order $order) {
    Gate::authorize('update', $order);

    return [
        'list' => $order->getVehicleSelectPossibleValues(),
    ];
})->name('orders.vehicle-options');
```

Both of these response bodies are valid:

```json
{ "4": "Panda", "7": "Golf" }
```

```json
{ "list": { "4": "Panda", "7": "Golf" } }
```

When `possibleValuesRowRoute` is set, `possibleValuesMethod` is not used for
the cell options. The route is requested at the first opening and again at
each later opening, so the list can reflect current server state. This avoids
issuing one request for every visible row when the page is loaded.

## Nullable values and option groups

Set the standard select properties when needed:

```php
'vehicle_id' => [
    'type' => 'editor.selectCell',
    'possibleValuesRowLabelMethod' => 'getVehicleSelectLabel',
    'possibleValuesRowRoute' => route('orders.vehicle-options', [
        'order' => config('datatables.replace_model_id_string'),
    ]),
    'nullable' => true,
    'nullValue' => 'null',
    'nullString' => 'Nessun veicolo',
    'select2' => true,
],
```

The options may also be grouped:

```json
{
  "null": "Nessun veicolo",
  "groups": {
    "Disponibili": { "4": "Panda" },
    "In manutenzione": { "7": "Golf" }
  }
}
```

## Choosing the right editor

| Requirement | Use |
| --- | --- |
| One option list shared by all rows | `editor.select` |
| Different options for every row, included in table data | `editor.selectCell` with `possibleValuesMethod` |
| Different options for every row, fetched only on opening | `editor.selectCell` with `possibleValuesRowRoute` |

`select2 => true` is optional in every case. It adds textual search to the
select; with a row route, its initialization still waits for the options to be
loaded.
