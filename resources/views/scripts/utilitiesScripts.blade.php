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
    $(document).on('init.dt', function ( e, settings ) {

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