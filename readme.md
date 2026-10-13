# Datatables

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require ilbronza/datatables
```

add this lines to package.json dependencies list

``` bash
"datatables.net-autofill-dt": "^2.3.6",
"datatables.net-buttons-dt": "^1.7.0",
"datatables.net-colreorder-dt": "^1.5.3",
"datatables.net-dt": "^1.10.24",
"datatables.net-fixedcolumns-dt": "^3.3.2",
"datatables.net-fixedheader-dt": "^3.1.8",
"datatables.net-keytable-dt": "^2.6.1",
"datatables.net-responsive-dt": "^2.2.7",
"datatables.net-rowgroup-dt": "^1.1.2",
"datatables.net-rowreorder-dt": "^1.2.7",
"datatables.net-scroller-dt": "^2.0.3",
"datatables.net-searchbuilder-dt": "^1.0.1",
"datatables.net-searchpanes-dt": "^1.2.2",
"datatables.net-select-dt": "^1.3.3",
"jszip": "^3.6.0",
"moment": "^2.29.1",
"webpack-jquery-ui": "^2.0.1"
```

run npm install from terminal

``` bash
npm install
```

copy the file `vendor/ilbronza/datatables/resources/js/ilBronza.datatables.js` to `resources/js/ilBronza.datatables.js`
add these line to resources/js/app.js to include the required modules. All other js files are included directly from vendor folder
``` bash
require('./ilBronza.datatables.js');
```

keep in mind that it needs jQuery and jquery-ui, if you didn't required yet add to yout resources/js/app.js file
``` bash
import $ from 'jquery';
window.$ = window.jQuery = $;

require('webpack-jquery-ui');
```






publish the package assets

``` bash
php artisan vendor:publish --force --tag "datatables.assets"
```


compile the file with laravel-mix

``` bash
npm run development
```



## Usage

### Row refresh queries

Implement `getIndexQuery()` on a CRUD index controller to expose its Eloquent
builder. Row refreshes apply the primary-key filter before executing the query;
existing controllers use a temporary model scope around `getIndexElements()`.
See [the row refresh query guide](docs/row-refresh-query.md) for the optional
standard index trait, legacy behavior, and standalone tables.

### `async`

Use `async` to load one column in a single POST after each draw. IDs come from
the existing DataTables row IDs and the endpoint is stored on the column header. See
[the async field guide](docs/async-field.md) for configuration, response format,
renderers, and caching.

### `editor.selectCell`

Use `editor.selectCell` when a select's allowed values are included in each
table row. For lazy loading when the editor opens, both `editor.select` and
`editor.selectCell` support `possibleValuesRowRoute`. See
[the `editor.selectCell` guide](docs/editor-select-cell.md) for configuration,
response formats, nullable values, and Select2 support.

### Hover operations and delete

Use `fieldOperations` on a `keyvalue.keyvalue` field to show actions over its
cell on hover. The built-in `delete` operation uses the field's `modelClass` to
generate a placeholder `getDeleteUrl()`, substitutes the cell's ID, asks for
confirmation, and sends a `DELETE` request through the existing Ajax handler.

```php
'id' => [
    'type' => 'keyvalue.keyvalue',
    'modelClass' => Order::gpc(),
    'property' => 'name',
    'fieldOperations' => [
        'commessa' => [
            'type' => 'link',
            'faIcon' => 'clipboard',
            'target' => '_blank',
            'link' => $orderPlaceholder->getPlaceholderRoute('show'),
        ],
        'delete',
    ],
],
```

`delete` defaults to the Font Awesome `trash` icon. To override the icon or
URL, use `'delete' => ['type' => 'delete', 'faIcon' => 'trash', 'link' => $url]`.
An explicit URL must contain the configured row-ID placeholder when it is
shared across rows.

Set `'fieldOperationsVertical' => true` alongside `fieldOperations` to stack
the hover actions vertically in a 30px-wide popup. The default is `false`
(horizontal). For iterated fields, set it inside `childParameters` to orient
each item's popup.

Each operation can also specify a `label` to display text instead of its icon.
Popups containing labels fit their text in either orientation. On `links.see`
fields, placeholder links use the owning DataTables row ID.

For a link in a UIkit iframe lightbox, set `'target' => 'iframe'`. The operation
adds `iframed=true` to the URL and keeps the row-ID placeholder substitution:

```php
'note' => [
    'type' => 'link',
    'faIcon' => 'clipboard',
    'target' => 'iframe',
    'link' => $orderPlaceholder->getAddNotesUrl(),
],
```

### Read-only format for date editors

Set `dateFormat` on an `editor.dates.date` field to format the cell while its
input is not focused, and for users who cannot edit it:

```php
'periodo' => [
    'type' => 'editor.dates.date',
    'dateFormat' => 'YYYY-MM',
],
```

Focusing the cell reveals the native date input, which still uses
`inputFieldDefaultFormat` (`YYYY-MM-DD` by default). Saving preserves the full
date, including the day. Without `dateFormat`, the existing display is unchanged.
This option also applies to `editor.dates.datetime` and `editor.dates.time`.
Rebuild the host application's JavaScript and LESS assets after updating this
feature (publish `datatables.assets` first if the app uses copied assets).

### Validity classes for date fields

Add `checkValidity => true` to a `dates.*` or `editor.dates.*` field to expose
its validity alongside its normal value. The cell data gains one final value:
`0` for a past date, `1` for a future date, and `null` when the date is empty.
The same value is automatically added as a class to the row, prefixed with the
field slug (for example, `expiry-date0`, `expiry-date1`, or
`expiry-datenull`). This makes it possible to style the whole row:

```php
'expiry_date' => [
    'type' => 'dates.date',
    'checkValidity' => true,
],
```

```css
tr.expiry-date0 { background: #fee2e2; }
tr.expiry-date1 { background: #dcfce7; }
```


### Form in table header and submit cell

You can populate a form in the header of the table and use a table cell as the submit button.  
This feature is useful for quick inline filters or actions. To enable it, use the `DatatableFieldSubmit` field type in your configuration.

create a submit field
```
class DatatableFieldAddUnitloads extends DatatableFieldSubmit
{
    public $function = 'getAddUnitloadsToDeliveryUrl';
}
```

add the field to the table header
```
public function addPostFieldsToTable()
{
	$unitloads = Unitload::gpc()::whereIn('id', request()->unitloads)->get();

	foreach($unitloads as $unitload)
		$this->getTable()->addPostField(
			FormField::createFromArray([
				'label' => $unitload->getName() . ' (' . $unitload->getQuantity() . ')',
				'type' => 'text',
				'disabled' => true,
				'name' => 'unitloads[]',
				'value' => $unitload->getKey(),
			])
		);
}
```

use the created field in fields configuration

```
static function getFieldsGroup() : array
{
    return 
    [
        'fields' => 
        [
            'mySelfAddToDelivery' => 'warehouse::deliveries.addUnitloads'
        ]
    ];
}
```




### Add data to headerData
Example for add data to th

```
'name' => [
    'type' => 'flat',
    'headerData' => [
        'reloadTable' => true,
        'merryChristmas' => 'something'
    ]
]
```

Example for fetcher field

``` bash
    'mySelfTimingMessage.quantityWorkstations' => [
        'type' => 'iterators.each',
        'childParameters' => [
            'requiresPlaceholderElement' => true,
            'textParameter' => 'workstation_alias',
            'type' => 'links.fetcher',

            'fetcher' => [
                'urlMethod' => 'getPriceCalculationMessageUrl',
                //target is the place where to take the id from => 'currentField' || 'row'
                'target' => 'currentField',
                'target' => 'row'
            ]
        ],
        'width' => '25px'
    ],
```

## Refresh dei campi form dopo draw / save riga

Le pagine edit possono registrare campi del form da rileggere dal server quando le tabelle cambiano (es. totali e margini ricalcolati). I campi si registrano come selettori in:

``` javascript
window.dtEditorRefreshingFieldList.push('input[name=total_cost]');
```

(es. il blade `orderQuotationPage` di Products li popola da `getFieldsToUpdateOnTableEdit()`).

Il refresh parte automaticamente in due momenti:

1. **Dopo il save di un campo editor in tabella** — `window.dtRefreshFieldsList()` viene chiamata nella success di `__ibCallAjax`.
2. **A ogni `draw.dt`** (reload ajax, paginazione, sort, init, `reloadAllTables`) — con throttling trailing: la prima draw arma un timer, le draw successive nella finestra vengono assorbite, allo scadere parte un solo refresh.

``` javascript
// finestra di throttling del refresh su draw (default 1000ms)
window.__ibDtRefreshFieldsListOnDrawThrottleMs = 1000;
```

Se la lista `dtEditorRefreshingFieldList` è vuota il listener è un no-op: le pagine senza campi registrati non fanno alcuna chiamata.

`dtRefreshFieldsList()` delega a `dtRefreshFieldsListBatch()` (pacchetto FormField): i selettori vengono risolti in nomi campo e richiesti al server con **una sola POST cumulativa** (`ib-editor-read-batch`), invece di una chiamata per campo. Vedi il readme di `ilbronza/formfield` per l'API completa del batch.

## DatatableFieldClasses

``` bash

class DatatableFieldClient extends DatatableFieldSeeName
{
    public ? string $translationPrefix = 'clients::fields';
    public ? string $forcedStandardName = 'client';
}

```
