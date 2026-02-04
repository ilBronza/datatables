<thead class="sectionheader @if($table->hasRangeFilter()) ranged-filters-table @endif">

@if($table->hasMainHeader())
	@include('datatables::uikit.header._mainHeader')
@endif

@include('datatables::uikit.header.__headerTHs')
@include('datatables::uikit.header.__headerFilters')

@include('datatables::uikit.header._headerSummary')

</thead>
