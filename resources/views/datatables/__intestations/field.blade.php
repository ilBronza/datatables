@if(empty($table->noSearch))

@php
	$baseId = $table->id . $position . $field->absoluteIndex;
@endphp

	@if($field->isRange())
		@include('datatables::datatables.__intestations._fieldRange')
	@else
		@include('datatables::datatables.__intestations._fieldNormal')
	@endif

@else

@lang('fields.' . $field->name)

@endif
