@if(false)
<script type="text/javascript">
@endif

    //"rowCallback": function(row, data, index)

    var cellValue = data[{{ $field->absoluteIndex }}];
    var api = this.api();
    var node = api.cell(index, {{ $field->absoluteIndex }}).node();

    var difference = moment().diff(moment.unix(cellValue), 'days');

    //scaduto
	if(difference >= 0)
		$(node).css('background-color', 'rgba(255, 0, 0, 1)');

	else if(difference > -5)
		$(node).css('background-color', 'rgba(255, 0, 0, 0.8)');

	else if(difference > -15)
		$(node).css('background-color', 'rgba(255, 0, 0, 0.6)');

	else if(difference > -30)
		$(node).css('background-color', 'rgba(255, 0, 0, 0.4)');

	else if(difference > -60)
		$(node).css('background-color', 'rgba(255, 0, 0, 0.2)');


@if(false)
</script>
@endif

