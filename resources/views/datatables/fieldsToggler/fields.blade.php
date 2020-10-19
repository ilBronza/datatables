<div class="fields-container-parent">
	<div class="fields-container">

		@foreach($group->fields as $field)
			@include('datatables::datatables.fieldsToggler._field')
		@endforeach

	</div>
</div>