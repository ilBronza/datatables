$(document).ready(function()
{
    const state = {
        locks: {},
        unlockTimers: {},
        rowRefreshes: {},
        tableRowReloads: {},
        tableReloads: {},
    };

    function getContext(target)
    {
        const $target = $(target);
        const $table = $target.closest('table.datatable');
        let $row = $target.closest('tr');

        if ($row.hasClass('child') || $row.hasClass('ib-datatable-inline-edit-child'))
            $row = $row.prev();

        const tableId = $table.attr('id');
        const rowId = String($row.attr('id') || '').trim();

        if (! tableId || ! rowId)
            return null;

        return { tableId: tableId, rowId: rowId, key: tableId + ':' + rowId };
    }

    function tableHasLock(tableId)
    {
        return Object.keys(state.locks).some(function(key)
        {
            return key.indexOf(tableId + ':') === 0 && state.locks[key];
        });
    }

    function lock(target)
    {
        const context = getContext(target);

        if (! context)
            return;

        if (state.unlockTimers[context.key])
        {
            clearTimeout(state.unlockTimers[context.key]);
            delete state.unlockTimers[context.key];
        }

        state.locks[context.key] = true;
    }

    function flush(tableId)
    {
        if (tableHasLock(tableId))
            return;

        Object.keys(state.rowRefreshes).forEach(function(key)
        {
            if (key.indexOf(tableId + ':') !== 0)
                return;

            const params = state.rowRefreshes[key];
            delete state.rowRefreshes[key];
            window.__refreshRow(params);
        });

        const rowReload = state.tableRowReloads[tableId];
        if (rowReload)
        {
            delete state.tableRowReloads[tableId];
            window.reloadTableRows(
                rowReload.tableId,
                rowReload.rowIds,
                rowReload.preserveSelectionIds,
                rowReload.onRowsReloaded
            );
        }

        const tableReload = state.tableReloads[tableId];
        if (tableReload)
        {
            delete state.tableReloads[tableId];
            window.reloadDatatable(tableReload);
        }

    }

    function unlock(target)
    {
        const context = getContext(target);

        if (! context)
            return;

        // A mouse click fires pointerdown on the next editor before blur on
        // this one. Waiting one turn lets the new focus keep the same row
        // locked and prevents a refresh between the two controls.
        state.unlockTimers[context.key] = setTimeout(function()
        {
            delete state.unlockTimers[context.key];

            const activeContext = getContext(document.activeElement);
            if (activeContext && activeContext.key === context.key)
                return;

            delete state.locks[context.key];
            flush(context.tableId);
        }, 0);
    }

    function getTableIdFromSelector(tableSelector)
    {
        try
        {
            if (tableSelector === '#')
                return $('.datatable').first().attr('id');

            return $(tableSelector).first().attr('id');
        }
        catch (error)
        {
            return null;
        }
    }

    function installRefreshGuards()
    {
        const refreshRow = window.__refreshRow;
        const reloadTableRows = window.reloadTableRows;
        const reloadDatatable = window.reloadDatatable;

        if (typeof refreshRow === 'function')
        {
            if (! refreshRow.__ibEditorRowLockWrapped)
            {
                const guardedRefreshRow = function(params)
                {
                    const context = params && params.target ? getContext(params.target) : null;

                    if (context && tableHasLock(context.tableId))
                    {
                        state.rowRefreshes[context.key] = params;
                        return true;
                    }

                    return refreshRow.apply(this, arguments);
                };

                guardedRefreshRow.__ibEditorRowLockWrapped = true;
                window.__refreshRow = guardedRefreshRow;
            }
        }

        if (typeof reloadTableRows === 'function')
        {
            if (! reloadTableRows.__ibEditorRowLockWrapped)
            {
                const guardedReloadTableRows = function(tableId, rowIds, preserveSelectionIds, onRowsReloaded)
                {
                    const domId = getTableIdFromSelector(tableId);

                    if (domId && tableHasLock(domId))
                    {
                        state.tableRowReloads[domId] = {
                            tableId: tableId,
                            rowIds: rowIds,
                            preserveSelectionIds: preserveSelectionIds,
                            onRowsReloaded: onRowsReloaded,
                        };

                        return true;
                    }

                    return reloadTableRows.apply(this, arguments);
                };

                guardedReloadTableRows.__ibEditorRowLockWrapped = true;
                window.reloadTableRows = guardedReloadTableRows;
            }
        }

        if (typeof reloadDatatable === 'function')
        {
            if (! reloadDatatable.__ibEditorRowLockWrapped)
            {
                const guardedReloadDatatable = function(table)
                {
                    const tableId = $(table.table().node()).attr('id');

                    if (tableId && tableHasLock(tableId))
                    {
                        state.tableReloads[tableId] = table;
                        return true;
                    }

                    return reloadDatatable.apply(this, arguments);
                };

                guardedReloadDatatable.__ibEditorRowLockWrapped = true;
                window.reloadDatatable = guardedReloadDatatable;
            }
        }
    }

    // Only inline editors need refresh protection. Locking generic links and
    // buttons could leave a table locked after an ordinary row interaction.
    const focusableSelector = [
        'table.datatable tbody .ib-editor-text',
        'table.datatable tbody .ib-editor-select',
        'table.datatable tbody .ib-editor-color',
        'table.datatable tbody .ib-editor-custom-value',
        'table.datatable tbody .ib-editor-file-upload',
    ].join(', ');

    document.addEventListener('pointerdown', function(event)
    {
        const target = $(event.target).closest(focusableSelector).get(0);

        if (target)
            lock(target);
    }, true);

    document.addEventListener('focusin', function(event)
    {
        const target = $(event.target).closest(focusableSelector).get(0);

        if (target)
            lock(target);
    }, true);

    document.addEventListener('focusout', function(event)
    {
        const target = $(event.target).closest(focusableSelector).get(0);

        if (target)
            unlock(target);
    }, true);

    installRefreshGuards();
    setTimeout(installRefreshGuards, 0);

    window.ibDatatableEditorRowLock = {
        isLocked: tableHasLock,
        state: state,
    };
});
