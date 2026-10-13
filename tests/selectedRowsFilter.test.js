const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync('resources/assets/js/datatables.vendor.filtering.min.js', 'utf8');

function boot()
{
    const document = {};
    const searches = [];
    let apiCreations = 0;

    function $(node)
    {
        if (node === document)
            return { ready() {} };

        return { data: key => node.data[key] };
    }

    function Api(settings)
    {
        apiCreations++;
        this.getDataAttribute = key => settings.nTable.data[key];
        this.row = index => ({ selected: () => settings.selected.has(index) });
    }

    Api.register = () => {};
    $.fn = { dataTable: { Api, ext: { search: searches } } };
    vm.runInNewContext(source, { $, document, window: {} });

    return {
        matches: (settings, index) => searches.every(fn => fn(settings, [], index, [])),
        apiCreations: () => apiCreations,
    };
}

function table(selected = [], showSelected)
{
    return {
        nTable: { data: { showSelected }, getAttribute: () => 'test-table' },
        selected: new Set(selected),
    };
}

test('inactive selection filtering accepts all rows without constructing table APIs', () => {
    const runtime = boot();
    const settings = table([1]);

    for (const state of [undefined, false]) {
        settings.nTable.data.showSelected = state;
        for (let i = 0; i < 3405; i++)
            assert.equal(runtime.matches(settings, i), true);
    }

    assert.equal(runtime.apiCreations(), 0);
});

test('active filtering includes selected row indexes on any page and reuses the API', () => {
    const runtime = boot();
    const settings = table([1, 250], true);

    for (let i = 0; i < 3405; i++)
        assert.equal(runtime.matches(settings, i), i === 1 || i === 250);

    assert.equal(runtime.apiCreations(), 1);
});

test('selection changes are visible immediately through the cached API', () => {
    const runtime = boot();
    const settings = table([1], true);

    assert.equal(runtime.matches(settings, 1), true);
    settings.selected.delete(1);
    settings.selected.add(2);
    assert.equal(runtime.matches(settings, 1), false);
    assert.equal(runtime.matches(settings, 2), true);
    settings.selected.clear();
    assert.equal(runtime.matches(settings, 2), false);
});

test('toggling the filter off and on reads its current state', () => {
    const runtime = boot();
    const settings = table([1], true);

    assert.equal(runtime.matches(settings, 2), false);
    settings.nTable.data.showSelected = false;
    assert.equal(runtime.matches(settings, 2), true);
    settings.nTable.data.showSelected = true;
    assert.equal(runtime.matches(settings, 2), false);
    assert.equal(runtime.apiCreations(), 1);
});

test('tables keep independent filter and selection state', () => {
    const runtime = boot();
    const first = table([1], true);
    const second = table([2], true);

    assert.equal(runtime.matches(first, 1), true);
    assert.equal(runtime.matches(second, 1), false);
    assert.equal(runtime.matches(first, 2), false);
    assert.equal(runtime.matches(second, 2), true);
    second.nTable.data.showSelected = false;
    assert.equal(runtime.matches(second, 1), true);
    assert.equal(runtime.matches(first, 2), false);
    assert.equal(runtime.apiCreations(), 2);
});

test('reinitializing the same table node uses the new DataTables settings', () => {
    const runtime = boot();
    const previous = table([1], true);
    assert.equal(runtime.matches(previous, 1), true);

    const current = { nTable: previous.nTable, selected: new Set([2]) };
    assert.equal(runtime.matches(current, 1), false);
    assert.equal(runtime.matches(current, 2), true);
    assert.equal(runtime.apiCreations(), 2);
});
