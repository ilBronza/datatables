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
        document,
        flush: window.ibDtFlushPendingRenderedCells,
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

    assert.deepEqual(data.rowData, ['server-a', 'new-b']);
    assert.equal(data.cells[0].innerHTML, '<span>old-a</span>');
    assert.equal(data.cells[1].innerHTML, '<span>new-b</span>');
    assert.deepEqual(data.invalidations, [{ columnIndex: 1, source: 'data' }]);
    assert.equal(result.protectedCells, 1);
});

test('invalidates a protected cell after it loses focus', () => {
    const data = fixture({ activeColumn: 0 });
    const runtime = boot({ activeElement: data.activeElement });

    runtime.patch(data.table, data.row, ['server-a', 'new-b']);
    runtime.document.activeElement = null;
    runtime.flush();

    assert.equal(data.cells[0].innerHTML, '<span>server-a</span>');
    assert.deepEqual(data.invalidations, [
        { columnIndex: 1, source: 'data' },
        { columnIndex: 0, source: 'data' },
    ]);
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
