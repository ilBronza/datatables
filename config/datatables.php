<?php

return [
	'widths' => [
		'boolean' => '1em'
	],
	'fields' => [
		'boolean' => [
			'nullable' => true,
		]
	],
    'replace_model_id_string' => env('REPLACE_MODEL_ID_STRING', 'replace_model_id_string'),
    'domStickyButtons' => env('DATATABLES_DOM_STICKY_BUTTONS', false),
    'domStickyHeader' => env('DATATABLES_STICKY_HEADER', false),
    'fixedHeader' =>  env('DATATABLES_FIXED_HEADER', false),
    'hideColumns' => env('DATATABLES_HIDE_COLUMNS', true),
    'useTooltips' => env('DATATABLES_USE_TOOLTIPS', true),

    'scrollX' => false,

	'debug' => true,

	'pageLength' => 50,

	'mustPrintIntestation' => false,

	'rangeFilter' => [
		'enabled' => false
	],

	'defaultButtons' => [
        'search' => true,
		'reload' => true,
        'selectFiltered' => true,
        'copy' => true,
        'csv' => true,
        'doubler' => true,
    ]
];