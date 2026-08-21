@include('datatables::datatablesFields._tableSingleSpec')

@if($table->canEditColumnStyles() && $table->canHideColumns())
	@include('datatables::datatablesFields._columnSettingsForm')
@endif

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
		<div class="uk-offcanvas-bar uk-padding-small">

			<button class="uk-offcanvas-close" type="button" uk-close></button>

			<ul id="togglefields{{ $table->getId() }}"
				class="uk-nav uk-dropdown-nav ib-colvis-container toggle-vis-container table{{ $table->getId() }}"
				data-tableid="{{ $table->getId() }}"
				data-settings-store-url="{{ $table->getColumnVisibilityStoreUrl() }}"
			>
				@foreach($table->getFields() as $field)
					<li class="ib-colvis-item uk-flex uk-flex-wrap">
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

						@if($table->canEditColumnStyles())
							<a
									href="javascript:void(0)"
									class="ib-colvis-gear uk-width-auto"
							>
								<i class="fa-solid fa-gear"></i>
							</a>
						@endif
					</li>
				@endforeach
			</ul>
		</div>
	</div>

@endif

@php($hasSideExtraViews = ! request()->input('justTable', false) && $table->hasExtraViewsPositions(['left', 'right']))

@if($hasSideExtraViews)
	<div class="uk-grid-small" uk-grid>
		@if($table->hasExtraViewsPositions('left'))
			<aside class="uk-width-auto">
				{!! $table->renderExtraViews('left') !!}
			</aside>
		@endif

		<div class="uk-width-expand">
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
			{{-- data-cachedtablekey="{{ $table->getCachedTableKey() }}" --}}
			@endif

			@if($table->hasInlineCreate())
				data-store-url="{{ $table->getInlineCreateStoreUrl() }}"
				data-inline-create-url="{{ $table->getInlineCreateUrl() }}"
			@endif

			@if($table->getRelationName())
				data-relation="{{ $table->getRelationName() }}"
			@endif

			@if($channel = $table->getModelBroadcastChannel())
				data-model-broadcast-channel="{{ $channel }}"
				data-model-broadcast-model="{{ $table->modelClass }}"

				@if($createdFetchUrl = $table->getModelBroadcastCreatedFetchUrl())
					data-model-broadcast-created-fetch-url="{{ $createdFetchUrl }}"
				@endif

				@if($createdFetchRequestHook = $table->getModelBroadcastCreatedFetchRequestHook())
					data-model-broadcast-created-fetch-hook="{{ $createdFetchRequestHook }}"
				@endif
			@endif

			@if($refreshTableChannel = $table->getRefreshTableBroadcastChannel())
				data-refresh-table-broadcast-channel="{{ $refreshTableChannel }}"
			@endif

			@if($table->drawOnFieldsEvents())
				data-filter-draw-on-events="true"
			@endif

			data-editor-save-trigger="{{ config('datatables.editor.saveTrigger', 'enter') }}"
			data-editor-saved-feedback-duration="{{ max(0, (int) config('datatables.editor.savedFeedbackDuration', 1500)) }}"
			data-scroll-body-min-height="{{ config('datatables.scrollBodyMinHeight', 100) }}"

			@if($table->hasSummary())
				data-summary="true"
			@endif

			@if($table->usesColumnDisplay())
				data-columndisplayroute="{{ $table->getColumnDisplayRoute() }}"
			@endif
			data-uisettingsroute="{{ $table->getUiSettingsStoreUrl() }}"

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

@if($hasSideExtraViews)
		</div>

		@if($table->hasExtraViewsPositions('right'))
			<aside class="uk-width-auto">
				{!! $table->renderExtraViews('right') !!}
			</aside>
		@endif
	</div>
@endif

@if(! request()->input('justTable', false))
	{{-- @include('datatables::__extraViews', ['position' => 'bottom']) --}}

	@if($table->hasExtraViewsPositions('bottom'))
		{!! $table->renderExtraViews('bottom') !!}
	@endif

@endif
