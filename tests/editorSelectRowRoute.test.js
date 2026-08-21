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
    const start = moduleSource.indexOf('window.ibDtLoadSelectPossibleValuesRow = function');
    const end = moduleSource.indexOf('window.ibDtFlattenSelectPossibleValues');

    assert.notEqual(start, -1);
    assert.notEqual(end, -1);

    return moduleSource.slice(start, end);
}

function boot(response)
{
    const requests = [];
    const tableNode = { attributes: { id: 'orders' } };

    function wrap(node)
    {
        return {
            attr(name) {
                return node.attributes?.[name];
            },
        };
    }

    function $(node)
    {
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
