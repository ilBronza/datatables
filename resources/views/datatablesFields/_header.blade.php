{{-- <span class="uk-h6 uk-display-block uk-margin-remove-bottom uk-text-truncate" uk-tooltip="{{ $field->getTranslatedName() }}">{{ $field->getTranslatedName() }}</span>
 --}}

@if($field->isFilterable())
<div>
	@if(! $field->hasRangeFilter())
	<div class="datatablefilter">
		@include('datatables::datatablesFields.filters._' . $field->getFilterType())
	</div>
	@else
		@include('datatables::datatablesFields.filters._range' . ucfirst($field->getFilterType()))
	@endif
</div>
@else
<div>
	{{ $field->getTranslatedName() }}
</div>
@endif