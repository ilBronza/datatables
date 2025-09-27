<tr class="columns">
	@foreach($table->getFields() as $field)
		@include('datatables::uikit.header.__headerFilter')
	@endforeach
</tr>
