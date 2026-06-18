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
