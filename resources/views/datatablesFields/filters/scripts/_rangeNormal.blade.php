<script type="text/javascript">

$.fn.dataTable.ext.search.push(
	function( settings, data, dataIndex, rowData)
	{
		if('{{ $tableId }}' != settings.nTable.getAttribute('id'))
			return true;

		var min = $('#{{ $field->getId() }}start').data('value');
		var max = $('#{{ $field->getId() }}end').data('value');

		var value = rowData[{{ $field->getIndex() }}];

		if ( ( isNaN( min ) && isNaN( max ) ) ||
			( isNaN( min ) && value <= max ) ||
			( min <= value   && isNaN( max ) ) ||
			( min <= value   && value <= max ) )
			{
				return true;
			}

		return false;
});

</script>