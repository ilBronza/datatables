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
            if (typeof window.ibSyncClearFiltersButtonForTable === 'function') {
                window.ibSyncClearFiltersButtonForTable(tableId);
            }

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
        @if($table->hasReloadButton())
        {
            extend: 'reload',
            className: 'reload',
        }, @endif

			@if($table->hasRemoveFiltersButton())

        {
            text: '<i uk-tooltip="Rimuovi filtri" class="fa-solid fa-filter-circle-xmark"></i>',
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

                if (typeof window.ibSyncClearFiltersButtonForTable === 'function') {
                    window.ibSyncClearFiltersButtonForTable('{{ $table->getId() }}');
                }

                // ricarica la tabella senza filtri
                dt.search('').columns().search('').draw();
            }
        },

			@endif

		@if($table->hasMainHeader())
        {
            text: '<i uk-tooltip="{{ e(__('datatables::buttons.toggleMainHeaderTooltip')) }}" class="fa-solid fa-object-group"></i>',
            className: 'togglemainheader',
            action: function (e, dt, node, config) {
                const tableId = '{{ $table->getId() }}';
                const $rows = $('#' + tableId + '_wrapper').find('thead tr.mainheader');
                $rows.toggleClass('uk-hidden');
                const hidden = $rows.first().hasClass('uk-hidden');
                $(node).toggleClass('uk-button-primary', hidden);
                dt.columns.adjust();

                // persist UI setting
                try {
                    const $table = $('#' + tableId);
                    const url = $table.data('uisettingsroute');
                    if (url) {
                        $.ajax({
                            url: url,
                            dataType: 'json',
                            type: 'POST',
                            data: { key: 'mainHeaderHidden', value: hidden ? 1 : 0 }
                        });
                    }
                } catch (err) {}
            }
        },

		@endif

        {
            text: '<i class="fa-solid fa-filter-circle-xmark"></i> Nascondi ricerche',
            className: 'togglefilters',
            action: function (e, dt, node, config) {
                const tableId = '{{ $table->getId() }}';
                const $wrapper = $('#' + tableId + '_wrapper');

                const $headerFilterRow = $wrapper.find('thead tr.columns');
                const $footerFilterRow = $wrapper.find('tfoot tr.columns');

                $headerFilterRow.toggleClass('uk-hidden');
                $footerFilterRow.toggleClass('uk-hidden');

                if (typeof window.ibSetToggleFiltersButtonLabel === 'function') {
                    window.ibSetToggleFiltersButtonLabel($(node), tableId);
                }

                dt.columns.adjust();

                // persist UI setting
                try {
                    const $table = $('#' + tableId);
                    const url = $table.data('uisettingsroute');
                    if (url) {
                        $.ajax({
                            url: url,
                            dataType: 'json',
                            type: 'POST',
                            data: { key: 'filtersHidden', value: ($headerFilterRow.first().hasClass('uk-hidden') ? 1 : 0) }
                        });
                    }
                } catch (err) {}
            }
        },

		@if($table->hasSelectFilteredButton())
        {
            extend: 'selectAll',
			text: '<i uk-tooltip="Seleziona tutti" class="fa-regular fa-circle-check"></i> Seleziona tutti',
            className: 'selectall',
        },
        {
            extend: 'selectNone',
			text: '<i uk-tooltip="Deseleziona tutti" class="fa-regular fa-circle-xmark"></i> Deseleziona tutti',
        className: 'selectnone',
        },
        {
            extend: 'selectedRows',
			text: '<i uk-tooltip="Solo selezionate" class="fa-solid fa-eye"></i> Solo selezionate',
            className: 'selectedrows',
        },
		@endif

{{--		@if($table->hasDoublerButton())--}}
{{--            'doubler',--}}
{{--		@endif--}}

			@if($table->hasSummary())
        {
            extend: 'removeSummary',
            className: 'removesummary',
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

		@if($table->hasCsvButton())
        {
            extend: 'csv',
            className: 'csv uk-hidden ib-dt-hidden-export',
            exportOptions: {
                orthogonal: 'export'
            }
        },
		@endif

		@if($table->hasExcelButton())
        {
            extend: 'excel',
            className: 'excel uk-hidden ib-dt-hidden-export',
            exportOptions: {
                orthogonal: 'export'
            }
        },
		@endif

		@if($table->hasCopyButton())
        {
            extend: 'copy',
            className: 'copy uk-hidden ib-dt-hidden-export',
        },
		@endif

		@if($table->canHideColumns())
        {
            extend: 'fieldsVisibility',
            className: 'fieldsvisibility uk-hidden ib-dt-hidden-export',
        },
		@endif

        {
            extend: 'utils',
            className: 'utils'
        },
    ];

    window.__ibColumnStyleDraftByTable = window.__ibColumnStyleDraftByTable || {};

    window.__ibColumnStyleDraftByTable['{{ $table->getId() }}'] = {!! json_encode($table->getDatatableUserData()->columnsSettings ?? [])  !!};

    window.__ibDatatableFieldSettings = window.__ibDatatableFieldSettings || {};
    window.__ibDatatableFieldSettings['{{ $table->getId() }}'] = {!! json_encode($table->getFieldSettingsArray()) !!};

    window.__ibDatatableFieldsGroups = window.__ibDatatableFieldsGroups || {};
    window.__ibDatatableFieldsGroups['{{ $table->getId() }}'] = {!! json_encode($table->getFieldsGroupsDefinitions()) !!};

    window.__ibDatatableColumnIndexToFieldName = window.__ibDatatableColumnIndexToFieldName || {};
    window.__ibDatatableColumnIndexToFieldName['{{ $table->getId() }}'] = {!! json_encode(
        $table->getFields()->mapWithKeys(function ($field) {
            return [$field->getIndex() => $field->getFieldName()];
        })->all()
    ) !!};

    window.__ibDatatableUiSettings = window.__ibDatatableUiSettings || {};
    window.__ibDatatableUiSettings['{{ $table->getId() }}'] = {!! json_encode($table->getDatatableUserData()->uiSettings ?? []) !!};

    window.__ibDatatableUtilsButtons = window.__ibDatatableUtilsButtons || {};
    window.__ibDatatableUtilsButtons['{{ $table->getId() }}'] = [
			@if($table->hasCopyButton())
        'copy',
			@endif
			@if($table->hasCsvButton())
        'csv',
			@endif
			@if($table->hasExcelButton())
        'excel',
			@endif
			@if($table->canHideColumns())
        'fieldsVisibility',
			@endif
        'fieldsGroups',
    ];

</script>

@include('datatables::datatablesFields.__tableSingleSpecCss')
@include('datatables::datatablesFields.__tableSingleSpecRangeFilters')
@include('datatables::datatablesFields.__tableSingleSpecFieldOperations')
