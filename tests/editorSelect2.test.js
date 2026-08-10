const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.editorSelect2.js',
    'utf8'
);

function boot(selects)
{
    const sequence = [];
    const handlers = {};
    const documentNode = { kind: 'document', data: {} };
    const headerNode = { kind: 'header', data: { name: 'stato' } };
    const tableNode = { kind: 'table', data: {}, attributes: { id: 'orders' }, selects: selects };

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
            each(callback) {
                (node && node.matched ? node.matched : []).forEach(function(matchedNode) {
                    callback.call(matchedNode);
                });
                return this;
            },
            find(selector) {
                sequence.push({ call: 'find', selector: selector });
                return wrap({ kind: 'set', data: {}, matched: node && node.selects ? node.selects : [] });
            },
            on(eventName, selector, handler) {
                handlers[eventName] = { selector: selector, handler: handler };
                return this;
            },
            select2(options) {
                sequence.push({ call: 'select2', node: node.kind, options: options });
                node.data.select2 = {};
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

    vm.runInNewContext(moduleSource, {
        $: function(node) { return wrap(node); },
        document: documentNode,
        window: window,
    });

    return { handlers, sequence, tableNode, window };
}

function names(sequence)
{
    return sequence.map(function(entry) { return entry.call; });
}

function makeSelect(data)
{
    return { kind: 'select', data: data || {} };
}

test('a draw fills the options and hands each select over to select2', () => {
    const select = makeSelect();
    const runtime = boot([select]);

    assert.equal(runtime.handlers['draw.dt'].selector, 'table.dataTable');

    runtime.handlers['draw.dt'].handler.call(runtime.tableNode);

    assert.deepEqual(names(runtime.sequence), [
        'find',
        'ibDtGetSelectPossibleValues',
        'ibDtPopulateSelectOptions',
        'select2',
    ]);
    assert.equal(runtime.sequence[0].selector, 'select.ib-editor-select2');
    assert.equal(runtime.sequence[1].tableId, 'orders');
    assert.equal(runtime.sequence[1].fieldName, 'stato');
    assert.deepEqual(runtime.sequence[2].possibleValues, { a: 'Alfa', b: 'Beta' });
    assert.equal(runtime.sequence[3].options.width, '100%');
    assert.equal(runtime.sequence[3].options.dropdownAutoWidth, true);
});

test('possible values carried by the cell skip the table header lookup', () => {
    const select = makeSelect({ 'possible-values': { x: 'Ics' } });
    const runtime = boot([select]);

    runtime.handlers['draw.dt'].handler.call(runtime.tableNode);

    assert.deepEqual(names(runtime.sequence), [
        'find',
        'ibDtPopulateSelectOptions',
        'select2',
    ]);
    assert.deepEqual(runtime.sequence[1].possibleValues, { x: 'Ics' });
});

test('a redraw leaves the selects already handed over untouched', () => {
    const pristine = makeSelect();
    const initialized = makeSelect({ select2: {} });
    const runtime = boot([initialized, pristine]);

    runtime.handlers['draw.dt'].handler.call(runtime.tableNode);

    assert.deepEqual(names(runtime.sequence), [
        'find',
        'ibDtGetSelectPossibleValues',
        'ibDtPopulateSelectOptions',
        'select2',
    ]);
});

test('a patched cell reinitializes its selects through the exposed helper', () => {
    const select = makeSelect();
    const runtime = boot([]);
    const cell = { kind: 'cell', data: {}, selects: [select] };

    runtime.window.ibDtInitEditorSelect2sInRoot(cell);

    assert.deepEqual(names(runtime.sequence), [
        'find',
        'ibDtGetSelectPossibleValues',
        'ibDtPopulateSelectOptions',
        'select2',
    ]);
});
