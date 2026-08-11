(function(window, document)
{
    const pendingRows = {};
    const revisionProperty = '__ibDtEditorRevision';
    const savingProperty = '__ibDtEditorSaving';

    function getClosestCell(target)
    {
        if (! target)
            return null;

        if (typeof target.closest === 'function')
            return target.closest('td, th');

        return target.__ibDtCell || null;
    }

    function isEditorTarget(target)
    {
        if (! target || typeof target.matches !== 'function')
            return !! (target && target.__ibDtEditor);

        return target.matches(
            '.ib-editor-text, .ib-editor-select, .ib-editor-color, ' +
            '.ib-editor-custom-value, .ib-editor-file-upload'
        );
    }

    function getCellRevision(cell)
    {
        return cell && Number.isInteger(cell[revisionProperty])
            ? cell[revisionProperty]
            : 0;
    }

    function getCellSavingCount(cell)
    {
        return cell && Number.isInteger(cell[savingProperty])
            ? cell[savingProperty]
            : 0;
    }

    function advanceCellRevision(cell)
    {
        if (! cell)
            return false;

        cell[revisionProperty] = getCellRevision(cell) + 1;

        return true;
    }

    window.ibDtAdvanceEditorRevision = function(target)
    {
        if (! isEditorTarget(target))
            return false;

        return advanceCellRevision(getClosestCell(target));
    };

    window.ibDtBeginEditorCellSave = function(target)
    {
        if (! isEditorTarget(target))
            return false;

        const cell = getClosestCell(target);

        if (! advanceCellRevision(cell))
            return false;

        cell[savingProperty] = getCellSavingCount(cell) + 1;

        return true;
    };

    window.ibDtCompleteEditorCellSave = function(target)
    {
        if (! isEditorTarget(target))
            return false;

        const cell = getClosestCell(target);

        if (! cell)
            return false;

        cell[savingProperty] = Math.max(0, getCellSavingCount(cell) - 1);
        advanceCellRevision(cell);
        window.setTimeout(window.ibDtFlushPendingRenderedCells, 0);

        return true;
    };

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
            || getCellSavingCount(cell) > 0
            || cellHasLocalChanges(cell);
    }

    function mustRefreshActiveTargetCell(cell, options)
    {
        return !! (options
            && options.refreshActiveTargetCell
            && getClosestCell(options.target) === cell);
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

    function getDataSourceValue(data, dataSource)
    {
        if (! data || (typeof dataSource !== 'number' && typeof dataSource !== 'string'))
            return { supported: false };

        const keys = typeof dataSource === 'number'
            ? [dataSource]
            : dataSource.split('.');
        let value = data;

        for (let index = 0; index < keys.length; index++)
        {
            if (value === null || typeof value === 'undefined')
                return { supported: true, value: undefined };

            value = value[keys[index]];
        }

        return { supported: true, value: value };
    }

    function setDataSourceValue(data, dataSource, value)
    {
        if (! data || (typeof dataSource !== 'number' && typeof dataSource !== 'string'))
            return false;

        const keys = typeof dataSource === 'number'
            ? [dataSource]
            : dataSource.split('.');
        let target = data;

        for (let index = 0; index < keys.length - 1; index++)
        {
            const key = keys[index];

            if (! target[key] || typeof target[key] !== 'object')
                target[key] = {};

            target = target[key];
        }

        target[keys[keys.length - 1]] = value;

        return true;
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

    window.ibDtHasPendingCellRefresh = function(target)
    {
        const targetCell = getClosestCell(target);

        if (! targetCell)
            return false;

        return Object.keys(pendingRows).some(function(key)
        {
            const pending = pendingRows[key];

            return pending.columns.some(function(pendingColumn)
            {
                const cell = pending.table
                    .cell(pending.rowIndex, pendingColumn.columnIndex)
                    .node();

                return cell === targetCell
                    && getCellRevision(cell) === pendingColumn.revision
                    && ! cellHasLocalChanges(cell);
            });
        });
    };

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

            pending.columns.forEach(function(pendingColumn)
            {
                const columnIndex = pendingColumn.columnIndex;
                const cell = pending.table.cell(pending.rowIndex, columnIndex).node();

                // The user edited or saved this cell after the response which
                // created the pending value. That server value is obsolete.
                if (getCellRevision(cell) !== pendingColumn.revision)
                    return;

                if (columnIndex === inlineEditActionColumn || cellMustBePreserved(cell, trackedEditor))
                {
                    remaining.push(pendingColumn);
                    return;
                }

                if (setDataSourceValue(row.data(), pendingColumn.dataSource, pendingColumn.value))
                    invalidateCell(pending.table, pending.rowIndex, columnIndex);
            });

            setPendingColumns(pending.table, pending.rowIndex, remaining);
        });
    };

    window.ibDtCaptureRowRevisionSnapshot = function(table, row)
    {
        if (! table || ! row || ! row.any() || ! row.node())
            return {};

        const rowIndex = row.index();
        const revisions = {};

        table.columns().indexes().toArray().forEach(function(columnIndex)
        {
            const cell = table.cell(rowIndex, columnIndex).node();
            revisions[columnIndex] = getCellRevision(cell);
        });

        return revisions;
    };

    window.ibDtPatchRenderedRow = function(table, row, rowData, revisionSnapshot, options)
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
        const columnsToInvalidate = [];

        let invalidatedCells = 0;
        let staleCells = 0;

        columnIndexes.forEach(function(columnIndex)
        {
            const cellApi = table.cell(rowIndex, columnIndex);
            const cell = cellApi.node();
            const expectedRevision = revisionSnapshot
                && Object.prototype.hasOwnProperty.call(revisionSnapshot, columnIndex)
                    ? revisionSnapshot[columnIndex]
                    : getCellRevision(cell);

            if (getCellRevision(cell) !== expectedRevision)
            {
                staleCells++;
                return;
            }

            const dataSource = table.column(columnIndex).dataSrc();
            const nextValue = getDataSourceValue(rowData, dataSource);

            if (! nextValue.supported)
                return;

            const preserve = columnIndex === inlineEditActionColumn
                || (! mustRefreshActiveTargetCell(cell, options)
                    && cellMustBePreserved(cell, trackedEditor));

            if (preserve)
            {
                protectedColumns.push({
                    columnIndex: columnIndex,
                    dataSource: dataSource,
                    revision: expectedRevision,
                    value: nextValue.value,
                });
                return;
            }

            if (setDataSourceValue(currentData, dataSource, nextValue.value))
                columnsToInvalidate.push(columnIndex);
        });

        columnsToInvalidate.forEach(function(columnIndex)
        {
            if (invalidateCell(table, rowIndex, columnIndex))
                invalidatedCells++;
        });

        setPendingColumns(table, rowIndex, protectedColumns);

        return {
            invalidatedCells: invalidatedCells,
            protectedCells: protectedColumns.length,
            staleCells: staleCells,
        };
    };

    if (document && typeof document.addEventListener === 'function')
    {
        document.addEventListener('focusout', function()
        {
            window.setTimeout(window.ibDtFlushPendingRenderedCells, 25);
        }, true);

        document.addEventListener('input', function(event)
        {
            window.ibDtAdvanceEditorRevision(event.target);
        }, true);

        document.addEventListener('change', function(event)
        {
            window.ibDtAdvanceEditorRevision(event.target);
        }, true);
    }
})(window, document);
