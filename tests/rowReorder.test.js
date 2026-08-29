const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.main.min.js',
    'utf8'
);

function hasRowReorderChanges(diff)
{
    const start = moduleSource.indexOf('function __ibDatatableHasRowReorderChanges');
    const end = moduleSource.indexOf('//TODO TODO TODO SISTEMARE', start);

    assert.notEqual(start, -1);
    assert.notEqual(end, -1);

    const source = moduleSource.slice(start, end) +
        '\nglobalThis.hasRowReorderChanges = __ibDatatableHasRowReorderChanges;';
    const context = { Array };

    vm.runInNewContext(source, context);

    return context.hasRowReorderChanges(diff);
}

test('ignores row-reorder events without a changed row', () => {
    assert.equal(hasRowReorderChanges(undefined), false);
    assert.equal(hasRowReorderChanges([]), false);
});

test('processes row-reorder events with changed rows', () => {
    assert.equal(hasRowReorderChanges([{ node: {} }]), true);
});
