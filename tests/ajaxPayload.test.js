const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.ajaxCall.min.js',
    'utf8'
);

function extractHelpers()
{
    const start = moduleSource.indexOf('function __ibAjaxIsBlob');
    const end = moduleSource.indexOf('function __ibAppendFormDataValue');

    assert.notEqual(start, -1);
    assert.notEqual(end, -1);

    return moduleSource.slice(start, end) + '\n' +
        'globalThis.ajaxPayloadHelpers = { __ibAjaxDataContainsFile, __ibGetDeclaredElementData };';
}

function boot()
{
    class FakeBlob {}
    class FakeFileList {}

    function wrap(element)
    {
        return {
            get() { return element; },
            data(key) { return element.jqueryData[key]; },
        };
    }

    const context = {
        $: wrap,
        Blob: FakeBlob,
        FileList: FakeFileList,
        window: {},
    };

    vm.runInNewContext(extractHelpers(), context);

    return { ...context.ajaxPayloadHelpers, FakeBlob };
}

test('file detection stops at circular runtime plugin data', () => {
    const runtime = boot();
    const select2 = { options: {} };
    const option = { plugin: select2 };

    select2.options.element = option;

    assert.equal(runtime.__ibAjaxDataContainsFile({ fieldData: { select2 } }), false);
});

test('file detection still finds files inside ordinary payload objects', () => {
    const runtime = boot();

    assert.equal(runtime.__ibAjaxDataContainsFile({ nested: [new runtime.FakeBlob()] }), true);
});

test('field data contains declared attributes but not jQuery plugin state', () => {
    const runtime = boot();
    const element = {
        attributes: [
            { name: 'class', value: 'ib-editor-select' },
            { name: 'data-field', value: 'airport' },
            { name: 'data-originalvalue', value: '' },
            { name: 'data-custom-value-mode', value: 'false' },
            { name: 'data-select2-id', value: 'select2-data-1' },
        ],
        jqueryData: {
            field: 'airport',
            originalvalue: '',
            customValueMode: false,
            select2Id: 'select2-data-1',
            select2: { circular: true },
        },
    };

    const result = runtime.__ibGetDeclaredElementData(element);

    assert.deepEqual(JSON.parse(JSON.stringify(result)), {
        field: 'airport',
        originalvalue: '',
        customValueMode: false,
    });
});
