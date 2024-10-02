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

add these line to resources/js/app.js to include the required modules
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



## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [author name][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ilbronza/datatables.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ilbronza/datatables.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ilbronza/datatables/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/ilbronza/datatables
[link-downloads]: https://packagist.org/packages/ilbronza/datatables
[link-travis]: https://travis-ci.org/ilbronza/datatables
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/ilbronza
[link-contributors]: ../../contributors
