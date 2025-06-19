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


		@if($table->hasRemoveFiltersButton())

        drawCallback: function (settings) {
            const tableId = '{{ $table->getId() }}';
            const wrapper = $('#' + tableId + '_wrapper');
            const infoText = wrapper.find('.dataTables_info').text();
            const button = wrapper.find('button.clearfilters');

            const filterMarker = 'filtrati da'; // se tradotto, usa direttamente la stringa localizzata
            const filtersActive = infoText.includes(filterMarker);

            button.toggleClass('uk-button-danger', filtersActive);
        },

		@endif

        @if($table->hasSaveState())
        stateSave: true,

        stateSaveParams: function (settings, data) {
                // salva i valori dei filtri custom
                data.filters = {};
                $('#{{ $table->getId() }} thead input, #{{ $table->getId() }} tfoot input').each(function () {
                    data.filters[$(this).attr('name') || $(this).attr('id')] = $(this).val();
                });
            },

        stateLoadParams: function (settings, data) {

            // ripristina i valori visivi
            setTimeout(function () {

                console.log('data.filters');
                console.log(data.filters);

                if (data.filters) {
                    $('#{{ $table->getId() }} thead input, #{{ $table->getId() }} tfoot input').each(function () {
                        const key = $(this).attr('name') || $(this).attr('id');
                        if (data.filters[key] !== undefined) {
                            $(this).val(data.filters[key]);

                        if ($(this).attr('type') === 'date') {
                            $(this).trigger('change');
                        } else {
                            $(this).trigger('input');
                        }

                            console.log('this');
                            console.log(this);

                            $(this).trigger('change');
                            $(this).trigger('input');

                            {{-- $(this).trigger('input'); // forza DataTables a rileggere il filtro --}}

                            $(this).toggleClass('filter-filled', !!data.filters[key]);
                        }
                    });
                }
            }, 0);
        },

		@endif

		@if($table->hasFixedTableHeader())
        fixedHeader: true,
		@endif

        //SCROLLX genera parecchi problemi con il grab della tabella HTML se attivo. non trova più i data etc
        //TODO

		{{--		@if($table->canScrollX())--}}
				{{--		scrollX: {{ $table->canScrollX() ? 'true' : 'false' }},--}}
				{{--		@endif--}}

				@if($caption = $table->getCaption())
        caption: "{!! $caption !!}",
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
        dom: '{!! $dom !!}', //sgarruio
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

        //uash uash
        createdRow: function (row, data, dataIndex)
        {
			@foreach ($scripts as $script)
					{!! $script !!}
					@endforeach
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
            "targets": 'no-sort',
            "orderable": false
        }@if(count($table->columnDefs)||(count($table->customColumnDefs))),

			@if($table->hasSelectRowCheckboxes())
        {
            orderable: false,
            className: 'select-checkbox',
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
                $('#{{ $table->getId() }} thead input, #{{ $table->getId() }} tfoot input, #{{ $table->getId() }} thead select, #{{ $table->getId() }} tfoot select').each(function () {
                    $(this).val('').removeClass('filter-filled').trigger('input').trigger('change');
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
        // 'removeInlineSearch',
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
        } @if(! $loop->last), @endif
		@endif
		@endforeach
    ];

</script>

@include('datatables::datatablesFields.__tableSingleSpecCss')
@include('datatables::datatablesFields.__tableSingleSpecRangeFilters')
@include('datatables::datatablesFields.__tableSingleSpecFieldOperations')
