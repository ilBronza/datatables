<?php

return [
	'widths' => [
		'boolean' => '1em'
	],

	'datatableFieldWidths' => [
		'datatableFieldColor' => '2em',
		'datatableFieldIcon' => '2em',
		'datatableFieldJson' => '30em',
		'datatableFieldInteger' => 'auto',
		'datatableFieldFunction' => 'auto',
		'datatableFieldSelectRowCheckbox' => '1.5em',
		'datatableFieldFlat' => 'auto',
		'datatableFieldBoolean' => '1.5em',
		'datatableFieldBooleanAlarm' => '1.5em',
		'datatableFieldTranslatedClassBasename' => 'auto',

		'clients' => [
			'datatableFieldColor' => '3em'
		],

		'media' => [
			'datatableFieldMedia' => 'auto',
		],

		'html' => [
			'datatableFieldTag' => 'auto',
		],


		'dates' => [
			'datatableFieldDatetime' => '12em',
			'datatableFieldDate' => '5.7em',
			'datatableFieldSecondsToMinutes' => '5em',
			'datatableFieldTimestamp' => 'auto',
			'datatableFieldSecondsToString' => '3em',

			'fromString' => [
				'datatableFieldDate' => '5.7em'
			]
		],
		'relations' => [
			'datatableFieldHasMany' => 'auto',
			'datatableFieldBelongsTo' => 'auto',
			'datatableFieldBelongsToMany' => 'auto'
		],
		'numbers' => [
			'datatableFieldNumber2' => '4em',
			'datatableFieldPrice' => '4em',
			'datatableFieldFloor' => '4em'
		],
		'users' => [
			'datatableFieldName' => 'auto',
		],
		'models' => [
			'datatableFieldCachedModelProperty' => 'auto'
		],
		'iterators' => [
			'datatableFieldEach' => 'auto'
		],
		'links' => [
			'datatableFieldLinkCachedProperty' => 'auto',
			'datatableFieldArchive' => '2em',
			'datatableFieldUnDelete' => '2em',
			'datatableFieldDelete' => '2em',
			'datatableFieldEmail' => '18em',
			'datatableFieldSee' => '2em',
			'datatableFieldSeeName' => '2em',
			'datatableFieldEdit' => '2em',
			'datatableFieldLink' => '2em',
			'datatableFieldPdf' => '2em',
			'datatableFieldClone' => '2em',
			'datatableFieldFetcher' => 'auto'

		],
		'editor' => [
			'datatableFieldToggle' => '2em',
			'datatableFieldSave' => '2em',
			'datatableFieldPrice' => '6em',
			'datatableFieldNumeric' => '6em',
			'datatableFieldText' => 'auto',
			'datatableFieldSelect' => '12em',

			'datatableFieldColor' => '3em',

			'datatableFieldAjax' => 'auto',

			'dates' => [
				'datatableFieldTime' => '4em',
				'datatableFieldDatetime' => '12em',
				'datatableFieldDate' => '7em'
			],
		],

		'utilities' => [
			'datatableFieldRemoveNamespace' => 'auto',
			'datatableFieldMilestone' => '15em',
			'datatableFieldSorting' => '2em'
		],

		'datatableFieldPrimary' => '12em'
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
    'scrollY' => true,

	'fixedColumns' => [
		'enabled' => false,
	],

	'saveState' => false,

	'debug' => true,

	'pageLength' => 50,

	'mustPrintIntestation' => false,

	'footerFilters' => true,

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