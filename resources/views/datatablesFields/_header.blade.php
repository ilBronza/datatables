{{-- <span class="uk-h6 uk-display-block uk-margin-remove-bottom uk-text-truncate" uk-tooltip="{{ $field->getTranslatedName() }}">{{ $field->getTranslatedName() }}</span>
 --}}



@if($field->isFilterable())
	{{-- this is to export header column names --}}
	<span class="uk-hidden">{{ $field->getTranslatedName() }}</span>
	{{-- END this is to export header column names --}}

<div class="headercell @if($field->hasRangeFilter()) range-filter @else single-filter @endif">
	@if($field->mustPrintIntestation())
		<span class="uk-text-truncate">{{ $field->getTranslatedName() }}</span>
	@endif

	@if(! $field->hasRangeFilter())
	<div class="datatablefilter">
		@include('datatables::datatablesFields.filters._' . $field->getFilterType())
	</div>
	@else
		<div class="range-filters-container">
		@include('datatables::datatablesFields.filters._range' . ucfirst($field->getFilterType()))
		</div>
	@endif
</div>
@else
<div class="headercell uk-text-nowrap">
	@if($field->showLabel())
		{{ $field->getTranslatedName() }}
	@endif
</div>
@endif