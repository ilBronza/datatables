<tr class="columnsintestations">
	@foreach($table->getFields() as $field)
		@include('datatables::uikit.header.__headerTH')
	@endforeach
</tr>
