@include('datatables::datatablesFields._tableSingleSpec')

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

	<div id="offcanvastogglefields{{ $table->getId() }}" uk-offcanvas>
		<div class="uk-offcanvas-bar">

			<button class="uk-offcanvas-close" type="button" uk-close></button>

			<ul id="togglefields{{ $table->getId() }}"
				class="uk-nav uk-dropdown-nav toggle-vis-container table{{ $table->getId() }}"
				data-tableid="{{ $table->getId() }}">
				@foreach($table->getFields() as $field)
					<li>
						<a
								href="javascript:void(0)"
								class="toggle-vis @if($field->isVisible()) uk-text-bold @endif {{ $field->getFieldName() }}"
								data-column="{{ $field->getIndex() }}"
								data-name="{{ $field->getFieldName() }}"
								data-visibility="{{ ($field->isVisible() ? 1 : 0) }}"
								style="color: black;"
						>
							{{ $field->getTranslatedName() }}
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
	<thead class="sectionheader @if($table->hasRangeFilter()) ranged-filters-table @endif">

	@if($table->hasMainHeader())
		<tr class="mainheader">
			@foreach($table->getFields() as $field)
				@if($field->hasMainHeader())
					<th class="{{ Str::slug($field->mainHeader['label']) }}"
						colspan="{{ $colspan = ($field->mainHeader['colspan'] ?? 1) }}">
						<span>{!! $field->mainHeader['label'] !!}</span>
					</th>
				@else
					@if(($colspan = (($colspan ?? 0) -1)) <= 0)
						<th></th>
					@endif
				@endif
			@endforeach
		</tr>
	@endif
	<tr class="columns">
		@foreach($table->getFields() as $field)
			@if(! $table->isFlatTable())
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
						@endif

						@if($field->isEditor() && $field->isNullable())
							data-nullable="true"
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
					{{--                    {{ $field->renderHeader() }}--}}

					@include('datatables::datatablesFields._header')
				</th>
			@else
				<th
						class="{{ $field->getHeaderHtmlClasses() }} {{ Str::slug($field->getTranslatedName()) }}"

						@if(config('datatables.useTooltips'))
							uk-tooltip="offset: 20; title: {{ $field->getTranslatedName() }}"
						@endif
				>
                    <span class="uk-text-truncate">
                        {{ $field->getTranslatedName() }}
                    </span>
				</th>
			@endif
		@endforeach
	</tr>

	@if($table->hasSummary())

		<tr class="summary" style="display: none;">
			@foreach($table->getFields() as $field)
				<th
						class="summary{{ $field->getIndex() }}"
						data-name="{{ $field->getFieldName() }}"
						data-column="{{ $field->getIndex() }}"
				>
				</th>
			@endforeach
		</tr>

		@if($table->hasInlineSearch())

			<tr class="inlinesearchsummary" style="display: none;">
				@foreach($table->getFields() as $field)
					<th
							class="summary{{ $field->getIndex() }}"
							data-name="{{ $field->getFieldName() }}"
							data-column="{{ $field->getIndex() }}"
					>
					</th>
				@endforeach
			</tr>

		@endif

	@endif


	</thead>

	@if($table->isFlatTable())
		@foreach($tableSourceData as $row)
			<tr>
				@foreach($row as $cell)
					<td>{!! $cell !!}</td>
				@endforeach
			</tr>
		@endforeach
	@endif
</table>

</div>

@if(! request()->input('justTable', false))
	{{-- @include('datatables::__extraViews', ['position' => 'bottom']) --}}

	@if($table->hasExtraViewsPositions('bottom'))
		{!! $table->renderExtraViews('bottom') !!}
	@endif

@endif



<script>
    jQuery(document).ready(function($)
    {
        (function ()
        {
            // se hai un footer/sticky bar in basso, indica la sua altezza qui
            var BOTTOM_OFFSET = 0; // es. 48 o 64; considera anche env(safe-area-inset-bottom) sui device mobili

            function fitWindowWrapper()
            {
                var el = document.querySelector('.dataTables_scrollBody');
                if (!el) return;

                // posizione dell’elemento rispetto al viewport ad ogni ricalcolo
                var rect = el.getBoundingClientRect();
                var safeInset = (typeof CSS !== 'undefined' && CSS.supports('padding-bottom: env(safe-area-inset-bottom)'))
                    ? (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--safe-area-bottom')) || 0)
                    : 0;

                var available = window.innerHeight - rect.top - BOTTOM_OFFSET - safeInset;
                // fallback di sicurezza
                if (available < 100) available = 100;

                el.style.maxHeight = (available - 100) + 'px';
            }

            // primo calcolo quando il DOM è pronto
            if (document.readyState === 'loading')
            {
                document.addEventListener('DOMContentLoaded', fitWindowWrapper, {once: true});
            } else
            {
                fitWindowWrapper();
            }

            // ricalcola su resize/zoom o cambi layout
            window.addEventListener('resize', fitWindowWrapper);

            // se esiste la DataTable, ricalcola anche dopo draw/colonne responsive
            function hookDataTables()
            {
                var dt = window.table{{ $table->getId() }};
                if (!dt || !dt.table) return;

                $('#{{ $table->getId() }}')
                    .on('init.dt draw.dt xhr.dt processing.dt page.dt order.dt search.dt responsive-resize.dt column-visibility.dt column-reorder.dt', function () {
                        // subito
                        fitWindowWrapper();
                        // e subito dopo l’aggiornamento del layout
                        setTimeout(fitWindowWrapper, 0);
                    });

                return true;
            }

            var iv = setInterval(function(){
                if (hookDataTables()) clearInterval(iv);
            }, 100);


            // opzionale: osserva cambi dimensione contenuto interno (aperture/chiusure pannelli, filtri, ecc.)
            try
            {
                var target = document.querySelector('.dataTables_scrollBody');
                if (target && 'ResizeObserver' in window)
                {
                    var ro = new ResizeObserver(function ()
                    {
                        fitWindowWrapper();
                    });
                    ro.observe(target);
                }
            } catch (e)
            {
            }
        })();

    });
</script>