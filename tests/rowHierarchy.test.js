const assert = require('node:assert/strict');
const test = require('node:test');
const tree = require('../resources/assets/js/datatables.vendor.rowHierarchy.js');
const entry = (index, id, value, row_opened = false) => ({index, id, cell: {value, row_opened}});

test('arbitrary depth is iterative, with direct and complete branch controls', () => {
    const entries = Array.from({length: 20000}, (_, index) => entry(index, index, index ? index - 1 : null));
    const opened = new Map(), matches = new Set(entries.map(row => row.index));
    let graph = tree.build(entries, opened, matches);
    assert.deepEqual(tree.page(graph, 0, 1), [0]);
    assert.equal(graph.nodes.get(0).total, 19999);
    tree.toggle(graph, opened, 0, true);
    graph = tree.build(entries, opened, matches);
    assert.equal(tree.page(graph, 0, 1).length, 20000);
    tree.toggle(graph, opened, 0, false);
    graph = tree.build(entries, opened, matches);
    assert.deepEqual(tree.page(graph, 0, 1), [0]);
    tree.toggle(graph, opened, 0, false);
    graph = tree.build(entries, opened, matches);
    assert.equal(tree.page(graph, 0, 1).length, 20000);
    tree.toggle(graph, opened, 0, true);
    graph = tree.build(entries, opened, matches);
    assert.deepEqual(tree.page(graph, 0, 1), [0]);
    tree.toggle(graph, opened, 0, false);
    assert.deepEqual(tree.page(tree.build(entries, opened, matches), 0, 1), [0, 1]);
});

test('cycles, orphans, duplicate IDs and missing IDs never lose rows', () => {
    const entries = [entry(0,'a','b'), entry(1,'b','a'), entry(2,'child','a'),
        entry(3,'orphan','absent'), entry(4,'dup',null), entry(5,'dup',null),
        entry(6,'ambiguous','dup'), entry(7,null,null), entry(8,'self','self')];
    const matches = new Set(entries.map(row => row.index));
    const opened = new Map();
    let graph = tree.build(entries, opened, matches);
    for (const node of graph.roots) tree.toggle(graph, opened, node.index, true);
    graph = tree.build(entries, opened, matches);
    assert.equal(new Set(tree.page(graph, 0, -1)).size, entries.length);
    assert.equal(graph.nodes.get(0).issue, 'cycle');
    assert.equal(graph.nodes.get(2).parent.id, 'a');
    assert.equal(graph.nodes.get(3).issue, 'orphan');
    assert.equal(graph.nodes.get(4).issue, 'duplicate');
    assert.equal(graph.nodes.get(6).issue, 'orphan');
    assert.equal(graph.nodes.get(7).issue, 'missingId');
    assert.equal(graph.nodes.get(8).issue, 'cycle');
});

test('search exposes ancestors without mutating defaults or user state', () => {
    const entries = [entry(0,'order:1',null), entry(1,'product:1','order:1'), entry(2,0,'product:1'), entry(3,'001',null)];
    const opened = new Map();
    const graph = tree.build(entries, opened, new Set([2]), true);
    assert.deepEqual(tree.page(graph, 0, 1), [0,1,2]);
    tree.toggle(graph, opened, 0, true);
    assert.equal(opened.size, 0);
    assert.deepEqual(tree.page(tree.build(entries, opened, new Set([0,1,2,3])), 0, -1), [0,3]);
});
