(function(window, document)
{
    const pendingRows = {};

    function cellContains(cell, element)
    {
        return !! element && (cell === element || (typeof cell.contains === 'function' && cell.contains(element)));
    }

    function getTrackedEditor(tableDomId)
    {
        const editors = window.__ibDtFocusedEditors || {};
        const editor = editors[tableDomId];

        if (! editor)
            return null;

        if (document.documentElement && typeof document.documentElement.contains === 'function')
            return document.documentElement.contains(editor) ? editor : null;

        return editor;
    }

    function cellHasLocalChanges(cell)
    {
        return typeof cell.querySelector === 'function'
            && !! cell.querySelector('.editorchanged, .ib-editor-dirty');
    }

    function cellMustBePreserved(cell, trackedEditor)
    {
        if (! cell)
            return true;

        return cellContains(cell, document.activeElement)
            || cellContains(cell, trackedEditor)
            || cellHasLocalChanges(cell);
    }

    function getInlineEditActionColumn(rowNode)
    {
        const child = rowNode && rowNode.nextElementSibling;

        if (! child || ! child.classList || ! child.classList.contains('ib-datatable-inline-edit-child'))
            return null;

        const button = child.querySelector('.ib-datatable-inline-edit-save');
        const jQuery = window.jQuery || window.$;

        if (! button || ! jQuery)
            return null;

        const columnIndex = Number(jQuery(button).data('ibInlineEditOriginalCellIndex'));

        return Number.isInteger(columnIndex) && columnIndex >= 0 ? columnIndex : null;
    }

    function replaceDataSource(currentData, nextData)
    {
        if (Array.isArray(currentData) && Array.isArray(nextData))
        {
            currentData.length = nextData.length;

            nextData.forEach(function(value, index)
            {
                currentData[index] = value;
            });

            return true;
        }

        if (currentData && nextData && typeof currentData === 'object' && typeof nextData === 'object')
        {
            Object.keys(currentData).forEach(function(key)
            {
                if (! Object.prototype.hasOwnProperty.call(nextData, key))
                    delete currentData[key];
            });

            Object.keys(nextData).forEach(function(key)
            {
                currentData[key] = nextData[key];
            });

            return true;
        }

        return false;
    }

    function initializeCell(cell)
    {
        if (! cell)
            return;

        if (window.UIkit && typeof window.UIkit.update === 'function')
            window.UIkit.update(cell);

        if (typeof window.ibDtInitCleaveNumericsInRoot === 'function')
            window.ibDtInitCleaveNumericsInRoot(cell);
    }

    function invalidateCell(table, rowIndex, columnIndex)
    {
        const cell = table.cell(rowIndex, columnIndex);

        if (! cell || typeof cell.invalidate !== 'function')
            return false;

        cell.invalidate('data');
        initializeCell(cell.node());

        return true;
    }

    function getTableDomId(table)
    {
        const tableNode = table && table.table ? table.table().node() : null;

        return tableNode ? tableNode.id : null;
    }

    function getPendingKey(table, rowIndex)
    {
        return getTableDomId(table) + ':' + rowIndex;
    }

    function setPendingColumns(table, rowIndex, columns)
    {
        const key = getPendingKey(table, rowIndex);

        if (! columns.length)
        {
            delete pendingRows[key];
            return;
        }

        pendingRows[key] = {
            columns: columns,
            rowIndex: rowIndex,
            table: table,
        };
    }

    window.ibDtFlushPendingRenderedCells = function()
    {
        Object.keys(pendingRows).forEach(function(key)
        {
            const pending = pendingRows[key];
            const row = pending.table.row(pending.rowIndex);

            if (! row || ! row.any() || ! row.node())
            {
                delete pendingRows[key];
                return;
            }

            const tableDomId = getTableDomId(pending.table);
            const trackedEditor = getTrackedEditor(tableDomId);
            const inlineEditActionColumn = getInlineEditActionColumn(row.node());
            const remaining = [];

            pending.columns.forEach(function(columnIndex)
            {
                const cell = pending.table.cell(pending.rowIndex, columnIndex).node();

                if (columnIndex === inlineEditActionColumn || cellMustBePreserved(cell, trackedEditor))
                {
                    remaining.push(columnIndex);
                    return;
                }

                invalidateCell(pending.table, pending.rowIndex, columnIndex);
            });

            setPendingColumns(pending.table, pending.rowIndex, remaining);
        });
    };

    window.ibDtPatchRenderedRow = function(table, row, rowData)
    {
        if (! table || ! row || ! row.any() || ! row.node())
            return false;

        const rowIndex = row.index();
        const currentData = row.data();
        const tableDomId = getTableDomId(table);
        const trackedEditor = getTrackedEditor(tableDomId);
        const inlineEditActionColumn = getInlineEditActionColumn(row.node());
        const columnIndexes = table.columns().indexes().toArray();
        const protectedColumns = [];

        if (! replaceDataSource(currentData, rowData))
            return false;

        let invalidatedCells = 0;

        columnIndexes.forEach(function(columnIndex)
        {
            const cell = table.cell(rowIndex, columnIndex).node();
            const preserve = columnIndex === inlineEditActionColumn
                || cellMustBePreserved(cell, trackedEditor);

            if (preserve)
            {
                protectedColumns.push(columnIndex);
                return;
            }

            if (invalidateCell(table, rowIndex, columnIndex))
                invalidatedCells++;
        });

        setPendingColumns(table, rowIndex, protectedColumns);

        return {
            invalidatedCells: invalidatedCells,
            protectedCells: protectedColumns.length,
        };
    };

    if (document && typeof document.addEventListener === 'function')
    {
        document.addEventListener('focusout', function()
        {
            window.setTimeout(window.ibDtFlushPendingRenderedCells, 25);
        }, true);
    }
})(window, document);
