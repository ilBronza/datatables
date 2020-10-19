<script>

jQuery(document).ready(function()
{
    function setCookie(cname, cvalue, exMinutes)
    {
        var d = new Date();
        d.setTime(d.getTime() + (exMinutes*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    $('input.range').change( function()
    {
        var id = $(this).parents('table').data('id');

        setTimeout(function(){ 
        window['table' + id].draw();
         }, 300);
    } );

    $('th input').click(function(e)
    {
        e.stopPropagation();
    });

    @if($table->searchOnEnter ?? false)
    $('body').on('keypress', '.sectionheader input.column_filter:not(.range)', function(e)
    {
        var key = e.which;
        if(key != 13)  // the enter key code
            return true;

        var tableId = $(this).parents('table').data('id');
        var column = $(this).parents('th').data('column');

        var cookieKey = 'table' + tableId + 'header' + column;
        var cookieVal = jQuery(this).val();

        setCookie(cookieKey, cookieVal, 30);

        filterColumn(tableId, column, this);
    });
    @else

    $('body').on('keyup click', '.sectionheader input.column_filter:not(.range)', function()
    {
        var tableId = $(this).parents('table').data('id');
        var column = $(this).parents('th').data('column');

        var cookieKey = 'table' + tableId + 'header' + column;
        var cookieVal = jQuery(this).val();

        setCookie(cookieKey, cookieVal, 30);

        filterColumn(tableId, column, this);
    });
    @endif

    $('body').on('keyup click', '.sectionfooter input.column_filter:not(.range)', function()
    {
        var tableId = $(this).parents('table').data('id');
        var column = $(this).parents('th').data('column');

        var cookieKey = 'table' + tableId + 'footer' + column;
        var cookieVal = jQuery(this).val();

        setCookie(cookieKey, cookieVal, 30);


        filterNotColumn(tableId, column, this);
    });


    function filterColumn (tableId, i, element)
    {
        var table = window['table' + tableId].column(i).search(jQuery(element).val(), true, false).draw();    
        var data = table.page.info(); 
        
        if(data['recordsDisplay'] == data['recordsTotal'])
            $('#filter_alert').css('display', 'none');

        else{
            $('#filter_alert').css('display', '');
            $('#filtrati').html('Filtrati: ' + data['recordsDisplay'] + ' su: ' +  data['length']);
        }
    }

    function filterNotColumn (tableId, i, element) {

        var top = jQuery(".sectionheader input[data-column='" + jQuery(element).data('column') + "']").val(); 
        var bottom = jQuery(element).val();
        var column = jQuery('#' + tableId).DataTable().column(i);
        var string = '';
    
        if(bottom.length == 0)
        {
            if(top.length == 0)
                string = '^((?!(23595123053215912525)).)*$';

            else
                string = top;
        }
        else
        {
            if(top.length == 0)
                string = '^((?!('+bottom+')).)*$';

            else
                string = '(?=.*'+top+')(?=^((?!('+bottom+')).)*$)';
        }

        column.search(string, true, false).draw();   
    }

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

    // END SCRIPTS PER LE RICERCHE SU CAMPI

    $('input.range').change(function()
    {
        if($(this).attr('type') == 'date')
            $(this).data('timestamp', new Date($(this).val()).getTime() / 1000);


    });
});

</script>