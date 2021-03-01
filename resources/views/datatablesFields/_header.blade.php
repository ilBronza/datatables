{{-- <span class="uk-h4 uk-display-block uk-margin-remove-bottom uk-text-truncate" uk-tooltip="{{ $field->getTranslatedName() }}">{{ $field->getTranslatedName() }}</span>

 --}}<div  uk-tooltip="{{ $field->getTranslatedName() }}">
	@if(! $field->hasRangeFilter())
	<div class="datatablefilter">
		@include('datatables::datatablesFields.filters._' . $field->getFilterType())
	</div>
	@else
		@include('datatables::datatablesFields.filters._range' . ucfirst($field->getFilterType()))
	@endif
</div>
