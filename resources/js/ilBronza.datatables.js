/*

add this to package.json dependencies list

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
        "npm": "^7.12.0",
        "webpack-jquery-ui": "^2.0.1"

*/

import $ from 'jquery';
window.$ = window.jQuery = $;

require('webpack-jquery-ui');

window.moment = require('moment');

require('datatables.net-dt');
require('datatables.net-buttons-dt');

require( 'datatables.net-autofill-dt' );
require( 'datatables.net-buttons-dt' );
require( 'datatables.net-buttons/js/buttons.colVis.js' );
require( 'datatables.net-buttons/js/buttons.html5.js' );
require( 'datatables.net-buttons/js/buttons.print.js' );
require( 'datatables.net-colreorder-dt' );
require( 'datatables.net-fixedcolumns-dt' );
require( 'datatables.net-fixedheader-dt' );
require( 'datatables.net-keytable-dt' );
require( 'datatables.net-responsive-dt' );
require( 'datatables.net-rowgroup-dt' );
require( 'datatables.net-rowreorder-dt' );
require( 'datatables.net-scroller-dt' );
require( 'datatables.net-searchbuilder-dt' );
require( 'datatables.net-searchpanes-dt' );
require( 'datatables.net-select-dt' );

require('./datatables.vendor.ajaxCall.min.js');
require('./datatables.vendor.ajaxButton.min.js');
require('./datatables.vendor.buttons.min.js');
require('./datatables.vendor.sorting.min.js');
require('./datatables.vendor.filtering.min.js');
require('./datatables.vendor.columnVisibility.min.js');
require('./datatables.vendor.summary.min.js');
require('./datatables.vendor.duplicates.min.js');
require('./datatables.vendor.utilities.min.js');
require('./datatables.vendor.datatablesFields.min.js');
require('./datatables.vendor.main.min.js');
