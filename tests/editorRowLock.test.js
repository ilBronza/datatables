const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.editorRowLock.js',
    'utf8'
);

function boot()
{
    const handlers = {};
    const reloadCalls = [];
    const tableNode = { id: 'orders', kind: 'table' };
    const rowNode = { id: 'order-1', kind: 'row', tableNode: tableNode };
    const editor = { kind: 'editor', rowNode: rowNode, tableNode: tableNode };
    const table = {
        table() {
            return { node: () => tableNode };
        },
    };
    const document = {
        activeElement: editor,
        addEventListener(eventName, handler) {
            handlers[eventName] = handler;
        },
        getElementById() {
            return null;
        },
    };

    function wrap(node)
    {
        return {
            addClass() { return this; },
            attr(name) { return name === 'id' ? node && node.id : undefined; },
            children() { return wrap(null); },
            closest(selector) {
                if (! node)
                    return wrap(null);

                if (selector === 'table.datatable' || selector === 'table')
                    return wrap(node.kind === 'table' ? node : node.tableNode);

                if (selector === 'tr')
                    return wrap(node.kind === 'row' ? node : node.rowNode);

                return wrap(node);
            },
            first() { return this; },
            get(index) { return index === 0 ? node : undefined; },
            hasClass(className) {
                return !! (node && node.classes && node.classes.includes(className));
            },
            prepend() { return this; },
            prev() { return this; },
            ready(callback) {
                callback();
                return this;
            },
            remove() { return this; },
            removeClass() { return this; },
            get length() { return node ? 1 : 0; },
        };
    }

    const $ = function(node) { return wrap(node); };
    $.fn = {};

    const window = {
        __refreshRow() {},
        reloadTableRows() {},
        reloadDatatable(target, options) {
            reloadCalls.push({ target: target, options: options });
            return true;
        },
    };

    vm.runInNewContext(moduleSource, {
        $: $,
        Object: Object,
        String: String,
        clearTimeout() {},
        document: document,
        setTimeout(callback) { callback(); },
        window: window,
    });

    return { editor, handlers, reloadCalls, table, window };
}

test('ordinary table reload waits while an editor owns the row lock', () => {
    const runtime = boot();

    runtime.handlers.focusin({ target: runtime.editor });
    runtime.window.reloadDatatable(runtime.table);

    assert.equal(runtime.reloadCalls.length, 0);
    assert.equal(runtime.window.ibDatatableEditorRowLock.isLocked('orders'), true);
    assert.equal(runtime.window.ibDatatableEditorRowLock.state.tableReloads.orders, runtime.table);
});

test('forced table reload bypasses and clears the editor row lock', () => {
    const runtime = boot();

    runtime.handlers.focusin({ target: runtime.editor });
    runtime.window.reloadDatatable(runtime.table);
    runtime.window.reloadDatatable(runtime.table, { force: true });

    assert.equal(runtime.reloadCalls.length, 1);
    assert.equal(runtime.reloadCalls[0].target, runtime.table);
    assert.equal(runtime.reloadCalls[0].options.force, true);
    assert.equal(runtime.window.ibDatatableEditorRowLock.isLocked('orders'), false);
    assert.equal(runtime.window.ibDatatableEditorRowLock.state.tableReloads.orders, undefined);
});
