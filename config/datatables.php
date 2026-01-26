<?php

return [


	'fonts' => [

        // ---- Modern / UI (Google Fonts – molto leggibili)
        'Inter' => '"Inter", "Helvetica Neue", Helvetica, Arial, sans-serif',
        'Roboto' => '"Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif',
        'Open Sans' => '"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif',
        'Lato' => '"Lato", "Helvetica Neue", Helvetica, Arial, sans-serif',
        'Montserrat' => '"Montserrat", "Helvetica Neue", Helvetica, Arial, sans-serif',
        'Poppins' => '"Poppins", "Helvetica Neue", Helvetica, Arial, sans-serif',
        'Nunito' => '"Nunito", "Helvetica Neue", Helvetica, Arial, sans-serif',

        // ---- Web-safe classici
        'Arial' => 'Arial, Helvetica, sans-serif',
        'Helvetica' => '"Helvetica Neue", Helvetica, Arial, sans-serif',
        'Verdana' => 'Verdana, Geneva, sans-serif',
        'Tahoma' => 'Tahoma, Geneva, sans-serif',
        'Trebuchet MS' => '"Trebuchet MS", Helvetica, sans-serif',

        // ---- Serif (per report / stampa)
        'Times New Roman' => '"Times New Roman", Times, serif',
        'Georgia' => 'Georgia, "Times New Roman", serif',
        'Merriweather' => '"Merriweather", Georgia, serif',
        'Playfair Display' => '"Playfair Display", Georgia, serif',

        // ---- Monospace (codici / numeri / colonne tecniche)
        'Courier New' => '"Courier New", Courier, monospace',
        'Source Code Pro' => '"Source Code Pro", monospace',
        'JetBrains Mono' => '"JetBrains Mono", monospace',
        'Fira Code' => '"Fira Code", monospace',
    ],


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
			'datatableFieldFetcher' => 'auto',
			'datatableFieldSeen' => '2em'

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