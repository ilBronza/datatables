{{--  DATATABLES  --}}
<script type="text/javascript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.flash.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.4/js/buttons.print.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
<script type="text/javascript" src="/js/moment.min.js"></script>
{{--  //DATATABLES  --}}


{{-- START FILTERFUNCTIONS --}}
<style type="text/css">
    .datatablefilter
    {
        position: relative;
    }

    .filterfunctions
    {
        position: absolute;
        top: -14px;
        left: 0px;
        background-color: rgba(0, 0, 0, 0.5);
        color: #fff;
    }

    td
    {
        max-width: 140px;
    }

    nobr
    {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1em;
    }

    .uk-text-truncate br
    {
        display: none!important;
    }

    .uk-text-truncate *
    {
        display: inline!important;
    }
</style>

<script type="text/javascript">

$(document).ready(function($)
{
    $('body').on('mouseenter', '.datatablefilter', function()
    {
        $(this).find('.filterfunctions').removeClass('uk-hidden');
    });

    $('body').on('mouseleave', '.datatablefilter', function()
    {
        $(this).find('.filterfunctions').addClass('uk-hidden');
    });

    $('th .filterfunctions *').click(function(e)
    {
        e.stopPropagation();
    });

    $('.filterfunctions .close').click(function(e)
    {
        $(this).closest('.datatablefilter').find('input, select, textarea').each(function()
        {
            $(this).val('');
            $(this).change();
        });
    });
});
    
</script>

{{-- END FILTERFUNCTIONS --}}

<script type="text/javascript">
$(document).ready(function($)
{
    window.datatablesGetJsonObjectValues = function(key, fields, object)
    {
        let result = new Array();

        result.push(key + ': ' + object[key]);

        if(fields[key].length > 0)
            result.push(
                window.datatablesGetJsonObjectString(fields[key], object[key])
                );

        return result.join(', ');
    }


    window.datatablesGetJsonObjectString = function(fields, object)
    {
        let result = new Array();

        for (var key in fields)
            result.push(window.datatablesGetJsonObjectValues(key, fields, object));

        return result.join(' - ');
    }

    window.datatablesJsonEncode = function(object)
    {
        return JSON.stringify(object, null, 2);
    }

    $('input[type="date"]').change(function()
    {
        $(this).data('timestamp', new Date($(this).val()).getTime() / 1000);
    });

    $.fn.dataTable.ext.buttons.reload = {
        text: 'Reload',
        action: function ( e, dt, node, config ) {
            dt.ajax.reload();
        }
    };

    $.fn.dataTable.ext.buttons.removeSummary = {
        text: 'Riepilogo',
        action: function ( e, dt, node, config ) {

            $(node).toggleClass('uk-button-danger');

            // let mytitle = dt.column( idx ).header().innerHTML;
            let tableId = $(dt.column( 0 ).header()).parents('table').attr('id');
            let summaryRow = $('#' + tableId).find('tr.summary');

            $(summaryRow).toggle();
        }
    };

    $.fn.dataTable.ext.buttons.removeInlineSearch = {
        text: 'Riepilogo filtrato',
        action: function ( e, dt, node, config ) {

            $(node).toggleClass('uk-button-danger');

            // let mytitle = dt.column( idx ).header().innerHTML;
            let tableId = $(dt.column( 0 ).header()).parents('table').attr('id');
            let summaryRow = $('#' + tableId).find('tr.inlinesearchsummary');

            $(summaryRow).toggle();
        }
    };

    function addParameterToURL(url, paramName, paramValue)
    {
        url += (url.split('?')[1] ? '&':'?') + paramName + '=' + paramValue;

        return url;
    }

    $('th input').click(function(e)
    {
        e.stopPropagation();
    });

    function transformDataBySummaryExistence(tableId, summary, json)
    {
        if(summary)
        {
            let data = json.data;

            let summaryValues = json.data.pop();
            let summaryRow = $('#' + tableId).find('thead tr.summary');

            summaryValues.forEach(function(element, index)
            {
                let th = $(summaryRow).find('.summary' + index);
                $(th).html(element);
            });
        }

        return json.data;
    }

    function populateFilteredColumnValues(api, tableId)
    {
        // let filteredRows = api.rows({filter:'applied'}).data();
        let filterRowsOptions = {};

        if(window[ tableId + 'FilteredSelected'])
            filterRowsOptions = {selected: true};

        let filteredRows = api.rows(filterRowsOptions);

        $('#' + tableId).find('thead tr.columns th').each(function(columnIndex, element)
        {
            let summary = $(element).find('input').data('summary');

            if(typeof summary === 'undefined')
                return;

            let realColumnNumber = $(element).data('column');
            let th = $('#' + tableId + ' .inlinesearchsummary').find('.summary' + realColumnNumber);

            if(summary == 'sumMinutes')
            {
                // api.rows({selected: true}).every(function (rowIdx, tableLoop, rowLoop)
                // {
                //     console.log('uno');
                //     console.log(rowIdx);
                //     console.log(tableLoop);
                //     console.log(rowLoop);

                //     var data = this.cells(rowIdx, 4).data();

                //     console.log(data);
                // });
            }

            if((summary == 'average')||(summary == 'sum')||(summary == 'sumMinutes'))
            {
                let result = 0;
                let totalRowsFilteredCount = 0;

                var selection = {};

                filteredRows.every(function (rowIdx, tableLoop, rowLoop)
                {
                    var value = this.cell(rowIdx, realColumnNumber, { search:'applied' }).data();

                    if(! isNaN(float = parseFloat(value)))
                        result = result + float;

                    totalRowsFilteredCount = totalRowsFilteredCount + 1;
                });

                // api.column(columnIndex, { search:'applied' } ).data().each(function(value, rowIndex) {

                //     // console.log('rowIndex');
                //     // console.log(rowIndex);
                //     // console.log(value);

                //     // if(! isNaN(float = parseFloat(value)))
                //     //     result = result + float;

                //     // totalRowsFilteredCount = rowIndex;
                // });

                if(summary == 'average')
                    result = Math.round(((result / (totalRowsFilteredCount + 1)) + Number.EPSILON) * 100) / 100

                if(summary == 'sumMinutes')
                {
                    let hours = Math.floor((result / 60)) + "h";
                    let minutes = Math.floor((result % 60)) + "'";

                    result = hours + ' ' + minutes;
                }

                $(th).html(result);
            }
            else if((summary == 'distinct'))
            {
                let result = new Array();

                filteredRows.every(function (rowIdx, tableLoop, rowLoop)
                {
                    var value = this.cell(rowIdx, realColumnNumber, { search:'applied' }).data();

                    if(typeof result[value] === 'undefined')
                        result[value] = 0;

                    result[value] ++;
                });


                // api.column(columnIndex, { search:'applied' } ).data().each(function(value, rowIndex) {
                //     if(typeof result[value] === 'undefined')
                //         result[value] = 0;

                //     result[value] ++;
                // });

                let string = new Array;

                Object.keys(result).map(function(key, index)
                {
                    string.push(key + ': ' + result[key]);
                });

                $(th).html(string.join('<br />'));
            }

        });

    }

    function _filter(container, section, searchValue = false)
    {
        $('input', section).on('keyup change clear', function ()
        {
            let value = $(this).val().toLowerCase();

            if($(this).attr('type') == 'date')
                value = new Date(value).getTime() / 1000;

            $(this).data('value', value);

            // if(container.search() !== value)
            // {
                if(searchValue)
                    container.search(this.value).draw();

                else
                    container.draw();
            // }
        });        
    }

    function normalFilter(container, section)
    {
        _filter(container, section, true);
    }

    function rangeFilter(container, section)
    {
        _filter(container, section);
    }

    $('.wannabedatatable').each(function()
    {
        let url = $(this).data('url');
        let cachedtablekey = $(this).data('cachedtablekey');

        let tableId = $(this).attr('id');
        let columnDefs = window[tableId + 'columnDefs'];
        let rowReorder = window[tableId + 'rowReorder'];
        let options = window[tableId + 'options'];
        let buttons = window[tableId + 'buttons'];

        let summary = (typeof $('#' + tableId).data('summary') !== 'undefined');

        let datatableUrl = addParameterToURL(url, 'cachedtablekey', cachedtablekey);

        let settings = {
            dom: 'Bfrtip',
            processing: true,
            orderCellsTop: true,
            paging: false,
            // "serverSide": true,
            ajax: {
                url: datatableUrl,
                dataSrc: function(json)
                {
                    let data = transformDataBySummaryExistence(tableId, summary, json);

                    return data;
                }
            },
            columnDefs : columnDefs,
            rowReorder : rowReorder,
            buttons: buttons,
            initComplete: function ()
            {
                this.api().columns().every(function ()
                {
                    // normalFilter(this, this.header());

                    // // var that = this;

                    if($(this.header()).data('range'))
                        rangeFilter(this, this.header());
                    else
                        normalFilter(this, this.header());
                });
            },
            drawCallback: function(settings)
            {
                var api = this.api();

                if(summary)
                    populateFilteredColumnValues(api, tableId);
            }
        };

        jQuery.extend(settings, options);

        window['table' + tableId] = $(this).DataTable(settings);        
    })

    // START SCRIPTS PER LE RICERCHE SU CAMPI
    /* Create an array with the values of all the input boxes in a column */
    $.fn.dataTable.ext.order['dom-text'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
            return $('input', td).val();
        } );
    }

    /* Create an array with the values of all the input boxes in a column, parsed as numbers */
    $.fn.dataTable.ext.order['dom-text-numeric'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
            return $('input', td).val() * 1;
        } );
    }
     
    /* Create an array with the values of all the select options in a column */
    $.fn.dataTable.ext.order['dom-select'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
            return $('select', td).val();
        } );
    }
     
    $.fn.dataTable.ext.order['mystring-asc'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i )
        {
            let value = $(td).html();

            if(value == '')
                value = 'zzzzz';

            return value;
        } );
    }

    $.fn.dataTable.ext.order['mynumber-asc'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i )
        {
            let value = $(td).html();

            if(value == '')
                value = 9999999999999;

            return value;
        } );
    }

    $.fn.dataTable.ext.order['mynumber-desc'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i )
        {
            let value = $(td).html();

            if(value == '')
                value = -9999999999999;

            return value;
        } );
    }

    $.fn.dataTable.ext.order['mydate-asc'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i )
        {
            let value = $(td).html();

            if(value == '')
                value = '4021-05-28 10:27:00';

            return value;
        } );
    }

    jQuery.fn.dataTableExt.order['test-asc'] = function(x,y)
    {
        console.log('ordino');
        console.log(x);
        console.log(y);

        var retVal;
        x = $.trim(x);
        y = $.trim(y);

        if (x==y) retVal= 0;
        else if (x == "" || x == " ") retVal= 1;
        else if (y == "" || y == " ") retVal= -1;
        else if (x > y) retVal= 1;
        else retVal = -1; // <- this was missing in version 1

        return retVal;
    }

    /* Create an array with the values of all the checkboxes in a column */
    $.fn.dataTable.ext.order['dom-checkbox'] = function  ( settings, col )
    {
        return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i )
        {
            return $('input', td).prop('checked') ? '1' : '0';
        });
    }
});   

// END SCRIPTS PER LE RICERCHE SU CAMPI



</script>

