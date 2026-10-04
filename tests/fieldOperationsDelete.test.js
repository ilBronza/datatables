const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync('resources/assets/js/datatables.vendor.utilities.min.js', 'utf8');

function boot(ajaxResult = {}, rowId = 42)
{
    const handlers = [];
    const calls = [];
    const classes = new Set(['ibop']);
    const link = {href: '/orders/replace_model_id_string'};
    const cell = {removed: 0};
    const document = {};

    const linkWrapper = {
        closest(selector) {
            return selector === 'a' ? linkWrapper : cellWrapper;
        },
        attr(name, value) {
            if (value === undefined)
                return link[name];

            link[name] = value;
            return this;
        },
    };
    const cellWrapper = {
        addClass(name) { classes.add(name); return this; },
        removeClass(name) { classes.delete(name); return this; },
        hasClass(name) { return classes.has(name); },
        find(selector) {
            assert.equal(selector, 'div.fieldoperations');
            return {remove() { cell.removed++; }};
        },
    };

    const $ = function (target)
    {
        if (target === document)
            return {
                ready(callback) { callback(); },
                on() {},
            };

        if (target === 'body')
            return {on(event, selector, handler) { handlers.push({event, selector, handler}); }};

        if (target === link)
            return linkWrapper;

        if (target === cell)
            return cellWrapper;

        throw new Error('Unexpected jQuery target');
    };

    $.fn = {dataTable: {Api: {register() {}}}};

    const window = {
        replace_model_id_string: 'replace_model_id_string',
        __getCell() { return {data() { return [rowId, 'Order 42']; }}; },
        ibCallAjax(params) { calls.push(params); return ajaxResult; },
    };

    vm.runInNewContext(source, {window, document, $, jQuery: $, setTimeout() {}, console: {log() {}}});

    return {handlers, calls, classes, link, cell, window};
}

function handler(runtime, event, selector)
{
    const match = runtime.handlers.find(item => item.event === event && item.selector === selector);
    assert.ok(match, `Missing handler for ${event} ${selector}`);
    return match.handler;
}

test('delete operation replaces the row ID and retains its cell until Ajax completes', () => {
    const runtime = boot();
    let prevented = false;

    handler(runtime, 'click', '.ibfieldoperationdelete').call(runtime.link, {
        preventDefault() { prevented = true; },
    });

    assert.equal(prevented, true);
    assert.equal(runtime.link.href, '/orders/42');
    assert.equal(runtime.calls.length, 1);
    assert.equal(runtime.calls[0].target, runtime.link);
    assert.equal(runtime.classes.has('ib-field-operation-delete-pending'), true);

    handler(runtime, 'click', '.ibfieldoperationdelete').call(runtime.link, {
        preventDefault() {},
    });
    assert.equal(runtime.calls.length, 1);

    handler(runtime, 'mouseleave', '.ibop').call(runtime.cell);
    assert.equal(runtime.cell.removed, 0);

    runtime.calls[0].onComplete();
    assert.equal(runtime.classes.has('ib-field-operation-delete-pending'), false);
    assert.equal(runtime.cell.removed, 1);
});

test('cancelled delete confirmation releases the hover controls', () => {
    const runtime = boot(false);

    handler(runtime, 'click', '.ibfieldoperationdelete').call(runtime.link, {
        preventDefault() {},
    });

    assert.equal(runtime.classes.has('ib-field-operation-delete-pending'), false);
    assert.equal(runtime.cell.removed, 1);
});

test('delete operation ignores a cell without a row ID', () => {
    const runtime = boot({}, null);

    handler(runtime, 'click', '.ibfieldoperationdelete').call(runtime.link, {
        preventDefault() {},
    });

    assert.equal(runtime.calls.length, 0);
    assert.equal(runtime.link.href, '/orders/replace_model_id_string');
});
