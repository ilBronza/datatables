const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.ajaxButton.min.js',
    'utf8'
);

function extractLoader()
{
    const start = moduleSource.indexOf('window.ibDtGetSelectRowRouteFields = function');
    const end = moduleSource.indexOf('window.ibDtFlattenSelectPossibleValues');

    assert.notEqual(start, -1);
    assert.notEqual(end, -1);

    return moduleSource.slice(start, end);
}

function boot(response)
{
    const requests = [];
    const tableNode = { attributes: { id: 'orders' } };

    function wrap(input)
    {
        const nodes = Array.isArray(input) ? input : (input ? [input] : []);
        const node = nodes[0];
        return {
            ...nodes,
            length: nodes.length,
            attr(name) {
                return node?.attributes?.[name];
            },
            data(name, value) {
                node.data ??= {};
                if (arguments.length === 1) return node.data[name];
                node.data[name] = value;
                return this;
            },
            removeData(name) { delete (node.data ??= {})[name]; return this; },
            closest() { return wrap(node?.row); },
            find(selector) {
                const fields = node?.fields || [];
                return wrap(selector.startsWith('select[')
                    ? fields.filter(field => field.attributes?.['data-possible-values-row-route-fields'])
                    : fields);
            },
            filter(callback) { return wrap(nodes.filter(item => callback.call(item))); },
            each(callback) { nodes.forEach(item => callback.call(item)); return this; },
            is(selector) { return selector === ':checkbox' && node.type === 'checkbox'; },
            prop(name) { return node[name]; },
            on() { return this; },
            empty() { node.options = []; node.value = null; return this; },
            append(option) { node.options.push(option[0]); node.value = option[0].value; return this; },
            val(value) { if (! arguments.length) return node.value; node.value = value; return this; },
            text(value) { node.text = value; return this; },
            trigger(event) { (node.events ??= []).push(event); return this; },
            select2(action) { (node.select2Actions ??= []).push(action); return this; },
        };
    }

    function $(node)
    {
        if (typeof node === 'string') return wrap({});
        return wrap(node);
    }

    $.get = function(url, data)
    {
        requests.push({ url, data });

        return Promise.resolve(response);
    };

    const context = {
        $,
        Promise,
        encodeURIComponent,
        Error,
        window: {
            __ibGetEditorSubmitValue(field) { return field.value; },
            __getTableByCell() {
                return wrap(tableNode);
            },
            __getTH() {
                return { data() { return 'vehicle_id'; } };
            },
        },
    };

    vm.runInNewContext(extractLoader(), context);

    return { requests, window: context.window };
}

test('row route replaces the model placeholder and sends row context', async () => {
    const runtime = boot({ list: { 4: 'Panda' } });
    const select = {
        attributes: {
            'data-possible-values-row-route': '/orders/replace_model_id_string/vehicles',
            'data-possible-values-row-route-placeholder': 'replace_model_id_string',
            'data-row-id': '42',
            'data-field': 'vehicle_id',
        },
    };

    const list = await runtime.window.ibDtLoadSelectPossibleValuesRow(select);

    assert.deepEqual(JSON.parse(JSON.stringify(list)), { 4: 'Panda' });
    assert.deepEqual(JSON.parse(JSON.stringify(runtime.requests)), [{
        url: '/orders/42/vehicles',
        data: { row_id: '42', field: 'vehicle_id', table_id: 'orders' },
    }]);
});

test('row route accepts a direct list response', async () => {
    const runtime = boot({ 7: 'Golf' });
    const select = {
        attributes: {
            'data-possible-values-row-route': '/vehicle-options',
            'data-row-id': '42',
            'data-field': 'vehicle_id',
        },
    };

    const list = await runtime.window.ibDtLoadSelectPossibleValuesRow(select);

    assert.deepEqual(JSON.parse(JSON.stringify(list)), { 7: 'Golf' });
});

test('a select without a row route keeps the non-AJAX fallback path', () => {
    const runtime = boot({ list: {} });

    assert.equal(runtime.window.ibDtLoadSelectPossibleValuesRow({ attributes: {} }), null);
    assert.deepEqual(runtime.requests, []);
});

function contextualRow(id, sellable, operator = 'old-operator')
{
    const row = { fields: [] };
    const source = { row, value: sellable, attributes: { 'data-field': 'sellable_id' } };
    const select = {
        row, value: operator,
        attributes: {
            'data-possible-values-row-route': '/operator-options',
            'data-row-id': id,
            'data-field': 'sellable_supplier_id',
            'data-possible-values-row-route-fields': '["sellable_id"]',
            'data-possible-values-row-route-empty-value': 'null',
            'data-possible-values-row-route-empty-label': 'Da assegnare',
        },
    };
    row.fields = [source, select];
    return { row, source, select };
}

test('each opening sends the current draft from the same row, including an empty value', async () => {
    const runtime = boot({ list: {} });
    const first = contextualRow('1', 'sound');
    const second = contextualRow('2', 'lights');

    await runtime.window.ibDtLoadSelectPossibleValuesRow(first.select);
    await runtime.window.ibDtLoadSelectPossibleValuesRow(second.select);
    first.source.value = '';
    await runtime.window.ibDtLoadSelectPossibleValuesRow(first.select);

    assert.deepEqual(runtime.requests.map(item => item.data.sellable_id), ['sound', 'lights', '']);
    assert.deepEqual(runtime.requests.map(item => item.data.row_id), ['1', '2', '1']);
});

test('missing context is rejected instead of silently using a different row or persisted state', async () => {
    const runtime = boot({ list: {} });
    const { row, select } = contextualRow('1', 'sound');
    row.fields = [select];
    await assert.rejects(runtime.window.ibDtLoadSelectPossibleValuesRow(select), /mancante o ambiguo/);
    assert.equal(runtime.requests.length, 0);
});

test('a response cannot populate options for an obsolete draft', async () => {
    let resolve;
    const runtime = boot(new Promise(done => { resolve = done; }));
    const { source, select } = contextualRow('1', 'sound');
    const request = runtime.window.ibDtLoadSelectPossibleValuesRow(select);
    source.value = 'lights';
    resolve({ list: { 'sound-operator': 'Sound operator' } });
    await assert.rejects(request, { name: 'StaleSelectOptionsError' });
});

test('changing a source resets only its row, marks the draft, and never dispatches change/autosave', () => {
    const runtime = boot({ list: {} });
    const first = contextualRow('1', 'sound');
    const second = contextualRow('2', 'sound');
    first.select.data = { select2: {}, possibleValuesRowRouteReady: true };
    runtime.window.ibDtInvalidateSelectRowDependents(first.source);
    assert.equal(first.select.value, 'null');
    assert.equal(first.select.options[0].text, 'Da assegnare');
    assert.deepEqual(first.select.events, ['input', 'change.select2']);
    assert.deepEqual(first.select.select2Actions, ['close']);
    assert.equal(first.select.data.possibleValuesRowRouteReady, undefined);
    assert.equal(second.select.value, 'old-operator');
    assert.equal(runtime.requests.length, 0);
});

test('an in-flight request stays obsolete even after the source changes back to its initial value', async () => {
    let resolve;
    const runtime = boot(new Promise(done => { resolve = done; }));
    const { source, select } = contextualRow('1', 'sound');
    const request = runtime.window.ibDtLoadSelectPossibleValuesRow(select);
    source.value = 'lights';
    runtime.window.ibDtInvalidateSelectRowDependents(source);
    source.value = 'sound';
    resolve({ list: {} });
    await assert.rejects(request, { name: 'StaleSelectOptionsError' });
});

test('multi-select and checkbox context preserve their current values', async () => {
    const runtime = boot({ list: {} });
    const { source, select } = contextualRow('1', ['sound', 'lights']);
    await runtime.window.ibDtLoadSelectPossibleValuesRow(select);
    assert.deepEqual(runtime.requests[0].data.sellable_id, ['sound', 'lights']);
    source.type = 'checkbox';
    source.checked = false;
    await runtime.window.ibDtLoadSelectPossibleValuesRow(select);
    assert.equal(runtime.requests[1].data.sellable_id, false);
});

test('contextual option loading drops an obsolete value while legacy selects preserve it', () => {
    const runtime = boot({ list: {} });
    const start = moduleSource.indexOf('window.ibDtPopulateSelectOptions = function');
    const end = moduleSource.indexOf('window.ibDtActivateSelectCustomValue = function');
    runtime.window.ibDtAppendCustomValueOption = () => {};
    runtime.window.ibDtFlattenSelectPossibleValues = options => options;
    runtime.window.ibDtGetSelectOptionLabel = (options, key) => options[key] || '';
    vm.runInNewContext(moduleSource.slice(start, end), { window: runtime.window });

    function select(contextual, value = 'old') {
        return {
            html: [],
            val() { return value; },
            attr(name) {
                return {
                    'data-possible-values-row-route-fields': contextual ? '["sellable_id"]' : undefined,
                    'data-possible-values-row-route-empty-value': 'null',
                    'data-possible-values-row-route-empty-label': 'Da assegnare',
                }[name];
            },
            find() { return { text() { return 'Previous operator'; } }; },
            empty() { return this; },
            append(html) { this.html.push(html); return this; },
        };
    }

    const contextual = select(true);
    const legacy = select(false);
    const valid = select(true, 'new');
    for (const control of [contextual, legacy, valid])
        runtime.window.ibDtPopulateSelectOptions(control, { null: 'Da assegnare', new: 'New operator' });

    assert.ok(! contextual.html.join('').includes('value="old"'));
    assert.match(contextual.html[0], /selected value="null"/);
    assert.match(legacy.html[0], /selected value="old"/);
    assert.match(valid.html[0], /selected value="new"/);
});
