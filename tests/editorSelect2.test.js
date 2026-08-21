const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.editorSelect2.js',
    'utf8'
);

function boot(selectData, rowRoutePromise)
{
    const sequence = [];
    const handlers = {};
    const documentNode = { kind: 'document', data: {} };
    const headerNode = { kind: 'header', data: { name: 'stato' } };
    const selectionNode = { kind: 'selection', data: {} };
    const containerNode = { kind: 'container', data: {}, selection: selectionNode };
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
            data(name, value) {
                if (! node || ! node.data)
                    return undefined;

                if (arguments.length > 1)
                {
                    node.data[name] = value;

                    return this;
                }

                return node.data[name];
            },
            removeData(name) {
                if (node && node.data)
                    delete node.data[name];

                return this;
            },
            each(callback) {
                (node && node.matched ? node.matched : []).forEach(function(matchedNode) {
                    callback.call(matchedNode);
                });
                return this;
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
            return wrap(tableNode);
        },
        __getTH(cell) {
            return wrap(headerNode);
        },
        ibDtGetSelectPossibleValues(tableId, fieldName) {
            sequence.push({ call: 'ibDtGetSelectPossibleValues', tableId: tableId, fieldName: fieldName });
            return { a: 'Alfa', b: 'Beta' };
        },
        ibDtPopulateSelectOptions($select, possibleValues) {
            sequence.push({ call: 'ibDtPopulateSelectOptions', node: $select.node.kind, possibleValues: possibleValues });
        },
    };

    if (rowRoutePromise)
        window.ibDtLoadSelectPossibleValuesRow = function() { return rowRoutePromise; };

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

    assert.deepEqual(names(runtime.sequence), [
        'ibDtGetSelectPossibleValues',
        'ibDtPopulateSelectOptions',
        'select2',
        'select2',
    ]);
    assert.equal(runtime.handlers.mousedown.selector, 'table.datatable tbody select.ib-editor-select2');
    assert.equal(prevented, true);
    assert.equal(runtime.sequence[0].tableId, 'orders');
    assert.equal(runtime.sequence[0].fieldName, 'stato');
    assert.deepEqual(runtime.sequence[1].possibleValues, { a: 'Alfa', b: 'Beta' });
    assert.equal(runtime.sequence[2].action.width, '100%');
    assert.equal(runtime.sequence[3].action, 'open');
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

    assert.deepEqual(names(runtime.sequence), [
        'ibDtGetSelectPossibleValues',
        'ibDtPopulateSelectOptions',
        'select2',
        'trigger',
    ]);
    assert.equal(runtime.handlers.focusin.selector, 'table.datatable tbody select.ib-editor-select2');
    assert.equal(runtime.sequence[3].node, 'selection');
    assert.equal(runtime.sequence[3].event, 'focus');
});

test('a row route populates the select before the first Select2 opening', async () => {
    const runtime = boot({}, Promise.resolve({ c: 'Charlie' }));

    runtime.handlers.mousedown.handler.call(runtime.selectNode, { preventDefault() {} });
    await Promise.resolve();
    await Promise.resolve();

    assert.deepEqual(names(runtime.sequence), [
        'ibDtPopulateSelectOptions',
        'select2',
        'select2',
    ]);
    assert.deepEqual(runtime.sequence[0].possibleValues, { c: 'Charlie' });
    assert.equal(runtime.sequence[2].action, 'open');
});
