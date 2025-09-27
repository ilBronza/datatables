<thead class="sectionheader @if($table->hasRangeFilter()) ranged-filters-table @endif">

@if($table->hasMainHeader())
	@include('datatables::uikit.header._mainHeader')
@endif

@include('datatables::uikit.header.__headerTHs')
@include('datatables::uikit.header.__headerFilters')

@if($table->hasSummary())
	@include('datatables::uikit.header._headerSummary')
@endif

</thead>
