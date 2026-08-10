const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.editorSelect2.js',
    'utf8'
);

function boot(selectData)
{
    const sequence = [];
    const handlers = {};
    const documentNode = { kind: 'document', data: {} };
    const selectionNode = { kind: 'selection', data: {} };
    const containerNode = { kind: 'container', data: {}, selection: selectionNode };
    const headerNode = { kind: 'header', data: { name: 'stato' } };
    const tableNode = { kind: 'table', data: {}, attributes: { id: 'orders' } };

    const selectNode = {
        kind: 'select',
        data: selectData || {},
        container: containerNode,
    };

    function wrap(node)
    {
        return {
            node: node,
            attr(name) {
                return node && node.attributes ? node.attributes[name] : undefined;
            },
            data(name) {
                return node && node.data ? node.data[name] : undefined;
            },
            find() {
                return wrap(node && node.selection);
            },
            next() {
                return wrap(node && node.container);
            },
            on(eventName, selector, handler) {
                handlers[eventName] = { selector: selector, handler: handler };
                return this;
            },
            ready(callback) {
                callback();
                return this;
            },
            select2(action) {
                sequence.push({ call: 'select2', node: node.kind, action: action });

                if (typeof action === 'object')
                    node.data.select2 = {};

                return this;
            },
            trigger(eventName) {
                sequence.push({ call: 'trigger', node: node.kind, event: eventName });
                return this;
            },
        };
    }

    const window = {
        __getTableByCell(cell) {
            sequence.push({ call: '__getTableByCell', node: cell.kind });
            return wrap(tableNode);
        },
        __getTH(cell) {
            sequence.push({ call: '__getTH', node: cell.kind });
            return wrap(headerNode);
        },
        ibDtGetSelectPossibleValues(tableId, fieldName) {
            sequence.push({ call: 'ibDtGetSelectPossibleValues', tableId: tableId, fieldName: fieldName });
            return { a: 'Alfa', b: 'Beta' };
        },
        ibDtPopulateSelectOptions($select, possibleValues) {
            sequence.push({ call: 'ibDtPopulateSelectOptions', possibleValues: possibleValues });
        },
    };

    vm.runInNewContext(moduleSource, {
        $: function(node) { return wrap(node); },
        document: documentNode,
        window: window,
    });

    return { containerNode, handlers, selectNode, selectionNode, sequence, window };
}

function names(sequence)
{
    return sequence.map(function(entry) { return entry.call; });
}

test('the first mousedown fills the options before select2 takes over the select', () => {
    const runtime = boot();
    let prevented = false;

    runtime.handlers.mousedown.handler.call(runtime.selectNode, {
        preventDefault() { prevented = true; },
    });

    assert.equal(runtime.handlers.mousedown.selector, 'table.datatable tbody select.ib-editor-select2');
    assert.equal(prevented, true);
    assert.deepEqual(names(runtime.sequence), [
        '__getTableByCell',
        '__getTH',
        'ibDtGetSelectPossibleValues',
        'ibDtPopulateSelectOptions',
        'select2',
        'select2',
    ]);
    assert.deepEqual(runtime.sequence[3].possibleValues, { a: 'Alfa', b: 'Beta' });
    assert.equal(runtime.sequence[5].action, 'open');
});

test('possible values carried by the cell skip the table header lookup', () => {
    const runtime = boot({ 'possible-values': { x: 'Ics' } });

    runtime.handlers.mousedown.handler.call(runtime.selectNode, { preventDefault() {} });

    assert.deepEqual(names(runtime.sequence), [
        'ibDtPopulateSelectOptions',
        'select2',
        'select2',
    ]);
    assert.deepEqual(runtime.sequence[0].possibleValues, { x: 'Ics' });
});

test('an already initialized select is left untouched', () => {
    const runtime = boot({ select2: {} });
    let prevented = false;

    runtime.handlers.mousedown.handler.call(runtime.selectNode, {
        preventDefault() { prevented = true; },
    });
    runtime.handlers.focusin.handler.call(runtime.selectNode);

    assert.equal(prevented, false);
    assert.deepEqual(runtime.sequence, []);
});

test('reaching the cell by keyboard moves the focus onto the select2 container', () => {
    const runtime = boot();

    runtime.handlers.focusin.handler.call(runtime.selectNode);

    assert.equal(runtime.handlers.focusin.selector, 'table.datatable tbody select.ib-editor-select2');
    assert.deepEqual(names(runtime.sequence), [
        '__getTableByCell',
        '__getTH',
        'ibDtGetSelectPossibleValues',
        'ibDtPopulateSelectOptions',
        'select2',
        'trigger',
    ]);
    assert.equal(runtime.sequence[5].node, 'selection');
    assert.equal(runtime.sequence[5].event, 'focus');
});
