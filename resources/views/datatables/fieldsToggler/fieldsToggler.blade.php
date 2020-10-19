@if($table->mustShowColumnToggler())

<div class="fields-toggler-container" data-tableid="{{ $table->id }}">
	@include('datatables::datatables.fieldsToggler.button')

	<div id="toggle-usage{{ $table->id }}" {{-- hidden="hidden" --}}>

		@foreach($table->fieldsGroups as $index => $group)

		<div class="fields-toggler-group">

			@include('datatables::datatables.fieldsToggler.groupToggler')
			@include('datatables::datatables.fieldsToggler.fields')

		</div>

		@endforeach
	</div>
</div>

@endif