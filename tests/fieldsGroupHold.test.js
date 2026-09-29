const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync('resources/assets/js/datatables.vendor.fieldsGroups.js', 'utf8');

function eventTarget(extra = {}) {
    const listeners = new Map();

    return {
        ...extra,
        addEventListener(name, listener) {
            if (!listeners.has(name)) listeners.set(name, new Set());
            listeners.get(name).add(listener);
        },
        removeEventListener(name, listener) {
            listeners.get(name)?.delete(listener);
        },
        dispatch(name, event = {}) {
            for (const listener of listeners.get(name) || []) listener(event);
        },
    };
}

function boot(initialVisibility = [true, true, true]) {
    const visibility = initialVisibility.slice();
    const savedVisibility = [];
    const settings = { oFeatures: { bStateSave: true } };
    const tableNode = eventTarget({ id: 'operatorRows', __summaryActive: false });
    const classes = new Set();
    const attributes = {};
    const buttonNode = eventTarget({
        style: {},
        classList: {
            toggle(name, enabled) {
                if (enabled) classes.add(name);
                else classes.delete(name);
            },
            contains(name) { return classes.has(name); },
        },
        setAttribute(name, value) { attributes[name] = value; },
        getAttribute(name) { return attributes[name]; },
        setPointerCapture() {},
    });
    let adjusted = 0;
    let initializing = true;

    const dt = {
        table() { return { node: () => tableNode }; },
        settings() { return [settings]; },
        column(index) {
            return { visible() { return visibility[index]; } };
        },
        button() { return { trigger() {} }; },
    };

    dt.columns = function (indexes) {
        return {
            count() { return visibility.length; },
            visible(value) {
                for (const index of indexes) visibility[index] = value;
                if (settings.oFeatures.bStateSave) savedVisibility.push(visibility.slice());
            },
        };
    };
    dt.columns.adjust = () => {
        if (initializing) throw new Error('scroll body not initialized');
        adjusted += 1;
    };

    const window = eventTarget({
        __ibDatatableFieldsGroups: {
            operatorRows: {
                gross_costs: { columnIndexes: [1, 2], holdOnly: true },
                normal: { columnIndexes: [0] },
            },
        },
    });
    const context = {
        window,
        Promise,
        WeakMap,
        $: { fn: { dataTable: { ext: { buttons: {} } } } },
    };
    vm.runInNewContext(source, context);

    const holdButton = context.$.fn.dataTable.ext.buttons.fieldsGroupHold;
    const config = {
        fieldGroup: 'gross_costs',
        activeText: 'Rilascia per nascondere',
        inactiveText: 'Tieni premuto per mostrare',
    };
    holdButton.init(dt, buttonNode, config);
    initializing = false;

    return {
        api: window.ibDatatableFieldsGroups,
        button: holdButton,
        buttonNode,
        config,
        dt,
        window,
        visibility,
        savedVisibility,
        settings,
        getAdjusted: () => adjusted,
    };
}

test('hold-only columns ignore an old visible preference and appear only while pressed', () => {
    const runtime = boot();

    assert.deepEqual(runtime.visibility, [true, false, false]);
    assert.equal(runtime.buttonNode.innerHTML, 'Tieni premuto per mostrare');
    assert.equal(runtime.buttonNode.getAttribute('aria-pressed'), 'false');
    assert.equal(runtime.getAdjusted(), 0);

    runtime.buttonNode.dispatch('pointerdown', { button: 0, pointerId: 7 });
    assert.deepEqual(runtime.visibility, [true, true, true]);
    assert.equal(runtime.settings.oFeatures.bStateSave, false);
    assert.equal(runtime.buttonNode.getAttribute('aria-pressed'), 'true');

    runtime.window.dispatch('pointerup', { pointerId: 7 });
    assert.deepEqual(runtime.visibility, [true, false, false]);
    assert.equal(runtime.settings.oFeatures.bStateSave, true);
    assert.equal(runtime.buttonNode.getAttribute('aria-pressed'), 'false');
    assert.deepEqual(runtime.savedVisibility, []);
    assert.equal(runtime.getAdjusted(), 2);
});

test('cancel, window blur, and keyboard release always hide the group again', () => {
    const runtime = boot([true, false, false]);

    runtime.buttonNode.dispatch('pointerdown', { button: 0, pointerId: 3 });
    runtime.window.dispatch('pointercancel', { pointerId: 3 });
    assert.deepEqual(runtime.visibility, [true, false, false]);

    runtime.buttonNode.dispatch('keydown', { key: ' ', repeat: false, preventDefault() {} });
    assert.deepEqual(runtime.visibility, [true, true, true]);
    runtime.window.dispatch('keyup', { key: ' ', preventDefault() {} });
    assert.deepEqual(runtime.visibility, [true, false, false]);

    runtime.buttonNode.dispatch('pointerdown', { button: 0, pointerId: 4 });
    runtime.window.dispatch('blur');
    assert.deepEqual(runtime.visibility, [true, false, false]);
    assert.deepEqual(runtime.savedVisibility, []);
});

test('hold-only groups cannot be toggled persistently', async () => {
    const runtime = boot();

    assert.deepEqual(Array.from(runtime.api.getGroupNames(runtime.dt)), ['normal']);
    await runtime.api.toggle(runtime.dt, 'gross_costs');
    assert.deepEqual(runtime.visibility, [true, false, false]);
    assert.deepEqual(runtime.savedVisibility, []);
});
