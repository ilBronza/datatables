<tr class="columns" data-dt-order="disable">
	@foreach($table->getFields() as $field)
		@include('datatables::uikit.header.__headerFilter')
	@endforeach
</tr>
