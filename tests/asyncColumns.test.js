const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.asyncColumns.js',
    'utf8'
);

function boot({ columnDefinitions, rowIds })
{
    const ajaxCalls = [];
    const rows = rowIds.map((id) => ({
        id,
        cells: columnDefinitions.map(() => ({
            attrs: {},
            classes: new Set(),
            html: '',
        })),
    }));
    const tableNode = {
        attrs: { 'data-realid': 'orders' },
        getAttribute(name) { return this.attrs[name] || null; },
        id: 'orders',
    };
    const handlers = {};

    class Collection
    {
        constructor(elements = [])
        {
            this.elements = elements;
            this.length = elements.length;
        }

        addClass(className)
        {
            this.elements.forEach((element) => element.classes.add(className));
            return this;
        }

        attr(name, value)
        {
            if (typeof value === 'undefined')
                return this.elements[0] ? this.elements[0].attrs[name] : undefined;

            this.elements.forEach((element) => { element.attrs[name] = String(value); });
            return this;
        }

        each(callback)
        {
            this.elements.forEach((element, index) => callback.call(element, index, element));
            return this;
        }

        empty()
        {
            return this.html('');
        }

        find(selector)
        {
            return new Collection();
        }

        get(index)
        {
            return this.elements[index];
        }

        html(value)
        {
            if (typeof value === 'undefined')
                return this.elements[0] ? this.elements[0].html : undefined;

            this.elements.forEach((element) => { element.html = String(value); });
            return this;
        }

        off()
        {
            return this;
        }

        on(eventName, handler)
        {
            handlers[eventName] = handler;
            return this;
        }

        removeClass(className)
        {
            this.elements.forEach((element) => element.classes.delete(className));
            return this;
        }
    }

    function $(target)
    {
        if (typeof target === 'function')
        {
            target();
            return new Collection();
        }

        if (target === 'table.dataTable' || typeof target === 'undefined')
            return new Collection();

        return new Collection([target]);
    }

    $.fn = { dataTable: {} };
    $.fn.dataTable.Api = function (settings) { return settings.api; };
    $.fn.dataTable.isDataTable = () => true;

    const datatable = {
        cell(rowIndex, columnIndex) {
            return { node: () => rows[rowIndex].cells[columnIndex] };
        },
        columns() {
            return {
                every(callback) {
                    columnDefinitions.forEach((definition, index) => callback.call({
                        getHeaderData(name) { return definition[name]; },
                        index() { return index; },
                    }));
                },
            };
        },
        rows() {
            return {
                every(callback) {
                    rows.forEach((_row, index) => callback.call({ index: () => index }));
                },
            };
        },
        table() {
            return { node: () => tableNode };
        },
    };

    const window = {
        __ibCallAjax(url, type, data, params) {
            ajaxCalls.push({ data, params, type, url });
        },
        ibDtGetRowIdFromRowIndex(_datatable, rowIndex) {
            return rows[rowIndex].id;
        },
        jQuery: $,
    };
    const document = {};

    vm.runInNewContext(moduleSource, {
        Array,
        JSON,
        Number,
        Object,
        Set,
        String,
        TypeError,
        document,
        window,
    });

    return { ajaxCalls, datatable, handlers, rows, tableNode, window };
}

test('the async module is part of the compiled DataTables entrypoint', () => {
    const entrypoint = fs.readFileSync('resources/assets/js/ilBronza.datatables.js', 'utf8');

    assert.match(entrypoint, /require\('\.\/datatables\.vendor\.asyncColumns\.js'\)/);
    assert.match(moduleSource, /window\.ibDtGetRowIdFromRowIndex/);
    assert.match(moduleSource, /window\.__ibCallAjax/);
    assert.doesNotMatch(moduleSource, /\$\.ajax\s*\(/);
});

test('a draw uses existing row ids and the shared ajax helper once per column', () => {
    const runtime = boot({
        columnDefinitions: [
            { asyncRoute: false },
            { asyncCache: 0, asyncRenderer: 'text', asyncRoute: '/first' },
            { asyncCache: 0, asyncRenderer: 'text', asyncRoute: '/second' },
        ],
        rowIds: [10, 20],
    });

    runtime.window.ibResolveAsyncColumns(runtime.datatable);

    assert.equal(runtime.ajaxCalls.length, 2);
    assert.equal(runtime.ajaxCalls[0].url, '/first');
    assert.equal(runtime.ajaxCalls[0].type, 'POST');
    assert.deepEqual(Array.from(runtime.ajaxCalls[0].data.ids), ['10', '20']);
    assert.equal(runtime.ajaxCalls[1].url, '/second');
    assert.equal(runtime.rows[0].cells[1].attrs['aria-busy'], 'true');

    runtime.ajaxCalls[0].params.onSuccess({ data: { 10: '<unsafe>', 20: 'Ready' } });
    runtime.ajaxCalls[0].params.onComplete();

    assert.equal(runtime.rows[0].cells[1].html, '&lt;unsafe&gt;');
    assert.equal(runtime.rows[1].cells[1].html, 'Ready');
    assert.equal(runtime.rows[0].cells[1].attrs['aria-busy'], 'false');
});

test('in-flight calls are deduplicated and caching remains opt-in', () => {
    const runtime = boot({
        columnDefinitions: [
            { asyncCache: 1, asyncRenderer: 'text', asyncRoute: '/cached' },
        ],
        rowIds: [7],
    });

    runtime.window.ibResolveAsyncColumns(runtime.datatable);
    runtime.window.ibResolveAsyncColumns(runtime.datatable);

    assert.equal(runtime.ajaxCalls.length, 1);

    runtime.ajaxCalls[0].params.onSuccess({ 7: 'Cached' });
    runtime.ajaxCalls[0].params.onComplete();
    runtime.rows[0].cells[0].html = '';

    runtime.window.ibResolveAsyncColumns(runtime.datatable);

    assert.equal(runtime.ajaxCalls.length, 1);
    assert.equal(runtime.rows[0].cells[0].html, 'Cached');
});

test('the async PHP field no longer puts row ids into cell markup', () => {
    const fieldSource = fs.readFileSync('src/DatatablesFields/DatatableFieldAsync.php', 'utf8');

    assert.doesNotMatch(fieldSource, /data-async-id/);
    assert.doesNotMatch(fieldSource, /<span/);
    assert.match(fieldSource, /public function transformValue\(\$value\)[\s\S]*?return null;/);
});
