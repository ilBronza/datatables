(function(window, document)
{
    function getRenderedCell(settings, rowIndex, columnIndex, rowData)
    {
        const column = settings.aoColumns && settings.aoColumns[columnIndex];

        if (! column || typeof column.fnGetData !== 'function')
            return null;

        let display;

        try
        {
            display = column.fnGetData(rowData, 'display', {
                settings: settings,
                row: rowIndex,
                col: columnIndex,
            });
        }
        catch (error)
        {
            return null;
        }
        const template = document.createElement('div');

        if (display && typeof display === 'object' && display.nodeType)
            template.appendChild(display.cloneNode(true));
        else
            template.innerHTML = display === null || typeof display === 'undefined'
                ? ''
                : String(display);

        return template.innerHTML;
    }

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

    function synchronizeRowData(rowState, rowData)
    {
        // row().data(rowData) calls DataTables invalidation, which can write
        // all rendered cells immediately. Updating the data source and caches
        // separately leaves the existing DOM entirely under our control.
        rowState._aData = rowData;
        rowState._aSortData = null;
        rowState._aFilterData = null;
        rowState._sFilterRow = null;

        if (Object.prototype.hasOwnProperty.call(rowState, 'displayData'))
            rowState.displayData = null;
    }

    window.ibDtPatchRenderedRow = function(table, row, rowData)
    {
        if (! table || ! row || ! row.any() || ! row.node())
            return false;

        const settings = table.settings()[0];
        const rowIndex = row.index();
        const rowState = settings.aoData && settings.aoData[rowIndex];
        const rowNode = row.node();

        if (! rowState || ! Array.isArray(rowState.anCells))
            return false;

        const tableDomId = settings.nTable ? settings.nTable.id : null;
        const activeElement = document.activeElement;
        const trackedEditor = getTrackedEditor(tableDomId);
        const inlineEditActionColumn = getInlineEditActionColumn(rowNode);
        const renderedCells = [];

        // Render everything before changing either DOM or DataTables state.
        // Renderers therefore all see one coherent server response.
        rowState.anCells.forEach(function(cell, columnIndex)
        {
            if (! cell)
                return;

            renderedCells[columnIndex] = getRenderedCell(settings, rowIndex, columnIndex, rowData);
        });

        let changedCells = 0;
        let protectedCells = 0;

        rowState.anCells.forEach(function(cell, columnIndex)
        {
            if (! cell)
                return;

            const rendered = renderedCells[columnIndex];

            if (rendered === null || cell.innerHTML === rendered)
                return;

            const preserve = columnIndex === inlineEditActionColumn
                || cellContains(cell, activeElement)
                || cellContains(cell, trackedEditor)
                || cellHasLocalChanges(cell);

            if (preserve)
            {
                protectedCells++;
                return;
            }

            cell.innerHTML = rendered;
            changedCells++;

            if (window.UIkit && typeof window.UIkit.update === 'function')
                window.UIkit.update(cell);

            if (typeof window.ibDtInitCleaveNumericsInRoot === 'function')
                window.ibDtInitCleaveNumericsInRoot(cell);
        });

        synchronizeRowData(rowState, rowData);

        return {
            changedCells: changedCells,
            protectedCells: protectedCells,
        };
    };
})(window, document);
