const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(
    'resources/assets/js/datatables.vendor.selectionActions.js',
    'utf8'
);

function boot(selected = 0)
{
    const handlers = [];
    const action = {
        classes: new Set(['ib-dt-selection-action', 'uk-hidden']),
        disabled: false,
        attributes: {},
    };
    const actions = {
        length: 1,
        toggleClass(name, enabled) {
            if (enabled)
                action.classes.add(name);
            else
                action.classes.delete(name);

            return this;
        },
        prop(name, value) {
            action[name] = value;

            return this;
        },
        attr(name, value) {
            action.attributes[name] = value;

            return this;
        },
    };
    const container = {};
    const dt = {
        table() {
            return { container: () => container };
        },
        rows() {
            return { count: () => selected };
        },
    };
    const $ = function (node)
    {
        if (node === document)
            return {
                on(events, handler) {
                    handlers.push({ events, handler });
                },
            };

        if (node === container)
            return { find: () => actions };

        throw new Error('Unexpected jQuery target');
    };

    $.fn = {
        dataTable: {
            Api: function (settings) { return settings; },
        },
    };

    const document = {};
    const context = {
        document,
        setTimeout: () => {},
        window: { jQuery: $, $ },
        $,
    };

    vm.runInNewContext(source, context);

    return {
        action,
        dt,
        handlers,
        select(count) { selected = count; },
        sync: context.window.ibDtSyncSelectionActionButtons,
    };
}

test('selection actions stay hidden and disabled until a row is selected', () => {
    const runtime = boot();

    runtime.sync(runtime.dt);

    assert.equal(runtime.action.classes.has('uk-hidden'), true);
    assert.equal(runtime.action.disabled, true);
    assert.equal(runtime.action.attributes['aria-hidden'], 'true');
});

test('selection actions appear as soon as at least one row is selected', () => {
    const runtime = boot();

    runtime.select(2);
    runtime.sync(runtime.dt);

    assert.equal(runtime.action.classes.has('uk-hidden'), false);
    assert.equal(runtime.action.disabled, false);
    assert.equal(runtime.action.attributes['aria-hidden'], 'false');
});

test('DataTables select and deselect events resync selection actions', () => {
    const runtime = boot();
    const selectionHandler = runtime.handlers.find(({ events }) => events.includes('select.dt.ibSelectionActions'));

    runtime.select(1);
    selectionHandler.handler({}, runtime.dt);
    assert.equal(runtime.action.classes.has('uk-hidden'), false);

    runtime.select(0);
    selectionHandler.handler({}, runtime.dt);
    assert.equal(runtime.action.classes.has('uk-hidden'), true);
});
