<span class="uk-h4">{{ $field->getTranslatedName() }}</span>

<div>
	@if(! $field->hasRangeFilter())
		@include('datatables::datatablesFields.filters._' . $field->getFilterType())
	@else
		@include('datatables::datatablesFields.filters._range' . ucfirst($field->getFilterType()))
	@endif
</div>
