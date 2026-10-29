@if($table->hasHeaderStylesToggleButton())
    {
        text: '<i class="fa-solid fa-text-width" aria-hidden="true"></i>',
        className: 'toggleheaderstyles{{ $table->areHeaderStylesExpanded() ? ' uk-button-primary' : '' }}',
        attr: {
            'aria-pressed': '{{ $table->areHeaderStylesExpanded() ? 'true' : 'false' }}',
            'aria-label': {!! json_encode(__($table->areHeaderStylesExpanded() ? 'datatables::buttons.truncateHeaders' : 'datatables::buttons.expandHeaders')) !!},
            'uk-tooltip': 'title: ' + {!! json_encode(e(__($table->areHeaderStylesExpanded() ? 'datatables::buttons.truncateHeaders' : 'datatables::buttons.expandHeaders'))) !!} + '; pos: top',
        },
        action: function (e, dt, node) {
            const tableId = {!! json_encode($table->getId()) !!};
            const expanded = $(node).attr('aria-pressed') !== 'true';
            const styleId = 'ib-header-styles-' + tableId;
            let style = document.getElementById(styleId);
            if (!style) {
                style = document.createElement('style');
                style.id = styleId;
                style.setAttribute('data-ib-header-styles-mode', '');
                document.head.appendChild(style);
            }
            style.textContent = expanded
                ? {!! json_encode(\IlBronza\Datatables\Support\HeaderStyles::modeCss($table->getId(), true)) !!}
                : {!! json_encode(\IlBronza\Datatables\Support\HeaderStyles::modeCss($table->getId(), false)) !!};

            const label = expanded
                ? {!! json_encode(e(__('datatables::buttons.truncateHeaders'))) !!}
                : {!! json_encode(e(__('datatables::buttons.expandHeaders'))) !!};
            $(node).attr('aria-pressed', expanded ? 'true' : 'false')
                .attr('aria-label', label)
                .attr('uk-tooltip', 'title: ' + label + '; pos: top')
                .toggleClass('uk-button-primary', expanded);

            const tooltip = window.UIkit && window.UIkit.getComponent($(node)[0], 'tooltip');
            if (tooltip) {
                tooltip.hide();
            }

            window.__ibDatatableUiSettings = window.__ibDatatableUiSettings || {};
            window.__ibDatatableUiSettings[tableId] = window.__ibDatatableUiSettings[tableId] || {};
            window.__ibDatatableUiSettings[tableId].headerStylesExpanded = expanded;

            dt.columns.adjust();
            if (dt.fixedHeader && typeof dt.fixedHeader.adjust === 'function') {
                dt.fixedHeader.adjust();
            }
            window.dispatchEvent(new Event('resize'));

            const url = dt.table().node().getAttribute('data-uisettingsroute');
            if (url) {
                $.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'POST',
                    data: { key: 'headerStylesExpanded', value: expanded ? 1 : 0 }
                });
            }
        }
    },
@endif
