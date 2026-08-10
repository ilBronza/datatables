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
    const document = {
        activeElement,
        createElement() { return makeElement(); },
        documentElement: {
            contains(element) { return !!element && element.attached !== false; },
        },
    };
    const window = {};

    vm.runInNewContext(moduleSource, { document, Number, Object, String, window });

    return { document, patch: window.ibDtPatchRenderedRow, window };
}

function fixture({ activeColumn = null, dirtyColumn = null } = {}) {
    const cells = [makeElement('<span>old-a</span>'), makeElement('<span>old-b</span>')];
    const activeElement = activeColumn === null ? null : { owner: cells[activeColumn] };

    if (dirtyColumn !== null)
        cells[dirtyColumn].querySelector = () => ({});

    const rowNode = makeElement();
    const rowState = {
        _aData: ['old-a', 'old-b'],
        _aFilterData: ['old-a', 'old-b'],
        _aSortData: ['old-a', 'old-b'],
        _sFilterRow: 'old-a old-b',
        anCells: cells,
        displayData: ['<span>old-a</span>', '<span>old-b</span>'],
    };
    const settings = {
        aoColumns: [0, 1].map((columnIndex) => ({
            fnGetData(data) { return '<span>' + data[columnIndex] + '</span>'; },
        })),
        aoData: [rowState],
        nTable: { id: 'orders' },
    };
    const row = {
        any: () => true,
        index: () => 0,
        node: () => rowNode,
    };
    const table = { settings: () => [settings] };

    return { activeElement, cells, row, rowState, table };
}

test('updates changed cells without invoking a DataTables redraw', () => {
    const data = fixture();
    const runtime = boot();

    const result = runtime.patch(data.table, data.row, ['new-a', 'new-b']);

    assert.equal(data.cells[0].innerHTML, '<span>new-a</span>');
    assert.equal(data.cells[1].innerHTML, '<span>new-b</span>');
    assert.equal(result.changedCells, 2);
    assert.deepEqual(data.rowState._aData, ['new-a', 'new-b']);
    assert.equal(data.rowState._aFilterData, null);
    assert.equal(data.rowState._aSortData, null);
    assert.equal(data.rowState.displayData, null);
});

test('preserves the focused cell while updating its siblings', () => {
    const data = fixture({ activeColumn: 0 });
    const runtime = boot({ activeElement: data.activeElement });

    const result = runtime.patch(data.table, data.row, ['server-a', 'new-b']);

    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');
    assert.equal(data.cells[1].innerHTML, '<span>new-b</span>');
    assert.equal(result.changedCells, 1);
    assert.equal(result.protectedCells, 1);
});

test('preserves dirty cells even when they are not focused', () => {
    const data = fixture({ dirtyColumn: 0 });
    const runtime = boot();

    const result = runtime.patch(data.table, data.row, ['server-a', 'new-b']);

    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');
    assert.equal(data.cells[1].innerHTML, '<span>new-b</span>');
    assert.equal(result.protectedCells, 1);
});

test('preserves an editor tracked by the table when browser focus is temporarily elsewhere', () => {
    const data = fixture();
    const trackedEditor = { owner: data.cells[0], attached: true };
    const runtime = boot();
    runtime.window.__ibDtFocusedEditors = { orders: trackedEditor };

    const result = runtime.patch(data.table, data.row, ['server-a', 'new-b']);

    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');
    assert.equal(data.cells[1].innerHTML, '<span>new-b</span>');
    assert.equal(result.protectedCells, 1);
});
