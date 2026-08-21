const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.modelBroadcasts.min.js',
    'utf8'
);

function wait(milliseconds) {
    return new Promise((resolve) => setTimeout(resolve, milliseconds));
}

function boot(options = {}) {
    const handlers = {};
    const echoListeners = {};
    const tables = new Map();
    const ajaxCalls = [];

    const document = {
        getElementById(id) {
            return tables.get(id) || null;
        },
    };

    function $(target) {
        if (target === document) {
            return {
                on(eventName, handler) {
                    handlers[eventName] = handler;
                    return this;
                },
            };
        }

        return {
            DataTable() {
                return target.datatable;
            },
        };
    }

    $.fn = {
        dataTable: {
            isDataTable(table) {
                return !!table.datatable;
            },
        },
    };

    $.ajax = (request) => {
        ajaxCalls.push(request);

        if (options.ajax)
            return options.ajax(request);

        return Promise.resolve({ data: [[request.data.rowId, 'Created']] });
    };

    const window = {
        Echo: {
            private(channel) {
                return {
                    listen(eventName, listener) {
                        echoListeners[channel + ':' + eventName] = listener;
                    },
                };
            },
        },
        Promise,
        Set,
        clearTimeout,
        setTimeout(callback, delay) {
            // Keep animation cleanup from extending the test process lifetime.
            if (delay >= 1000)
                return 0;

            return setTimeout(callback, delay);
        },
    };

    vm.runInNewContext(moduleSource, {
        $,
        Promise,
        Set,
        clearTimeout,
        console,
        document,
        Error,
        Map,
        String,
        window,
        setTimeout,
    });

    function addTable(table) {
        tables.set(table.id, table);
        handlers['init.dt']({}, { nTable: table });
    }

    function emit(channel, payload) {
        echoListeners[channel + ':.crud.model.changed'](payload);
    }

    function emitRefreshTable(channel, payload = {}) {
        echoListeners[channel + ':.refreshTable'](payload);
    }

    return {
        addTable,
        ajaxCalls,
        browserTabId: window.ibGetDatatableBrowserTabId(),
        emit,
        emitRefreshTable,
    };
}

function makeTable({
    channel = 'channel.crud-events.models/order',
    refreshTableChannel = null,
    id = 'orders',
    model = 'App\\Models\\Order',
    serverSide = false,
} = {}) {
    const rows = [];
    const calls = { added: [], addedNodes: [], draws: [], reloads: 0 };
    const table = {
        attributes: {
            'data-model-broadcast-channel': channel,
            'data-model-broadcast-model': model,
            'data-refresh-table-broadcast-channel': refreshTableChannel,
        },
        getAttribute(name) {
            return this.attributes[name] || null;
        },
        id,
    };

    const datatable = {
        ajax: {
            reload(_callback, resetPaging) {
                calls.reloads += 1;
                calls.reloadPaging = resetPaging;
            },
            url() {
                return '/orders';
            },
        },
        draw(resetPaging) {
            calls.draws.push(resetPaging);
            return this;
        },
        row: {
            add(row) {
                calls.added.push(row);
                const classNames = new Set();
                const node = {
                    classList: {
                        add(className) {
                            classNames.add(className);
                        },
                        contains(className) {
                            return classNames.has(className);
                        },
                        remove(className) {
                            classNames.delete(className);
                        },
                    },
                    offsetWidth: 0,
                };
                const addedRow = {
                    draw(resetPaging) {
                        calls.draws.push(resetPaging);
                        return addedRow;
                    },
                    node() {
                        return node;
                    },
                };

                calls.addedNodes.push(node);
                rows.push({ id: row[0], data: row });

                return addedRow;
            },
        },
        rows() {
            return {
                every(callback) {
                    rows.forEach((row) => callback.call({ id: () => row.id }));
                },
            };
        },
        settings() {
            return [{ oFeatures: { bServerSide: serverSide } }];
        },
    };

    table.datatable = datatable;

    return { calls, table };
}

test('reloads a table on its dedicated refresh channel without a model broadcast', () => {
    const runtime = boot();
    const fixture = makeTable({
        channel: null,
        refreshTableChannel: 'channel.tables.orders.refresh',
    });
    runtime.addTable(fixture.table);

    runtime.emitRefreshTable('channel.tables.orders.refresh');

    assert.equal(fixture.calls.reloads, 1);
    assert.equal(fixture.calls.reloadPaging, false);
});

test('shares a refresh channel between its rendered tables', () => {
    const runtime = boot();
    const firstFixture = makeTable({
        channel: null,
        id: 'orders',
        refreshTableChannel: 'channel.tables.orders.refresh',
    });
    const secondFixture = makeTable({
        channel: null,
        id: 'orders-summary',
        refreshTableChannel: 'channel.tables.orders.refresh',
    });
    runtime.addTable(firstFixture.table);
    runtime.addTable(secondFixture.table);

    runtime.emitRefreshTable('channel.tables.orders.refresh');

    assert.equal(firstFixture.calls.reloads, 1);
    assert.equal(secondFixture.calls.reloads, 1);
});

test('fetches and adds a created row with a string key', async () => {
    const runtime = boot();
    const fixture = makeTable();
    runtime.addTable(fixture.table);

    runtime.emit('channel.crud-events.models/order', {
        action: 'created',
        key: 'ORD-42-A',
        model: 'App\\Models\\Order',
    });

    await wait(140);
    await wait(0);

    assert.equal(runtime.ajaxCalls.length, 1);
    assert.equal(runtime.ajaxCalls[0].data.rowId, 'ORD-42-A');
    assert.deepEqual(fixture.calls.added, [['ORD-42-A', 'Created']]);
    assert.deepEqual(fixture.calls.draws, [false]);
    assert.equal(fixture.calls.reloads, 0);
});

test('ignores a created event emitted by the originating table only', async () => {
    const runtime = boot();
    const sourceFixture = makeTable({ id: 'orders' });
    const otherFixture = makeTable({ id: 'orders-summary' });
    runtime.addTable(sourceFixture.table);
    runtime.addTable(otherFixture.table);

    runtime.emit('channel.crud-events.models/order', {
        action: 'created',
        key: 'ORD-SELF',
        model: 'App\\Models\\Order',
        origin: {
            browserTabId: runtime.browserTabId,
            tableId: 'orders',
        },
    });

    await wait(140);
    await wait(0);

    assert.equal(sourceFixture.calls.added.length, 0);
    assert.equal(sourceFixture.calls.reloads, 0);
    assert.equal(otherFixture.calls.added.length, 1);
    assert.equal(otherFixture.calls.reloads, 0);
});

test('adds the mapped row through the client-side DataTables API', async () => {
    const runtime = boot({
        ajax() {
            return Promise.resolve({ data: [[42, 'Mapped order']] });
        },
    });
    const fixture = makeTable();
    runtime.addTable(fixture.table);

    runtime.emit('channel.crud-events.models/order', {
        action: 'created',
        key: 42,
        model: 'App\\Models\\Order',
    });

    await wait(140);

    assert.deepEqual(fixture.calls.added, [[42, 'Mapped order']]);
    assert.deepEqual(fixture.calls.draws, [false]);
    assert.equal(
        fixture.calls.addedNodes[0].classList.contains('ib-datatable-row-broadcast-created'),
        true
    );
    assert.equal(fixture.calls.reloads, 0);
});

test('deduplicates repeated created events while the row fetch is in flight', async () => {
    const runtime = boot({
        ajax() {
            return new Promise(() => {});
        },
    });
    const fixture = makeTable();
    runtime.addTable(fixture.table);

    const payload = {
        action: 'created',
        key: 'external-order-7',
        model: 'App\\Models\\Order',
    };

    runtime.emit('channel.crud-events.models/order', payload);
    runtime.emit('channel.crud-events.models/order', payload);

    await wait(140);
    assert.equal(runtime.ajaxCalls.length, 1);
    assert.equal(fixture.calls.added.length, 0);
});

test('reloads server-side tables without locally adding the created row', async () => {
    const runtime = boot();
    const fixture = makeTable({ serverSide: true });
    runtime.addTable(fixture.table);

    runtime.emit('channel.crud-events.models/order', {
        action: 'created',
        key: 'ORD-99',
        model: 'App\\Models\\Order',
    });
    runtime.emit('channel.crud-events.models/order', {
        action: 'created',
        key: 'ORD-100',
        model: 'App\\Models\\Order',
    });

    await wait(260);

    assert.equal(runtime.ajaxCalls.length, 0);
    assert.equal(fixture.calls.added.length, 0);
    assert.equal(fixture.calls.reloads, 1);
    assert.equal(fixture.calls.reloadPaging, false);
});

test('falls back to a non-destructive reload when the row fetch fails', async () => {
    const runtime = boot({
        ajax() {
            return Promise.reject(new Error('Network error'));
        },
    });
    const fixture = makeTable();
    runtime.addTable(fixture.table);

    runtime.emit('channel.crud-events.models/order', {
        action: 'created',
        key: 'ORD-500',
        model: 'App\\Models\\Order',
    });

    await wait(160);

    assert.equal(fixture.calls.added.length, 0);
    assert.equal(fixture.calls.reloads, 1);
    assert.equal(fixture.calls.reloadPaging, false);
});
