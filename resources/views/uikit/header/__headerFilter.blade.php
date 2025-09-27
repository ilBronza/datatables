	<th
			@if(config('datatables.useTooltips'))
				uk-tooltip="offset: 20; title: {{ $field->getTranslatedName() }}"
			@endif

			class="{{ $field->getHeaderHtmlClasses() }} {{ Str::slug($field->getTranslatedName()) }}"

			data-ajaxExtraData="{{ $field->getJsonAjaxExtraData() }}"

			@if($field->isEditor())

				@if($field->hasSaveButton())
					data-editorsavebutton="true"
			@endif

			@if($field->isNullable())
				data-nullable="true"
			@endif

			@endif

			data-form-id="{{ $field->getFormId() }}"
			data-showDuplicates="{{ $field->hasDoubler() }}"
			data-range-filter="{{ $field->hasRangeFilter() }}"
			data-filter-type="{{ $field->getFilterType() }}"
			data-filterable="{{ $field->isFilterable() }}"
			data-filter-events="{{ $field->getJqueryFilterEventsString() }}"
			data-filter-draw-on-events="{{ $field->canDrawTable() }}"
			data-filter-draw-on-keyup="{{ $field->canDrawKeyup() }}"
			data-name="{{ $field->getFieldName() }}"
			data-label="{{ $field->getTranslatedName() }}"
			data-camelName="{{ $field->getCamelName() }}"
			data-column="{{ $field->getIndex() }}"

			@foreach($field->getHeaderData() as $data => $value)
				data-{{ $data }}="{{ $value }}"
			@endforeach

			@if($field->getFilteredTable())
				data-filteredTable="{{ $field->getFilteredTable() }}"
			@endif
	>
        	<div class="headercell @if($field->hasRangeFilter()) range-filter @else single-filter @endif">
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
		@include('datatables::datatablesFields.filters.filterfunctions')
	</th>
