const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(
    'resources/assets/js/datatables.vendor.fieldsGroups.js',
    'utf8'
);

function createButtonNode()
{
    const classes = new Set();
    const attributes = {};

    return {
        classList: {
            toggle(name, enabled) {
                if (enabled) classes.add(name);
                else classes.delete(name);
            },
            contains(name) {
                return classes.has(name);
            },
        },
        setAttribute(name, value) {
            attributes[name] = value;
        },
        getAttribute(name) {
            return attributes[name] || null;
        },
    };
}

function boot({ definitions, visibility, route = '/column-display' })
{
    const listeners = [];
    const persistCalls = [];
    const applyCalls = [];
    let adjusted = 0;
    const tableNode = {
        id: 'orders',
        getAttribute(name) {
            return name === 'data-columndisplayroute' ? route : null;
        },
    };

    const dt = {
        table() {
            return { node: () => tableNode };
        },
        column(index) {
            return {
                visible(value) {
                    if (arguments.length === 0)
                        return visibility[index];

                    visibility[index] = value;
                    listeners.forEach((listener) => listener());
                },
            };
        },
        manageColumnVisibility(fieldName, isVisible, options) {
            if (options.mode === 'persistOnly') {
                persistCalls.push({ fieldName, isVisible, options });
                return Promise.resolve({ success: true });
            }

            applyCalls.push({ fieldName, isVisible, options });
            return Promise.resolve({ success: true });
        },
        on(event, listener) {
            listeners.push(listener);
        },
    };

    dt.columns = function () {
        return { count: () => visibility.length };
    };
    dt.columns.adjust = () => { adjusted += 1; };

    const context = {
        Promise,
        window: {
            __ibDatatableFieldsGroups: { orders: definitions },
            __ibDatatableColumnIndexToFieldName: {
                orders: visibility.map((_, index) => `column_${index}`),
            },
        },
        $: {
            fn: {
                dataTable: {
                    ext: { buttons: {} },
                },
            },
        },
    };

    vm.runInNewContext(source, context);

    return {
        api: context.window.ibDatatableFieldsGroups,
        button: context.$.fn.dataTable.ext.buttons.fieldsGroupToggle,
        dt,
        visibility,
        persistCalls,
        applyCalls,
        getAdjusted: () => adjusted,
    };
}

test('toggle hides visible group columns then restores only its own saved subset', async () => {
    const runtime = boot({
        definitions: { economic_minor_details: { columnIndexes: [1, 2] } },
        visibility: [true, true, true, true],
    });

    await runtime.api.toggle(runtime.dt, 'economic_minor_details');

    assert.deepEqual(runtime.visibility, [true, false, false, true]);
    assert.deepEqual(
        JSON.parse(JSON.stringify(runtime.api.getHiddenState(runtime.dt).economic_minor_details)),
        [1, 2]
    );
    assert.equal(runtime.persistCalls.filter((call) => !call.isVisible).length, 2);

    await runtime.api.toggle(runtime.dt, 'economic_minor_details');

    assert.deepEqual(runtime.visibility, [true, true, true, true]);
    assert.deepEqual(
        JSON.parse(JSON.stringify(runtime.api.getHiddenState(runtime.dt).economic_minor_details)),
        []
    );
    assert.equal(runtime.persistCalls.filter((call) => call.isVisible).length, 2);
    assert.equal(runtime.getAdjusted(), 2);
});

test('a fully hidden group from saved preferences is expanded on its first click', async () => {
    const runtime = boot({
        definitions: { texts: { columnIndexes: [1, 2] } },
        visibility: [true, false, false],
    });

    await runtime.api.toggle(runtime.dt, 'texts');

    assert.deepEqual(runtime.visibility, [true, true, true]);
    assert.deepEqual(
        JSON.parse(JSON.stringify(runtime.api.getHiddenState(runtime.dt).texts)),
        []
    );
});

test('overlapping groups retain independent restore state', async () => {
    const runtime = boot({
        definitions: {
            economic_minor_details: { columnIndexes: [1, 2] },
            texts: { columnIndexes: [2, 3] },
        },
        visibility: [true, true, true, true],
    });

    await runtime.api.toggle(runtime.dt, 'economic_minor_details');
    await runtime.api.toggle(runtime.dt, 'texts');
    await runtime.api.toggle(runtime.dt, 'economic_minor_details');

    assert.deepEqual(runtime.visibility, [true, true, true, false]);

    await runtime.api.toggle(runtime.dt, 'texts');

    assert.deepEqual(runtime.visibility, [true, true, true, true]);
});

test('an overlapping group can expand a fully hidden group without leaving stale state', async () => {
    const runtime = boot({
        definitions: {
            economic_minor_details: { columnIndexes: [1, 2] },
            texts: { columnIndexes: [1, 2] },
        },
        visibility: [true, true, true],
    });

    await runtime.api.toggle(runtime.dt, 'economic_minor_details');
    await runtime.api.toggle(runtime.dt, 'texts');

    assert.deepEqual(runtime.visibility, [true, true, true]);
    assert.equal(runtime.api.isGroupActive(runtime.dt, 'economic_minor_details'), false);

    await runtime.api.toggle(runtime.dt, 'economic_minor_details');

    assert.deepEqual(runtime.visibility, [true, false, false]);
});

test('the custom DataTables button updates accessible visual state and ignores unknown groups', async () => {
    const runtime = boot({
        definitions: { texts: { columnIndexes: [1] } },
        visibility: [true, true],
    });
    const buttonNode = createButtonNode();

    runtime.button.init(runtime.dt, buttonNode, {
        fieldGroup: 'texts',
        activeText: 'Mostra testi',
        inactiveText: 'Nascondi testi',
    });
    assert.equal(buttonNode.getAttribute('aria-pressed'), 'false');
    assert.equal(buttonNode.innerHTML, 'Nascondi testi');

    await runtime.button.action(null, runtime.dt, buttonNode, {
        fieldGroup: 'texts',
        activeText: 'Mostra testi',
        inactiveText: 'Nascondi testi',
    });
    assert.equal(buttonNode.classList.contains('uk-button-primary'), true);
    assert.equal(buttonNode.getAttribute('aria-pressed'), 'true');
    assert.equal(buttonNode.innerHTML, 'Mostra testi');

    await assert.doesNotReject(() => runtime.button.action(null, runtime.dt, buttonNode, { fieldGroup: 'missing' }));
});

test('the custom button also updates its text when Buttons provides a jQuery node', async () => {
    const runtime = boot({
        definitions: { texts: { columnIndexes: [1] } },
        visibility: [true, true],
    });
    const buttonNode = createButtonNode();
    const jqueryNode = { 0: buttonNode, length: 1, jquery: '3.7.1' };
    const config = {
        fieldGroup: 'texts',
        activeText: 'Mostra testi',
        inactiveText: 'Nascondi testi',
    };

    runtime.button.init(runtime.dt, jqueryNode, config);
    await runtime.button.action(null, runtime.dt, jqueryNode, config);

    assert.equal(buttonNode.innerHTML, 'Mostra testi');
    assert.equal(buttonNode.getAttribute('aria-pressed'), 'true');
});

test('the reusable API does not depend on dropdown markup or simulated menu clicks', () => {
    assert.equal(source.includes('__ibFieldsGroupsDrop'), false);
    assert.equal(source.includes('__ibFieldsGroupsMenuEl'), false);
    assert.equal(source.includes('.click('), false);
    assert.equal(source.includes('Gruppi colonne'), false);
});
