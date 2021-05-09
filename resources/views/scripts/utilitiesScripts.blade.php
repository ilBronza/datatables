<style type="text/css">
.doubled,
.doubled * 
{
	font-weight: bold!important;
	color: red!important;
}


.fieldoperations
{
    position: absolute;
    background-color: #ffffffdd;
    top: 0;
    left: 30px;
    border: 1px solid #ccc;
    display: inline-block;
}

.fieldoperations span
{
    float: left;
    color: #000!important;
    display: inline-block;
    padding: 2px;
    cursor: pointer;
}

.fieldoperations span:hover
{
    background-color: #ddd;
}

.fieldoperations span + span
{
    margin-left: 5px;
}
    



</style> 

<script type="text/javascript">
jQuery(document).ready(function()
{
    window.decodeHTMLEntities = function(text)
    {
        var entities = [
            ['amp', '&'],
            ['apos', '\''],
            ['#x27', '\''],
            ['#x2F', '/'],
            ['#39', '\''],
            ['#47', '/'],
            ['lt', '<'],
            ['gt', '>'],
            ['nbsp', ' '],
            ['quot', '"']
        ];

        for (var i = 0, max = entities.length; i < max; ++i) 
        text = text.replace(new RegExp('&'+entities[i][0]+';', 'g'), entities[i][1]);

        return text;
    }

    window.filterRowsByCell = function(cell, set)
    {
        var table = $('#order').DataTable();

        if(set)
            var content = $(cell).closest('.client').find('a').html();
        else
            var content = '';

        $('.sectionheader th.client input').val(window.decodeHTMLEntities(content)).keyup();
    }

    window.checkRowsByRowsCell = function(rows, value)
    {
        var changed = 0;

        rows.every(function()
        {
            changed ++;

            if(value)
                this.select();
            else
                this.deselect();
        });

        if(value == true)
            window.addSuccessNotification('aggiunte ' + changed + ' commesse alla selezione', 0.2);
        else
            window.addWarningNotification('rimosse ' + changed + ' commesse dalla selezione', 0.2);         
    }

    window.getRowsIndexesByCell = function(table, cell, justVisible = false)
    {
        var content = $(cell).closest('.client').find('a').html();

        var tdIndex = $(cell).closest('td').index() + 1;
        var colIndex = $(cell).parents('table.dataTable').find('thead tr th:nth-child(' + tdIndex + ')').data('column');

        var indexes;

        if(justVisible)
            indexes = table.rows({ search: 'applied' });

        else
            indexes = table.rows();

        indexes = indexes.eq( 0 ).filter( function (rowIdx) {
            var current = $(table.cell( rowIdx, colIndex ).node()).find('a').html();

            return (current === content) ? true : false;
        } );

        return indexes;
    }

    window.getRowsByCell = function (cell, justVisible = false)
    {
        var table = $(cell).parents('table.dataTable').DataTable();
        var indexes = window.getRowsIndexesByCell(table, cell, justVisible);

        return table.rows( indexes );
            // .nodes()
            // .to$();
    }

    window.checkVisibleRowsByCell = function(cell, value)
    {
        var rows = window.getRowsByCell(cell, true);

        window.checkRowsByRowsCell(rows, value);
    }

    window.checkRowsByCell = function(cell, value)
    {
        var rows = window.getRowsByCell(cell);

        window.checkRowsByRowsCell(rows, value);
    }

    $('body').on('mouseleave', '.ibop', function()
    {
        $(this).find('div.fieldoperations').remove();
    });

    $('body').on('click', '.checkVisible', function()
    {
        window.checkVisibleRowsByCell(this, true);
    });

    $('body').on('click', '.checkAll', function()
    {
        window.checkRowsByCell(this, true);
    });

    $('body').on('click', '.ban', function()
    {
        window.checkRowsByCell(this, false);
    });

    $('body').on('click', '.search', function()
    {
        $(this).find('div').remove();
        window.filterRowsByCell(this, true);
    });

    $('body').on('click', '.close', function()
    {
        $(this).find('div').remove();
        window.filterRowsByCell(this, false);
    });

    $('body').on('click', '.filteredTable', function()
    {
        var htmlTable = $(this).parents('.dataTable');
        var table = htmlTable.DataTable();
        let tableId = htmlTable.attr('id');

        let td = $(this).closest('td');

        var tdIndex = td.index() + 1;

        var th = $(this).parents('.dataTable').find('thead tr th:nth-child(' + tdIndex + ')');

        let htmlClass = th.data('camelname');

        let filteringIndex = $(th).data('column');
        var filteringValue = td.text();

        if($('#modal-' + tableId).length == 0)
            $('body').append('<div id="modal-' + tableId + '" uk-modal><div class="uk-modal-dialog uk-width-1-1 uk-height-1-1"><iframe name="' + Date.now() + '" class="uk-width-1-1 uk-height-1-1" src="' + table.ajax.url() + '&filteringIndex=' + filteringIndex + '&filteringValue=' + filteringValue + '&iframed=true&justTable=true" uk-video></iframe></div></div>');

        UIkit.modal($('#modal-' + tableId)).show();

        var iframe = $('#modal-' + tableId + ' iframe').contents();

        var input = iframe.find('thead th.' + htmlClass + ' input');

        setTimeout(function()
        {
            iframe.find('thead').find('input').each(function()
            {
                if($(this).val())
                {
                    $(this).val('');
                    $(this).click();
                }
            })

            input.val(filteringValue);
            input.click();
        }, 800);

    });


    function getSearchParameters() {
        var prmstr = window.location.search.substr(1);
        return prmstr != null && prmstr != "" ? transformToAssocArray(prmstr) : {};
    }

    function transformToAssocArray( prmstr ) {
        var params = {};
        var prmarr = prmstr.split("&");
        for ( var i = 0; i < prmarr.length; i++) {
            var tmparr = prmarr[i].split("=");
            params[tmparr[0]] = tmparr[1];
        }
        return params;
    }


    $(document).on('init.dt', function ( e, settings )
    {

        var params = getSearchParameters();

        if((typeof params.filteringIndex !== 'undefined')&&(typeof params.filteringValue !== 'undefined'))
        {
            $('.dataTable').find('thead th[data-column="' + params.filteringIndex + '"] input').val(params.filteringValue);


            $('.dataTable').find('thead th[data-column=' + params.filteringIndex + '] input').val(decodeURI(params.filteringValue)).keyup();
        }

        var api = new $.fn.dataTable.Api( settings );

        let table = api.table().node();

        $(table).find('th.doubler').each(function()
        {
            let columnIndex = $(this).data('column');
            let columnName = $(this).data('camelname');

            let names = window.__getTableArrayNames(api, columnIndex);

            window.__showDuplicates(api, names, columnIndex, columnName);
        });
    });

    window.__showDuplicates = function(datatable, names, columnIndex, columnName)
    {
        datatable.rows().eq( 0 ).map( function (rowIdx)
        {
            var nameCell = datatable.cell( rowIdx, columnIndex );
            var name = String(nameCell.data());

            if(names[name] > 1)
            {
                $(nameCell.node()).addClass('doubled');

                $(nameCell.node()).closest('tr').addClass('double' + columnName);
            }
        });
    }

	window.__getTableArrayNames = function(datatable, columnIndex, dataIndex = null)
	{
		let names = [];

		datatable.rows().eq( 0 ).map( function (rowIdx)
		{
            var nameCell = datatable.cell( rowIdx, columnIndex );
            var name = String(nameCell.data());

			if(typeof names[name] === 'undefined')
				names[name] = 0;

			names[name] += 1;
		});

		return names;
	}
});
</script>