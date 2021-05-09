<script type="text/javascript">

$(document).ready(function($)
{
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
    
</script>