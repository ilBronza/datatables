<script type="text/javascript">

	@if($table->isArrayTable())
        window.{{ $table->getId() }}dataset = {!! json_encode($tableSourceData) !!};
	@endif

        window.{{ $table->getId() }}FilteredSelected = false;

    window.{{ $table->getId() }}rowReorder = @isset($table->dragAndDrop) {

		@if(isset($table->dragAndDrop->selector))
        selector: '{{ $table->dragAndDrop->selector }}',
        dataSrc: {{ $table->dragAndDrop->dataSrc ?? 1 }},

		@endif

    }
	@else false @endisset ;

    window.{{ $table->getId() }}options = {

            @if($table->hasFixedColumns())
        		scrollX: false,

			@if(!! $table->getFixedColumnsLeft())
			fixedColumns: {
				leftColumns: {{ $table->getFixedColumnsLeft() }}
			},
			@endif

        @endif

		@if($table->hasRemoveFiltersButton())

        drawCallback: function (settings) {
            const tableId = '{{ $table->getId() }}';
            const wrapper = $('#' + tableId + '_wrapper');
            const infoText = wrapper.find('.dataTables_info').text();
            const button = wrapper.find('button.clearfilters');

            const filterMarker = 'filtrati da'; // se tradotto, usa direttamente la stringa localizzata
            const filtersActive = infoText.includes(filterMarker);

            button.toggleClass('uk-button-danger', filtersActive);

            //HIGHLIGHT VISITED LINKS
			if (typeof window.highlightVisitedLinks === 'function') {
				window.highlightVisitedLinks();
			}
        },

		@endif

        @if($table->hasSaveState())
        stateSave: true,

        stateSaveParams: function (settings, data) {
                // salva i valori dei filtri custom
                data.filters = {};

                $('#{{ $table->getId() }} .columns input').each(function ()
                {
                    data.filters[$(this).attr('name') || $(this).attr('id')] = $(this).val();
                });
            },

        stateLoadParams: function (settings, data) {

            // ripristina i valori visivi
            setTimeout(function () {

                $("table[data-realid='{{ $table->getId() }}'] .columns input").each(function () {

                    const key = $(this).attr('name') || $(this).attr('id');

                    if (data.filters[key]) {

                        $(this).val(data.filters[key]).change();

{{--                         let field = this;

                        // Triggers per compatibilità
                        setTimeout(function ()
                        {
                            $(field).trigger('change');
                        }, 100); --}}

                        $(this).addClass('filter-filled');
                    }
                });

                // Forza il ricalcolo
                const tableInstance = $('#{{ $table->getId() }}').DataTable();

                tableInstance.draw();
			}, 0);
        },

		@endif

		@if($table->hasFixedTableHeader())
        fixedHeader: true,
		@endif

        //SCROLLX genera parecchi problemi con il grab della tabella HTML se attivo. non trova più i data etc
        //TODO

        /**
		 *
		 *
		 * occhio che se scroll Y perde il mainheader sulle tabelle del relation manager, mistero non si sa perché zio culofreno
         */

		@if($table->canScrollX())

            @if($table->canScrollY())
			scrollY: '600px', /* valore qualsiasi iniziale, anche 200 */
            @endif

			scrollCollapse: true,
			autoWidth: false,

        	scrollX: {{ $table->canScrollX() ? 'true' : 'false' }},
		@endif

				@if($caption = $table->getCaption())
        captionText: "{!! $caption !!}",
		@endif
        keys: true,
        language: {
            thousands: "{{ config('datatables.thousandsSeparator', '.') }}",
            lengthMenu: "{!! __('datatables::datatables.show_MENU_entries') !!}",
            infoFiltered : "{!! __('datatables::datatables.infoFiltered') !!}",
            info: "{!! __('datatables::datatables.showing_START_to_END_of_TOTAL_entries') !!}",
            search: "{!! __('datatables::datatables.generalSearchTitle') !!}",
            searchPlaceholder: "{!! __('datatables::datatables.searchPlaceholder') !!}",
            paginate: {
                first: "{!! __('datatables::datatables.first') !!}",
                last: "{!! __('datatables::datatables.last') !!}",
                next: "{!! __('datatables::datatables.next') !!}",
                previous: "{!! __('datatables::datatables.previous') !!}",
            },
        },

		@if($dom = $table->getCustomDom())
        dom: '{!! $dom !!}',
		@endif

		@if($table->isAjaxTable())
        //ajaxxalo
        ajax: {
            url: window.addParameterToURL(window.addParameterToURL("{{ $table->getUrl() }}", 'cachedtablekey', "{{ $table->getCachedTableKey() }}"), 'model', '{{ $table->getRelationName() }}'),
			type: '{{ $table->getAjaxMethod() }}',
            dataSrc: function (json)
            {
                let data = window.transformDataBySummaryExistence("{{ $table->getId() }}", "{{ $table->hasSummary() }}", json);

                return data;
            }
        },
		{{-- @elseif($table->isArrayTable()) --}}
        //arrayalo
		@else
        data: window.{{ $table->getId() }}dataset,
		@endif

				@if($table->hasSelectRowCheckboxes())
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
		@endif

		@if($scripts = $table->getCreatedRowScripts())

        createdRow: function (row, data, dataIndex)
        {
            //start createdRow
			@foreach ($scripts as $script)
						{!! $script !!}
					@endforeach

			//end createdRow
        },
		@endif

        pageLength: {{ $table->getPageLength() }},

		@if($table->getRowIdIndex() !== null)
        rowId: {{ $table->getRowIdIndex() }},
		@endif

				@foreach($table->options as $name => $value)
				@if($name == 'order')
        "{{ $name }}":
            [
                //{!! json_encode($value) !!}
				@foreach($value as $index => $order)
				{!! json_encode($order) !!}@if(! $loop->last),@endif
				@endforeach

                ],
				@else
                    "{{ $name }}"
    :
	{!! json_encode($value) !!},
	@endif
	@endforeach
    }


    // window.{{ $table->getId() }}options = @if(count($table->options)) {
    //     @foreach($table->options as $name => $value)

    //     @if($name == 'order')
    //     "{{ $name }}" : 
    //     [
    //         @foreach($value as $index => $order)

    //         {!! json_encode($order) !!}@if(! $loop->last),
    //             @endif
    //         @endforeach

    //     ],
    //     @else
    //     "{{ $name }}" : {!! json_encode($value) !!},
    //     @endif

    // @endforeach
    // }
    // @endif ;

    window.{{ $table->getId() }}columnDefs = [
        {
            // "targets": 'no-sort',
            "targets": '_all',
            "orderable": false
        }@if(count($table->columnDefs)||(count($table->customColumnDefs))),

			@if($table->hasSelectRowCheckboxes())
        {
            orderable: false,
            // className: 'select-checkbox',
            render: $.fn.dataTable.render.select(),
            targets: 0,
        },
			@endif
			@foreach ($table->columnDefs as $type => $element)
			@foreach($element->values as $key => $value)
			@if($key == '')
        {
            "{{ $type }}": false, "targets": {{ json_encode($value) }}
        },
			@elseif($key == 1)
        {
            "{{ $type }}": true, "targets": {{ json_encode($value) }}
        },
			@elseif($key == 'dom-text-numeric')
        {
            "{{ $type }}": "{{ $key }}", "sType": "numeric", "targets": {{ json_encode($value) }}
        },
			@else
        {
            "{{ $type }}": "{{ $key }}", "targets": {{ json_encode($value) }}
        },
		@endif
		@endforeach
		@endforeach

        //count columnDefs = {{ count($table->customColumnDefs) }}

		@foreach ($table->customColumnDefs as $customColumnDef)

        //INDEX LOOP {{ $loop->index }}

		{!! $customColumnDef !!},
		@endforeach
		@endif ];

    window.{{ $table->getId() }}buttons = [

        {
            extend: 'fieldsVisibility',
            className: 'fieldsvisibility',
        },

			@if($table->hasRemoveFiltersButton())

        {
            text: 'Rimuovi filtri',
            className: 'clearfilters',
            action: function (e, dt, node, config) {
                // reset input e select nei thead e tfoot
                $("table[data-realid='{{ $table->getId() }}'] .columns input").each(function ()
                {
                    $(this).val('').removeClass('filter-filled');
                });

                // reset eventuali filtri range custom
                Object.keys(window).forEach(function (key) {
                    if (key.startsWith('range{{ $table->getId() }}')) {
                        delete window[key];
                    }
                });

                // ricarica la tabella senza filtri
                dt.search('').columns().search('').draw();
            }
        },

			@endif

		@if($table->hasSearchButton())
        // 'search',
			@endif

			@if($table->hasReloadButton())
        {
            extend: 'reload',
            className: 'reload',
        },
			@endif

			@if($table->hasSelectFilteredButton())
        {
            className: 'selectfiltered',
            text: '{{ trans('datatables::buttons.selectFiltered') }}',
            action: function (e, dt, node, config)
            {
                dt.rows({search: 'applied'}).select();
            }
        },
        // 'selectAll',
        {
            extend: 'selectNone',
            className: 'selectnone',
        },
        {
            extend: 'selectedRows',
            className: 'selectedrows',
        },
		@endif

				@if($table->hasDoublerButton())
            'doubler',
			@endif

			@if($table->hasSummary())
        {
            extend: 'removeSummary',
            className: 'removesummary',
        },
		@if($table->hasInlineSearch())
        	'removeInlineSearch',
			@endif
			@endif

			@if($table->hasCopyButton())
        {
            extend: 'copy',
            className: 'copy',
        },
			@endif

			@if($table->hasCsvButton())
        {
            extend: 'csv',
            className: 'csv',
            exportOptions: {
                orthogonal: 'export'
            }
        },
		@endif

				@foreach ($table->getButtons() as $button)
				@if(is_string($button))
            '{{ $button }}' @if(! $loop->last), @endif
				@elseif(is_array($button))
				{!! json_encode($button) !!} @if(! $loop->last), @endif
			@else
        {
            attr: {
				@foreach($button->getAttributes() as $attribute => $value)
                '{{ $attribute }}': '{{ $value }}',
				@endforeach
						@foreach($button->getData() as $data => $value)
                'data-{{ $data }}': '{{ $value }}',
				@endforeach
						@if($button->getId())
                id: '{{ $button->getId() }}',
				@endif
            },
			@if($buttonClasses = $button->getHtmlClassesString())
            className: '{{ $buttonClasses }}',
			@else
            className: '{{ Str::slug($button->getText()) }}',
			@endif
            text: '{{ $button->getText() }}@if($button->hasIcon()) {!! $button->renderIcon() !!}@endif',
			@if($jsMethodText = $button->renderJsMethod())
            action: function (e, dt, node, config)
            {
				{!! $jsMethodText !!}
            }
			@endif
        },
		@endif
		@endforeach
        {
            // Save all dirty inline-editor inputs for this table (visibility handled by JS via uk-hidden)
            text: '{{ __('datatables::buttons.save') ?? 'Save' }}',
            className: 'ib-save-dt-inputs uk-hidden',
            attr: {
                id: 'ib-save-dt-inputs-{{ $table->getId() }}'
            },
            action: function (e, dt, node, config) {
                // Handled by delegated click listener in datatables.vendor.ajaxButton.min.js
                e.preventDefault();
            }
        },
    ];

    window.__ibColumnStyleDraftByTable = window.__ibColumnStyleDraftByTable || {};

    window.__ibColumnStyleDraftByTable['{{ $table->getId() }}'] = {!! json_encode($table->getDatatableUserData()->columnsSettings ?? [])  !!};

</script>

@include('datatables::datatablesFields.__tableSingleSpecCss')
@include('datatables::datatablesFields.__tableSingleSpecRangeFilters')
@include('datatables::datatablesFields.__tableSingleSpecFieldOperations')
