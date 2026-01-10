@include('datatables::datatablesFields._tableSingleSpec')

@include('datatables::datatablesFields._columnSettingsForm')

@if(! request()->input('justTable', false))
	{{-- @include('datatables::__extraViews', ['position' => 'top']) --}}

	@if($table->hasExtraViewsPositions('top'))
		{!! $table->renderExtraViews('top') !!}
	@endif

@endif

@if($table->hasForm())

	{!! $table->getForm()->_render() !!}

@endif

@if($table->canHideColumns())

	<button hidden class="colvisbutton" id="offcanvastogglefieldsbutton{{ $table->getId() }}"
			uk-toggle="target: #offcanvastogglefields{{ $table->getId() }}">Fields visibility
	</button>

	<div id="offcanvastogglefields{{ $table->getId() }}" uk-offcanvas="mode: push">
		<div class="uk-offcanvas-bar">

			<button class="uk-offcanvas-close" type="button" uk-close></button>

			<ul id="togglefields{{ $table->getId() }}"
				class="uk-nav uk-dropdown-nav toggle-vis-container table{{ $table->getId() }}"
				data-tableid="{{ $table->getId() }}"
			>
				@foreach($table->getFields() as $field)
					<li class="ib-colvis-item uk-flex">
						<a
								href="javascript:void(0)"
								class="uk-width-expand toggle-vis @if($field->isVisible()) uk-text-bold @endif {{ $field->getFieldName() }}"
								data-column="{{ $field->getIndex() }}"
								data-name="{{ $field->getFieldName() }}"
								data-visibility="{{ ($field->isVisible() ? 1 : 0) }}"
								style="color: black;"
						>
							{{ $field->getTranslatedName() }}
						</a>

						<a
								href="javascript:void(0)"
								class="ib-colvis-gear uk-width-auto"
						>
							<i class="fa-solid fa-gear"></i>
						</a>
					</li>
				@endforeach
			</ul>
		</div>
	</div>

@endif

<div class="uk-width-auto">

	<table
			id="{{ $table->getId() }}"

			data-realid="{{ $table->getId() }}"

			@if($table->getRowIdIndex() !== null)
				data-rowid="{{ $table->getRowIdIndex() }}"
			@endif

			@if(isset($table->dragAndDrop->url))
				data-storemasssortingurl="{{ $table->dragAndDrop->url }}" ,
			@endif


			@if($table->isAjaxTable())
				data-url="{{ $table->getUrl() }}"
			data-cachedtablekey="{{ $table->getCachedTableKey() }}"
			@endif

			@if($table->getRelationName())
				data-relation="{{ $table->getRelationName() }}"
			@endif

			@if($table->drawOnFieldsEvents())
				data-filter-draw-on-events="true"
			@endif

			@if($table->hasSummary())
				data-summary="true"
			@endif

			@if($table->usesColumnDisplay())
				data-columndisplayroute="{{ $table->getColumnDisplayRoute() }}"
			@endif

			{!! $table->getDomStickynessDataAttribute() !!}

			class="wannabedatatable {{ $table->getStripeClass() }} datatable {{ $table->getName() }} {{ $table->getHtmlClassesString() }}"
			style="table-layout: auto;"
	>
		@include('datatables::uikit.header._header')

		@if($table->isFlatTable())
			@foreach($tableSourceData as $row)
				<tr>
					@foreach($row as $cell)
						<td>{!! $cell !!}</td>
					@endforeach
				</tr>
			@endforeach
		@endif

		@if($table->hasFooterFilters())
			@include('datatables::uikit.footer._footer')
		@endif

	</table>

</div>

@if(! request()->input('justTable', false))
	{{-- @include('datatables::__extraViews', ['position' => 'bottom']) --}}

	@if($table->hasExtraViewsPositions('bottom'))
		{!! $table->renderExtraViews('bottom') !!}
	@endif

@endif


