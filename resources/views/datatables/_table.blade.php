@include('datatables::datatables.scripts.table')

<div class="uk-table" id="table-container-{{ $table->id }}">

	@if($table->form)
	@include('forms._' . $table->form->type, [
		'action' => $table->form->action,
		'formId' => $table->form->id?? 'form' . $table->id
		])
	@endif

	@include('datatables::datatables.__topViews')	

	@include('datatables::datatables.__navbar')

	<div uk-grid>

		{{-- @include('datatables::datatables.__rowsCounter') --}}

		@include('datatables::datatables.__title')

	</div>

	<table
		class="{{ $maindatatable?? '' }} {{ $table->defaultFrameworkClasses }} datatable display {{ (!empty($table->noSearch)? 'no-ordering' : 'ordering') }} uk-table-justify uk-table-middle"
{{--         width="100%"
        style="table-layout: fixed; width:100%;"
 --}}        data-id="{{ $table->id }}"
        id="{{ $table->id }}"
        >

		@include('datatables::datatables.__tHead')

{{--
 		@if($table->mustRenderBody())
			@include('datatables::datatables.__tBody')
		@endif
 --}}		

 	@include('datatables::datatables.__tFoot')
		
	</table>

	@include('datatables::datatables.__bottomViews')

	@if($table->form)
    @include('forms._closure')
	@endif

</div>