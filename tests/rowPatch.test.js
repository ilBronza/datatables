const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.rowPatch.js',
    'utf8'
);

function makeElement(innerHTML = '') {
    return {
        classList: { contains: () => false },
        contains(element) { return element && element.owner === this; },
        innerHTML,
        nextElementSibling: null,
        querySelector() { return null; },
    };
}

function boot({ activeElement = null } = {}) {
    const handlers = {};
    const document = {
        activeElement,
        addEventListener(eventName, handler) { handlers[eventName] = handler; },
        documentElement: {
            contains(element) { return !!element && element.attached !== false; },
        },
    };
    const window = {
        setTimeout(callback) { callback(); },
    };

    vm.runInNewContext(moduleSource, { Array, document, Number, Object, window });

    return {
        advanceRevision: window.ibDtAdvanceEditorRevision,
        beginSave: window.ibDtBeginEditorCellSave,
        captureRevisions: window.ibDtCaptureRowRevisionSnapshot,
        completeSave: window.ibDtCompleteEditorCellSave,
        document,
        flush: window.ibDtFlushPendingRenderedCells,
        hasPendingRefresh: window.ibDtHasPendingCellRefresh,
        handlers,
        patch: window.ibDtPatchRenderedRow,
        window,
    };
}

function fixture({ activeColumn = null, dirtyColumn = null } = {}) {
    const cells = [makeElement('<span>old-a</span>'), makeElement('<span>old-b</span>')];
    const activeElement = activeColumn === null ? null : { owner: cells[activeColumn] };
    const invalidations = [];
    const rowNode = makeElement();
    const rowData = ['old-a', 'old-b'];

    if (dirtyColumn !== null)
        cells[dirtyColumn].querySelector = () => ({});

    const row = {
        any: () => true,
        data: () => rowData,
        index: () => 0,
        node: () => rowNode,
    };
    const table = {
        cell(_rowIndex, columnIndex) {
            return {
                invalidate(source) {
                    invalidations.push({ columnIndex, source });
                    cells[columnIndex].innerHTML = '<span>' + rowData[columnIndex] + '</span>';
                    return this;
                },
                node: () => cells[columnIndex],
            };
        },
        columns() {
            return { indexes: () => ({ toArray: () => [0, 1] }) };
        },
        column(columnIndex) {
            return { dataSrc: () => columnIndex };
        },
        row: () => row,
        table() {
            return { node: () => ({ id: 'orders' }) };
        },
    };

    return { activeElement, cells, invalidations, row, rowData, rowNode, table };
}

test('invalidates row cells through the public cell API without drawing', () => {
    const data = fixture();
    const runtime = boot();

    const result = runtime.patch(data.table, data.row, ['new-a', 'new-b']);

    assert.deepEqual(data.rowData, ['new-a', 'new-b']);
    assert.equal(data.cells[0].innerHTML, '<span>new-a</span>');
    assert.equal(data.cells[1].innerHTML, '<span>new-b</span>');
    assert.deepEqual(data.invalidations, [
        { columnIndex: 0, source: 'data' },
        { columnIndex: 1, source: 'data' },
    ]);
    assert.equal(result.invalidatedCells, 2);
});

test('preserves the focused cell while invalidating its siblings', () => {
    const data = fixture({ activeColumn: 0 });
    const runtime = boot({ activeElement: data.activeElement });

    const result = runtime.patch(data.table, data.row, ['server-a', 'new-b']);

    assert.deepEqual(data.rowData, ['old-a', 'new-b']);
    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');
    assert.equal(data.cells[1].innerHTML, '<span>new-b</span>');
    assert.deepEqual(data.invalidations, [{ columnIndex: 1, source: 'data' }]);
    assert.equal(result.protectedCells, 1);
});

test('refreshes an explicitly allowed focused file cell immediately', () => {
    const data = fixture({ activeColumn: 0 });
    const runtime = boot({ activeElement: data.activeElement });
    const fileInput = {
        closest: () => data.cells[0],
    };

    const result = runtime.patch(
        data.table,
        data.row,
        ['uploaded-file', 'new-b'],
        null,
        { refreshActiveTargetCell: true, target: fileInput }
    );

    assert.deepEqual(data.rowData, ['uploaded-file', 'new-b']);
    assert.equal(data.cells[0].innerHTML, '<span>uploaded-file</span>');
    assert.deepEqual(data.invalidations, [
        { columnIndex: 0, source: 'data' },
        { columnIndex: 1, source: 'data' },
    ]);
    assert.equal(result.protectedCells, 0);
});

test('invalidates a protected cell after it loses focus', () => {
    const data = fixture({ activeColumn: 0 });
    const runtime = boot({ activeElement: data.activeElement });

    runtime.patch(data.table, data.row, ['server-a', 'new-b']);
    runtime.document.activeElement = null;
    runtime.flush();

    assert.deepEqual(data.rowData, ['server-a', 'new-b']);
    assert.equal(data.cells[0].innerHTML, '<span>server-a</span>');
    assert.deepEqual(data.invalidations, [
        { columnIndex: 1, source: 'data' },
        { columnIndex: 0, source: 'data' },
    ]);
});

test('keeps an untouched focused cell pending until Tab moves focus away', () => {
    const data = fixture();
    const editor = {
        closest: () => data.cells[0],
        owner: data.cells[0],
    };
    const runtime = boot({ activeElement: editor });

    runtime.patch(data.table, data.row, ['calculated-a', 'new-b']);

    assert.equal(runtime.hasPendingRefresh(editor), true);
    assert.deepEqual(data.rowData, ['old-a', 'new-b']);
    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');

    runtime.document.activeElement = null;
    runtime.flush();

    assert.equal(runtime.hasPendingRefresh(editor), false);
    assert.deepEqual(data.rowData, ['calculated-a', 'new-b']);
    assert.equal(data.cells[0].innerHTML, '<span>calculated-a</span>');
});

test('does not treat a pending server value as authoritative after local input', () => {
    const data = fixture();
    const editor = {
        __ibDtEditor: true,
        closest: () => data.cells[0],
        matches: () => true,
        owner: data.cells[0],
    };
    const runtime = boot({ activeElement: editor });

    runtime.patch(data.table, data.row, ['calculated-a', 'new-b']);
    assert.equal(runtime.hasPendingRefresh(editor), true);

    runtime.handlers.input({ target: editor });

    assert.equal(runtime.hasPendingRefresh(editor), false);
});

test('does not suppress saving when the cell was already dirty before the refresh', () => {
    const data = fixture({ dirtyColumn: 0 });
    const editor = {
        closest: () => data.cells[0],
        owner: data.cells[0],
    };
    const runtime = boot({ activeElement: editor });
    const revisions = runtime.captureRevisions(data.table, data.row);

    runtime.patch(
        data.table,
        data.row,
        ['calculated-a', 'new-b'],
        revisions
    );

    assert.equal(runtime.hasPendingRefresh(editor), false);
    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');
});

test('preserves dirty cells until their local change is cleared', () => {
    const data = fixture({ dirtyColumn: 0 });
    const runtime = boot();

    runtime.patch(data.table, data.row, ['server-a', 'new-b']);
    runtime.flush();

    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');

    data.cells[0].querySelector = () => null;
    runtime.flush();

    assert.equal(data.cells[0].innerHTML, '<span>server-a</span>');
});

test('preserves an editor tracked by the table during focus transitions', () => {
    const data = fixture();
    const trackedEditor = { owner: data.cells[0], attached: true };
    const runtime = boot();
    runtime.window.__ibDtFocusedEditors = { orders: trackedEditor };

    const result = runtime.patch(data.table, data.row, ['server-a', 'new-b']);

    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');
    assert.equal(data.cells[1].innerHTML, '<span>new-b</span>');
    assert.equal(result.protectedCells, 1);
});

test('rejects a stale cell response when the user types after the request starts', () => {
    const data = fixture();
    const runtime = boot();
    const editor = {
        __ibDtEditor: true,
        closest: () => data.cells[0],
        matches: () => true,
    };
    const revisions = runtime.captureRevisions(data.table, data.row);

    runtime.handlers.input({ target: editor });
    data.cells[0].innerHTML = '<input value="10">';

    const result = runtime.patch(
        data.table,
        data.row,
        ['stale-zero', 'fresh-b'],
        revisions
    );

    assert.deepEqual(data.rowData, ['old-a', 'fresh-b']);
    assert.equal(data.cells[0].innerHTML, '<input value="10">');
    assert.deepEqual(data.invalidations, [{ columnIndex: 1, source: 'data' }]);
    assert.equal(result.staleCells, 1);
});

test('discards a pending response when saving starts before focus is released', () => {
    const data = fixture({ activeColumn: 0 });
    const runtime = boot({ activeElement: data.activeElement });
    const editor = {
        __ibDtEditor: true,
        closest: () => data.cells[0],
        matches: () => true,
    };
    const revisions = runtime.captureRevisions(data.table, data.row);

    data.cells[0].innerHTML = '<input value="10">';
    runtime.patch(data.table, data.row, ['stale-zero', 'fresh-b'], revisions);
    runtime.beginSave(editor);
    runtime.document.activeElement = null;
    runtime.flush();

    assert.deepEqual(data.rowData, ['old-a', 'fresh-b']);
    assert.equal(data.cells[0].innerHTML, '<input value="10">');

    runtime.completeSave(editor);
    const freshRevisions = runtime.captureRevisions(data.table, data.row);
    runtime.patch(data.table, data.row, ['10', 'freshest-b'], freshRevisions);

    assert.deepEqual(data.rowData, ['10', 'freshest-b']);
    assert.equal(data.cells[0].innerHTML, '<span>10</span>');
});
