<th
		class="{{ $field->getFooterHtmlClasses() }} {{ Str::slug($field->getTranslatedName()) }}"

		data-ajaxExtraData="{{ $field->getJsonAjaxExtraData() }}"

		data-range-filter="{{ $field->hasRangeFilter() }}"
		data-filter-type="{{ $field->getFilterType() }}"
		data-filterable="{{ $field->isFilterable() }}"
		data-filter-events="{{ $field->getJqueryFilterEventsString() }}"
		data-filter-draw-on-events="{{ $field->canDrawTable() }}"
		data-filter-draw-on-keyup="{{ $field->canDrawKeyup() }}"
		data-name="{{ $field->getFieldName() }}"
		data-camelName="{{ $field->getCamelName() }}"
		data-column="{{ $field->getIndex() }}"

		@foreach($field->getFooterData() as $data => $value)
			data-{{ $data }}="{{ $value }}"
		@endforeach

		@if($field->getFilteredTable())
			data-filteredTable="{{ $field->getFilteredTable() }}"
		@endif
>
	<div class="footercell @if($field->hasRangeFilter()) range-filter @else single-filter @endif">
		@if(! $field->hasRangeFilter())
			<div class="datatablefilter">
				@include('datatables::datatablesFields.filters._' . $field->getFilterType(), ['isFooter' => true])
			</div>
		@else
			<div class="range-filters-container">
				@include('datatables::datatablesFields.filters._range' . ucfirst($field->getFilterType()), ['isFooter' => true])
			</div>
		@endif
	</div>

	@include('datatables::datatablesFields.filters.filterfunctions')
</th>
