/**
 * Azioni che richiedono almeno una riga selezionata (per esempio la
 * cancellazione multipla) restano fuori dalla toolbar finché non sono
 * utilizzabili. I bottoni personalizzati possono usare la classe
 * `ib-dt-selection-action`; il cestino viene generato dal pacchetto quando
 * la tabella espone un campo Delete e permette la selezione delle righe.
 */
(function registerIlBronzaDatatablesSelectionActions()
{
    if (! window.jQuery || ! $.fn.dataTable || ! $.fn.dataTable.Api)
    {
        setTimeout(registerIlBronzaDatatablesSelectionActions, 100);
        return;
    }

    function asDataTableApi(table)
    {
        if (table && typeof table.rows === 'function')
            return table;

        return new $.fn.dataTable.Api(table);
    }

    function selectedDeleteUrls(rows, columnIndex)
    {
        const data = rows.data().toArray();
        const urls = data.map(function (row)
        {
            const cell = row && row[columnIndex];
            const url = Array.isArray(cell) ? cell[0] : cell;

            return typeof url === 'string' ? url.trim() : '';
        });

        return urls.length && urls.every(Boolean) ? urls : null;
    }

    window.ibDtSyncSelectionActionButtons = function (table)
    {
        const dt = asDataTableApi(table);

        if (! dt || typeof dt.rows !== 'function' || typeof dt.table !== 'function')
            return;

        const container = dt.table().container();
        const $actions = $(container).find('.ib-dt-selection-action');

        if (! $actions.length)
            return;

        const selected = dt.rows({ selected: true });
        const hasSelection = selected.count() > 0;

        $actions.each(function ()
        {
            const $action = $(this);
            const deleteColumnIndex = $action.attr('data-delete-column-index');
            const canDelete = deleteColumnIndex === undefined
                || selectedDeleteUrls(selected, Number(deleteColumnIndex)) !== null;
            const visible = hasSelection && canDelete;

            $action
                .toggleClass('uk-hidden', ! visible)
                .prop('disabled', ! visible || !! $action.data('ibDeletingSelected'))
                .attr('aria-hidden', visible ? 'false' : 'true');
        });
    };

    window.ibDtDeleteSelectedRows = function (table, node, columnIndex, labels)
    {
        const dt = asDataTableApi(table);
        const $node = $(node);

        if ($node.data('ibDeletingSelected'))
            return;

        const selected = dt.rows({ selected: true });
        const urls = selectedDeleteUrls(selected, columnIndex);

        if (! urls)
            return;

        if (! window.confirm(labels.confirm.replace(':count', String(urls.length))))
            return;

        const indexes = selected.indexes().toArray();
        const token = $('meta[name="csrf-token"]').attr('content') || window.csrfToken;
        const headers = typeof window.ibGetDatatableBroadcastHeaders === 'function'
            ? window.ibGetDatatableBroadcastHeaders(dt.table().node())
            : {};

        if (token)
            headers['X-CSRF-TOKEN'] = token;
        let position = 0;
        let failures = 0;
        const succeeded = [];

        $node.data('ibDeletingSelected', true).prop('disabled', true);

        function finish()
        {
            $node.data('ibDeletingSelected', false);
            dt.rows().deselect();

            if (dt.settings()[0].ajax)
                dt.ajax.reload(null, false);
            else if (succeeded.length)
                dt.rows(succeeded).remove().draw(false);

            if (failures && typeof window.addDangerNotification === 'function')
                window.addDangerNotification(labels.error.replace(':count', String(failures)));
            else if (succeeded.length && typeof window.addSuccessNotification === 'function')
                window.addSuccessNotification(labels.success.replace(':count', String(succeeded.length)));

            window.ibDtSyncSelectionActionButtons(dt);
        }

        function removeNext()
        {
            if (position >= urls.length)
                return finish();

            const current = position++;

            try
            {
                $.ajax({
                    url: urls[current],
                    type: 'DELETE',
                    dataType: 'json',
                    headers: headers,
                    success: function (response)
                    {
                        if (response && response.success === false)
                            failures++;
                        else
                            succeeded.push(indexes[current]);
                    },
                    error: function ()
                    {
                        failures++;
                    },
                    complete: removeNext
                });
            }
            catch (error)
            {
                failures++;
                removeNext();
            }
        }

        removeNext();
    };

    $(document).on('init.dt.ibSelectionActions draw.dt.ibSelectionActions', function (e, settings)
    {
        window.ibDtSyncSelectionActionButtons(settings);
    });

    $(document).on('select.dt.ibSelectionActions deselect.dt.ibSelectionActions', function (e, dt)
    {
        window.ibDtSyncSelectionActionButtons(dt);
    });

})();
