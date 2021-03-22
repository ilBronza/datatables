<style type="text/css">
.doubled,
.doubled * 
{
	font-weight: bold!important;
	color: red!important;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function()
{
    $('body').on('mouseenter', '.ibop', function()
    {
        $(this).css('position', 'relative');

        var div = "<div class='fieldoperations'><span class='checkvisible' uk-icon='check'></span><span class='ban' uk-icon='ban'></span><span class='search' uk-icon='search'></span></span><span class='close' uk-icon='close'></span><span class='check uk-hidden' uk-icon='check'></span></div>";

        $(this).append(div);
    });

    $('body').on('mouseleave', '.ibop', function()
    {
        $(this).find('div').remove();
    });

    $('body').on('click', '.checkvisible', function()
    {
        checkVisibleRowsByCell(this, true);
    });

    $('body').on('click', '.check', function()
    {
        checkRowsByCell(this, true);
    });

    $('body').on('click', '.ban', function()
    {
        checkRowsByCell(this, false);
    });

    $('body').on('click', '.search', function()
    {
        $(this).find('div').remove();
        filterRowsByCell(this, true);
    });

    $('body').on('click', '.close', function()
    {
        $(this).find('div').remove();
        filterRowsByCell(this, false);
    });





    $(document).on('init.dt', function ( e, settings )
    {
        var api = new $.fn.dataTable.Api( settings );

        let table = api.table().node();

        $(table).find('th.doubler').each(function()
        {
            let columnIndex = $(this).data('column');
            let names = window.__getTableArrayNames(api, columnIndex);

            window.__showDuplicates(api, names, columnIndex);
        });
    });

    window.__showDuplicates = function(datatable, names, columnIndex)
    {
        datatable.rows().eq( 0 ).map( function (rowIdx)
        {
            var nameCell = datatable.cell( rowIdx, columnIndex );
            var name = String(nameCell.data());

            if(names[name] > 1)
                $(nameCell.node()).addClass('doubled');
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