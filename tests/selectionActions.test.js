const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync('resources/assets/js/datatables.vendor.selectionActions.js', 'utf8');

function boot(initialRows = [], deleteColumnIndex = null, ajaxEnabled = true)
{
    const handlers = [];
    const requests = [];
    const notifications = [];
    const confirmations = [];
    const removed = [];
    const action = {
        classes: new Set(['ib-dt-selection-action', 'uk-hidden']),
        disabled: false,
        attributes: deleteColumnIndex === null ? {} : { 'data-delete-column-index': String(deleteColumnIndex) },
        data: {},
    };
    const container = {};
    const document = {};
    let selectedRows = initialRows;
    let reloaded = false;
    let confirmed = true;

    const actionWrapper = {
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
            if (value === undefined)
                return action.attributes[name];

            action.attributes[name] = value;
            return this;
        },
        data(name, value) {
            if (value === undefined)
                return action.data[name];

            action.data[name] = value;
            return this;
        },
    };

    const dt = {
        table() { return { container: () => container, node: () => ({}) }; },
        settings() { return [{ ajax: ajaxEnabled }]; },
        ajax: { reload() { reloaded = true; } },
        rows(selector) {
            if (Array.isArray(selector))
                return { remove() { removed.push(...selector); return { draw() {} }; } };

            const rows = selectedRows;

            return {
                count: () => rows.length,
                data: () => ({ toArray: () => rows }),
                indexes: () => ({ toArray: () => rows.map((_, index) => index) }),
                deselect() { selectedRows = []; },
            };
        },
    };

    const $ = function (node)
    {
        if (node === document)
            return { on(events, handler) { handlers.push({ events, handler }); } };

        if (node === container)
            return {
                find() {
                    return {
                        length: 1,
                        each(handler) { handler.call(action); },
                    };
                },
            };

        if (node === action)
            return actionWrapper;

        if (node === 'meta[name="csrf-token"]')
            return { attr: () => 'test-csrf-token' };

        throw new Error('Unexpected jQuery target');
    };

    $.fn = { dataTable: { Api: function (settings) { return settings; } } };
    $.ajax = options => { requests.push(options); };

    const window = {
        jQuery: $,
        $,
        confirm: message => { confirmations.push(message); return confirmed; },
        addDangerNotification: message => notifications.push({ type: 'error', message }),
        addSuccessNotification: message => notifications.push({ type: 'success', message }),
    };
    const context = { document, setTimeout: () => {}, window, $ };

    vm.runInNewContext(source, context);

    return {
        action,
        dt,
        handlers,
        requests,
        notifications,
        confirmations,
        removed,
        select(rows) { selectedRows = rows; },
        setConfirmed(value) { confirmed = value; },
        wasReloaded: () => reloaded,
        sync: window.ibDtSyncSelectionActionButtons,
        remove: labels => window.ibDtDeleteSelectedRows(dt, action, deleteColumnIndex, labels),
    };
}

const labels = {
    confirm: 'Delete :count rows?',
    error: 'Failed :count rows',
    success: 'Deleted :count rows',
};

test('selection actions stay hidden and disabled until a row is selected', () => {
    const runtime = boot();

    runtime.sync(runtime.dt);

    assert.equal(runtime.action.classes.has('uk-hidden'), true);
    assert.equal(runtime.action.disabled, true);
    assert.equal(runtime.action.attributes['aria-hidden'], 'true');
});

test('selection actions appear as soon as at least one row is selected', () => {
    const runtime = boot();

    runtime.select([['one'], ['two']]);
    runtime.sync(runtime.dt);

    assert.equal(runtime.action.classes.has('uk-hidden'), false);
    assert.equal(runtime.action.disabled, false);
    assert.equal(runtime.action.attributes['aria-hidden'], 'false');
});

test('DataTables select and deselect events resync selection actions', () => {
    const runtime = boot();
    const selectionHandler = runtime.handlers.find(({ events }) => events.includes('select.dt.ibSelectionActions'));

    runtime.select([['one']]);
    selectionHandler.handler({}, runtime.dt);
    assert.equal(runtime.action.classes.has('uk-hidden'), false);

    runtime.select([]);
    selectionHandler.handler({}, runtime.dt);
    assert.equal(runtime.action.classes.has('uk-hidden'), true);
});

test('delete action appears only when every selected row has a delete URL', () => {
    const runtime = boot([['id-1', '/items/1']], 1);

    runtime.sync(runtime.dt);
    assert.equal(runtime.action.classes.has('uk-hidden'), false);

    runtime.select([['id-1', '/items/1'], ['id-2', null]]);
    runtime.sync(runtime.dt);
    assert.equal(runtime.action.classes.has('uk-hidden'), true);
    assert.equal(runtime.action.disabled, true);
});

test('bulk delete confirms once, sends row DELETE requests and refreshes after completion', () => {
    const runtime = boot([['id-1', '/items/1'], ['id-2', ['/items/2', 'name']]], 1);

    runtime.remove(labels);
    runtime.remove(labels);
    assert.equal(runtime.action.disabled, true);
    assert.equal(runtime.requests.length, 1);
    assert.deepEqual(runtime.confirmations, ['Delete 2 rows?']);
    assert.equal(runtime.requests[0].url, '/items/1');
    assert.equal(runtime.requests[0].type, 'DELETE');
    assert.equal(runtime.requests[0].headers['X-CSRF-TOKEN'], 'test-csrf-token');

    runtime.requests[0].success({ success: true });
    runtime.requests[0].complete();
    assert.equal(runtime.requests.length, 2);
    assert.equal(runtime.requests[1].url, '/items/2');

    runtime.requests[1].success({ success: true });
    runtime.requests[1].complete();
    assert.equal(runtime.wasReloaded(), true);
    assert.deepEqual(runtime.notifications, [{ type: 'success', message: 'Deleted 2 rows' }]);
    assert.equal(runtime.action.classes.has('uk-hidden'), true);
});

test('cancelled delete sends no requests', () => {
    const runtime = boot([['id-1', '/items/1']], 1);
    runtime.setConfirmed(false);

    runtime.remove(labels);
    assert.equal(runtime.requests.length, 0);
});

test('failed row delete reports the failure and still refreshes', () => {
    const runtime = boot([['id-1', '/items/1'], ['id-2', '/items/2']], 1);

    runtime.remove(labels);
    runtime.requests[0].success({ success: true });
    runtime.requests[0].complete();
    runtime.requests[1].error();
    runtime.requests[1].complete();

    assert.equal(runtime.wasReloaded(), true);
    assert.deepEqual(runtime.notifications, [{ type: 'error', message: 'Failed 1 rows' }]);
});

test('array tables remove only rows whose delete request succeeded', () => {
    const runtime = boot([['id-1', '/items/1'], ['id-2', '/items/2']], 1, false);

    runtime.remove(labels);
    runtime.requests[0].success({ success: true });
    runtime.requests[0].complete();
    runtime.requests[1].success({ success: false });
    runtime.requests[1].complete();

    assert.equal(runtime.wasReloaded(), false);
    assert.deepEqual(runtime.removed, [0]);
    assert.deepEqual(runtime.notifications, [{ type: 'error', message: 'Failed 1 rows' }]);
});
