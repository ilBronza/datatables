(function registerIlBronzaDatatablesAsyncColumns($) {
    'use strict';

    if (!$ || !$.fn.dataTable || !$.fn.dataTable.Api)
        return;

    window.ibAsyncRenderers = window.ibAsyncRenderers || {};
    window.ibAsyncColumnState = window.ibAsyncColumnState || {};
    window.ibAsyncCellsCache = window.ibAsyncCellsCache || {};

    function escapeHtml(value) {
        return String(value === null || typeof value === 'undefined' ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.ibRegisterAsyncRenderer = function (name, renderer) {
        if (!name || typeof renderer !== 'function')
            throw new TypeError('Un renderer async richiede nome e funzione');

        window.ibAsyncRenderers[name] = renderer;
    };

    if (!window.ibAsyncRenderers.text)
        window.ibRegisterAsyncRenderer('text', escapeHtml);

    if (!window.ibAsyncRenderers.html) {
        window.ibRegisterAsyncRenderer('html', function (payload) {
            return payload === null || typeof payload === 'undefined' ? '' : String(payload);
        });
    }

    if (!window.ibAsyncRenderers.alerts) {
        window.ibRegisterAsyncRenderer('alerts', function (items) {
            if (!Array.isArray(items) || !items.length)
                return '';

            window.__ibAsyncModalCounter = (window.__ibAsyncModalCounter || 0) + 1;

            const modalId = 'ib-async-modal-' + window.__ibAsyncModalCounter;
            let html = '<div id="' + modalId + '" uk-modal><div class="uk-modal-dialog uk-modal-body">'
                + '<h2 class="uk-modal-title">Problemi</h2><ol>';

            items.forEach(function (item) {
                item = item || {};
                html += '<li>' + escapeHtml(item.label);

                if (item.href) {
                    const target = item.target ? ' target="' + escapeHtml(item.target) + '"' : '';
                    html += ' <a' + target
                        + ' rel="noopener noreferrer" class="uk-button uk-button-primary uk-button-small" href="'
                        + escapeHtml(item.href) + '">Risolvi</a>';
                }

                html += '</li>';
            });

            html += '</ol></div></div>';

            return html + '<a href="#' + modalId
                + '" uk-toggle class="ib-alertbutton uk-button uk-button-small uk-button-danger">'
                + '<i class="fa-solid fa-exclamation-triangle"></i>' + items.length + '</a>';
        });
    }

    function normalizeResponse(response) {
        if (response && response.data && typeof response.data === 'object' && !Array.isArray(response.data))
            return response.data;

        return response && typeof response === 'object' ? response : {};
    }

    function collectCurrentRows(datatable) {
        const rows = [];
        const seenIds = new Set();

        datatable.rows({ search: 'applied', page: 'current' }).every(function () {
            const rowIndex = this.index();
            const rowId = window.ibDtGetRowIdFromRowIndex(datatable, rowIndex);

            if (rowId === null || typeof rowId === 'undefined' || rowId === '')
                return;

            const id = String(rowId);

            if (seenIds.has(id))
                return;

            seenIds.add(id);
            rows.push({ id: id, index: rowIndex });
        });

        return rows;
    }

    function getCellTarget(datatable, rowIndex, columnIndex) {
        const node = datatable.cell(rowIndex, columnIndex).node();

        return node ? $(node) : $();
    }

    function markRowsLoading(datatable, columnIndex, rows) {
        rows.forEach(function (row) {
            getCellTarget(datatable, row.index, columnIndex)
                .attr('aria-busy', 'true')
                .removeClass('ib-asynccell-error');
        });
    }

    function renderRows(datatable, columnIndex, rendererName, payloadById, requestedIds) {
        const renderer = window.ibAsyncRenderers[rendererName] || window.ibAsyncRenderers.text;
        const requested = new Set(requestedIds.map(String));

        collectCurrentRows(datatable).forEach(function (row) {
            if (!requested.has(row.id))
                return;

            const $target = getCellTarget(datatable, row.index, columnIndex);

            if (!$target.length)
                return;

            const payload = Object.prototype.hasOwnProperty.call(payloadById, row.id)
                ? payloadById[row.id]
                : null;

            try {
                $target.html(renderer(payload, row.id, $target.get(0)))
                    .attr('aria-busy', 'false')
                    .removeClass('ib-asynccell-error');
            } catch (error) {
                $target.empty()
                    .attr('aria-busy', 'false')
                    .addClass('ib-asynccell-error');
            }
        });
    }

    function markColumnError(datatable, columnIndex, requestedIds) {
        const requested = new Set(requestedIds.map(String));

        collectCurrentRows(datatable).forEach(function (row) {
            if (!requested.has(row.id))
                return;

            getCellTarget(datatable, row.index, columnIndex)
                .attr('aria-busy', 'false')
                .addClass('ib-asynccell-error');
        });
    }

    function resolveColumn(datatable, column, currentRows) {
        const endpoint = column.getHeaderData('asyncRoute');

        if (!endpoint || !currentRows.length)
            return;

        const columnIndex = column.index();
        const rendererName = column.getHeaderData('asyncRenderer') || 'text';
        const useCache = String(column.getHeaderData('asyncCache')) === '1';
        const tableNode = datatable.table().node();
        const tableKey = tableNode.id || tableNode.getAttribute('data-realid') || 'datatable';
        const columnKey = tableKey + ':' + columnIndex + ':' + endpoint;
        const state = window.ibAsyncColumnState[columnKey] || { generation: 0, signature: null };
        const cache = window.ibAsyncCellsCache[columnKey] || {};
        const ids = currentRows.map(function (row) { return row.id; });
        const cachedIds = useCache ? ids.filter(function (id) {
            return Object.prototype.hasOwnProperty.call(cache, id);
        }) : [];
        const missingIds = useCache ? ids.filter(function (id) {
            return !Object.prototype.hasOwnProperty.call(cache, id);
        }) : ids;

        if (cachedIds.length)
            renderRows(datatable, columnIndex, rendererName, cache, cachedIds);

        if (!missingIds.length)
            return;

        const signature = JSON.stringify(missingIds);

        if (state.signature === signature)
            return;

        state.generation++;
        state.signature = signature;
        window.ibAsyncColumnState[columnKey] = state;

        const generation = state.generation;
        const missingSet = new Set(missingIds);

        markRowsLoading(datatable, columnIndex, currentRows.filter(function (row) {
            return missingSet.has(row.id);
        }));

        window.__ibCallAjax(endpoint, 'POST', { ids: missingIds }, {
            target: tableNode,
            responseDataType: 'json',
            onSuccess: function (response) {
                if (state.generation !== generation)
                    return;

                let payloadById = normalizeResponse(response);

                if (useCache) {
                    missingIds.forEach(function (id) {
                        cache[id] = Object.prototype.hasOwnProperty.call(payloadById, id)
                            ? payloadById[id]
                            : null;
                    });

                    window.ibAsyncCellsCache[columnKey] = cache;
                    payloadById = cache;
                }

                renderRows(datatable, columnIndex, rendererName, payloadById, useCache ? ids : missingIds);
            },
            onError: function () {
                if (state.generation === generation)
                    markColumnError(datatable, columnIndex, missingIds);
            },
            onComplete: function () {
                if (state.generation === generation)
                    state.signature = null;
            }
        });
    }

    window.ibResolveAsyncColumns = function (datatable) {
        if (!datatable || typeof window.ibDtGetRowIdFromRowIndex !== 'function'
            || typeof window.__ibCallAjax !== 'function')
            return;

        const currentRows = collectCurrentRows(datatable);

        datatable.columns().every(function () {
            resolveColumn(datatable, this, currentRows);
        });
    };

    $(document)
        .off('.ibAsyncColumns')
        .on('draw.dt.ibAsyncColumns', function (event, settings) {
            if (!settings || !settings.nTable)
                return;

            window.ibResolveAsyncColumns(new $.fn.dataTable.Api(settings));
        });

    $(function () {
        $('table.dataTable').each(function () {
            if ($.fn.dataTable.isDataTable(this))
                window.ibResolveAsyncColumns($(this).DataTable());
        });
    });

    window.ibClearAsyncCellsCache = function () {
        window.ibAsyncCellsCache = {};
    };
})(window.jQuery);
