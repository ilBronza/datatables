const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.ajaxButton.min.js',
    'utf8'
);

function field(value, { type = 'numeric', price = false, original = '', decimals = 2, inputmode } = {})
{
    return {
        value,
        attributes: { type, inputmode, 'data-originalvalue': original },
        classes: new Set(price ? ['ib-dt-price-editor'] : []),
        data: { field: 'amount', 'dt-price-decimals': decimals },
    };
}

function boot()
{
    const handlers = new Map();
    const requests = [];

    function $(node)
    {
        return {
            on(event, selector, handler) {
                handlers.set(event, handler);
                return this;
            },
            ready() {},
            hasClass(name) { return node.classes.has(name); },
            attr(name, value) {
                if (arguments.length === 1)
                    return node.attributes[name];
                node.attributes[name] = value;
                return this;
            },
            data(name, value) {
                if (arguments.length === 1)
                    return node.data[name];
                node.data[name] = value;
                return this;
            },
            val(value) {
                if (arguments.length === 0)
                    return node.value;
                node.value = value;
                return this;
            },
            removeData(name) { delete node.data[name]; return this; },
            removeClass() { return this; },
            closest() { return this; },
            find() { return { length: 0 }; },
        };
    }

    const window = { __ibDtEditorDebug: false };
    const context = { $, window, document: {} };

    vm.runInNewContext(moduleSource, context);

    const start = moduleSource.indexOf("    $('body').on('ib:dt-editor-save', '.ib-editor-text'");
    const end = moduleSource.indexOf('    // Save on Enter', start);
    assert.ok(start >= 0 && end > start);
    vm.runInNewContext(moduleSource.slice(start, end), context);

    window.manageEditorFieldSavingPermission = () => true;
    window.__ibUnmarkFieldDirty = () => {};
    window.__ibTogglePerTableSaveButton = () => {};
    window.ibCallAjax = (params) => requests.push(params);

    return {
        window,
        requests,
        save(target) {
            handlers.get('ib:dt-editor-save').call(target, {}, { source: 'enter' });
        },
    };
}

test('numeric and price editors send either decimal separator through AJAX without losing precision', () => {
    for (const options of [{}, { type: 'text', inputmode: 'decimal' }, { price: true }])
    {
        for (const value of ['12,202', '12.202', '-12,202', '-12.202', '0,000123', '0.000123'])
        {
            const runtime = boot();
            const target = field(value, options);
            runtime.save(target);

            assert.equal(runtime.requests.length, 1);
            assert.equal(runtime.requests[0].data.value, value.replace(',', '.'));
            assert.equal(target.attributes['data-originalvalue'], value.replace(',', '.'));
        }
    }
});

test('price decimal parsing is independent of configured display precision', () => {
    const { window } = boot();

    for (const decimals of [0, 2, 3, 6])
    {
        for (const value of ['12,202', '12.202', '12,', '12.', ',202', '.202'])
            assert.equal(
                window.__ibGetEditorSubmitValue(field(value, { price: true, decimals })),
                value.replace(',', '.')
            );
    }
});

test('formatted prices still remove thousands separators and accept extra decimal digits', () => {
    const { window } = boot();

    for (const [value, expected] of [
        ['1.234,56', '1234.56'],
        ['1,234.56', '1234.56'],
        ['12.202,00', '12202.00'],
        ['-1.234.567,8901', '-1234567.8901'],
        ['-1,234,567.8901', '-1234567.8901'],
    ])
        assert.equal(window.__ibGetEditorSubmitValue(field(value, { price: true })), expected);

    assert.equal(window.__ibFormatDatatablePriceEditorValue(field('', { price: true }), '12.202'), '12,20');
});

test('changing only the decimal separator does not mark an editor as changed or submit AJAX', () => {
    for (const price of [false, true])
    {
        const runtime = boot();
        const target = field('12,202', { price, original: '12.202' });

        assert.equal(runtime.window.editorFieldHasChanged(target), false);
        runtime.save(target);
        assert.equal(runtime.requests.length, 0);
    }
});

test('blank and zero numeric values keep their existing meaning', () => {
    const { window } = boot();

    for (const price of [false, true])
    {
        assert.equal(window.__ibGetEditorSubmitValue(field('', { price })), '');
        assert.equal(window.__ibGetEditorSubmitValue(field('0', { price })), '0');
        assert.equal(window.__ibGetEditorSubmitValue(field('0,000', { price })), '0.000');
        assert.equal(window.editorFieldHasChanged(field('', { price, original: '0' })), false);
    }
});

test('ordinary text and native date editors are not normalized', () => {
    const { window } = boot();

    for (const [type, value] of [['text', '12,202'], ['date', '2026-09-17'], ['time', '12:20']])
        assert.equal(window.__ibGetEditorSubmitValue(field(value, { type })), value);
});
