const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {spawnSync} = require('node:child_process');
const test = require('node:test');
let JSDOM;
try { ({JSDOM} = require('jsdom')); require.resolve('jquery'); require.resolve('datatables.net'); }
catch (_) { JSDOM = null; }
const domTest = (name, fn) => test(name, {skip: !JSDOM && 'See tests/README.md for DOM dependencies'}, fn);
const extensionTest = (name, module, fn) => {
    let available = false;
    try { require.resolve(module); available = !!JSDOM; } catch (_) {}
    test(name, {skip: !available && 'Install ' + module + ' for this integration test'}, fn);
};
const source = fs.readFileSync('resources/assets/js/datatables.vendor.rowHierarchy.js', 'utf8');
let fixture;
if (JSDOM) {
    const generated = spawnSync(process.env.IB_DT_PHP || 'php', ['-d','error_reporting=8191',
        'tests/fixtures/rowHierarchyFixtures.php.inc', path.resolve(process.env.IB_DT_BOOTSTRAP || '../.ibtest/bootstrap_datatables.php')], {encoding:'utf8'});
    assert.equal(generated.status, 0, generated.error?.message || generated.stderr || generated.stdout);
    fixture = JSON.parse(generated.stdout);
}
const row = (id, parent = null, opened = false, name = id, amount = 1) => [id, {value:parent, row_opened:opened}, name, amount];

function boot(t, data, options = {}) {
    const dom = new JSDOM('<!doctype html><html><body><table id="tree"><thead><tr><th>ID</th><th>Tree</th><th>Name</th><th>Amount</th></tr></thead></table></body></html>',
        {runScripts:'outside-only', pretendToBeVisual:true, url:'https://example.test'});
    const {window} = dom;
    t.after(() => window.close());
    for (const module of ['jquery', 'datatables.net', 'datatables.net-colreorder']) window.eval(fs.readFileSync(require.resolve(module), 'utf8'));
    if (options.keys) window.eval(fs.readFileSync(require.resolve('datatables.net-keytable'),'utf8'));
    if (options.select) window.eval(fs.readFileSync(require.resolve('datatables.net-select'),'utf8'));
    if (options.rowReorder) window.eval(fs.readFileSync(require.resolve('datatables.net-rowreorder'),'utf8'));
    window.eval(source);
    const column = window.eval('(' + fixture.column + ')');
    if (options.flatOnOrder) column.ibDtFlatOnOrder = true;
    const dt = window.jQuery('#tree').DataTable({data, rowId:0, order:[], pageLength:1,
        columnDefs:[{targets:0, visible:false}, {...fixture.options, ...column}], ...options});
    const visible = () => Array.from(window.document.querySelectorAll('#tree tbody > tr'), tr => tr.id).filter(Boolean);
    const click = (id, action = 'children') => window.document.getElementById(String(id)).querySelector('[data-ib-dt-tree-action="' + action + '"]').click();
    return {window, dt, visible, click};
}

domTest('PHP field automatically activates two accessible controls and applies JSON defaults', t => {
    const app = boot(t, [row('a',null,true),row('b','a',true),row('c','b'),row('d',null)]);
    assert.deepEqual(app.visible(), ['a','b','c']);
    const controls = app.window.document.getElementById('a').querySelectorAll('button');
    assert.equal(controls.length,2);
    assert.match(controls[0].innerHTML,/fa-chevron-down/);
    assert.match(controls[1].innerHTML,/fa-angles-down/);
    assert.equal(controls[0].getAttribute('aria-expanded'),'true');
    assert.match(controls[1].title,/\(2\)/);
    assert.equal(app.window.document.getElementById('c').querySelectorAll('button').length,0);
    assert.equal(app.window.document.getElementById('c').dataset.ibDtTreeDepth,'2');
    assert.match(app.window.document.querySelector('.dt-info').textContent,/di 2 rami/);
    assert.equal(app.dt.page.info().pages,2);
});

domTest('single toggle preserves descendant choices; double toggle explicitly opens or closes the entire branch', t => {
    const app = boot(t, [row('a'),row('b','a'),row('c','b'),row('x','a')]);
    app.click('a'); assert.deepEqual(app.visible(),['a','b','x']);
    app.click('b'); assert.deepEqual(app.visible(),['a','b','c','x']);
    app.click('a'); assert.deepEqual(app.visible(),['a']);
    app.click('a'); assert.deepEqual(app.visible(),['a','b','c','x']);
    app.click('a','branch'); assert.deepEqual(app.visible(),['a']);
    app.click('a'); assert.deepEqual(app.visible(),['a','b','x']);
    app.click('a','branch'); assert.deepEqual(app.visible(),['a','b','c','x']);
    assert.equal(app.window.document.activeElement.dataset.ibDtTreeAction,'branch');
});

domTest('paging counts roots and native row/column/cell page selectors include only the visible complete branch', t => {
    const app = boot(t, [row('a',null,true),row('a1','a',true),row('a2','a1'),row('b',null,true),row('b1','b')]);
    const {dt} = app;
    assert.deepEqual(Array.from(dt.rows({page:'current'}).ids()),['a','a1','a2']);
    assert.deepEqual(Array.from(dt.rows((idx,data) => data[0] !== 'a1',{page:'current'}).ids()),['a','a2']);
    assert.equal(dt.column(3,{page:'current'}).data().toArray().length,3);
    assert.equal(dt.cells(null,3,{page:'current'}).data().toArray().length,3);
    assert.equal(dt.rows({search:'applied'}).count(),5);
    dt.page('next').draw('page');
    assert.deepEqual(app.visible(),['b','b1']);
    assert.deepEqual(Array.from(dt.rows({page:'current'}).ids()),['b','b1']);
    assert.equal(dt.rows('#a',{page:'current'}).count(),0);
    assert.equal(dt.page.info().page,1);
    app.click('b');
    assert.deepEqual(app.visible(),['b']);
    assert.equal(dt.rows({search:'applied'}).count(),5);
    assert.equal(dt.column(3,{search:'applied'}).data().toArray().reduce((a,b) => a+b,0),5);
});

domTest('sorting is between siblings, and search shows ancestor paths while preserving collapsed state and raw matches', t => {
    const app = boot(t, [row('a',null,false,'Z root'),row('a1','a',false,'B child'),row('a2','a',false,'A child'),
        row('needle','a1',false,'Find me'),row('b',null,false,'A root')], {pageLength:10});
    const {dt} = app;
    dt.order([2,'asc']).draw();
    assert.deepEqual(app.visible(),['b','a']);
    app.click('a','branch');
    assert.deepEqual(app.visible(),['b','a','a2','a1','needle']);
    app.click('a','branch');
    dt.search('Find me').draw();
    assert.deepEqual(app.visible(),['a','a1','needle']);
    assert.deepEqual(Array.from(dt.rows({search:'applied'}).ids()),['needle']);
    assert.equal(app.window.document.getElementById('a').classList.contains('ib-dt-tree-context'),true);
    dt.search('').draw();
    assert.deepEqual(app.visible(),['b','a']);
});

domTest('row refresh, AJAX reload, removals and new defaults retain choices by stable row ID', t => {
    let payload = [row('a'),row('a1','a'),row('b')];
    const app = boot(t, [], {ajax:(_request, callback) => callback({data:payload}), pageLength:10});
    app.click('a');
    app.dt.row('#a').data(row('a',null,false,'Updated')).draw(false);
    assert.deepEqual(app.visible(),['a','a1','b']);
    payload = [row('b'),row('a'),row('a1','a'),row('new',null,true),row('new1','new')];
    app.dt.ajax.reload(null,false);
    assert.deepEqual(app.visible(),['b','a','a1','new','new1']);
    app.dt.row('#a').remove().draw(false);
    assert.ok(app.visible().includes('a1'));
    assert.match(app.window.document.getElementById('a1').textContent,/⚠/);
});

domTest('hidden and reordered columns retain hierarchy controls and IDs', t => {
    const app = boot(t,[row('a'),row('b','a'),row('c','b')],{colReorder:true,pageLength:10});
    app.dt.colReorder.order([3,2,0,1]);
    app.dt.column(1).visible(false);
    app.click('a','branch');
    assert.deepEqual(app.visible(),['a','b','c']);
    assert.deepEqual(Array.from(app.dt.rows({page:'current'}).ids()),['a','b','c']);
    app.dt.draw(false);
    assert.deepEqual(app.visible(),['a','b','c']);
});

domTest('plain tables retain native selectors and hierarchy destroy/recreate does not retain old defaults', t => {
    const app = boot(t,[row('a'),row('b','a')],{pageLength:10});
    app.click('a'); app.dt.destroy();
    assert.equal(app.window.document.querySelector('#tree').classList.contains('ib-dt-hierarchy'),false);
    const dt = app.window.jQuery('#tree').DataTable({data:[row('x'),row('y')],rowId:0,order:[],pageLength:1});
    assert.equal(dt.rows({page:'current'}).count(),1);
    assert.equal(dt.rows((idx,data) => data[0] === 'y').count(),1);
    assert.equal(dt.column(3).data().toArray().length,2);
    dt.page('next').draw('page');
    assert.deepEqual(Array.from(dt.rows({page:'current'}).ids()),['y']);
});

domTest('cancelled draw callbacks leave the native dataset and page intact', t => {
    let cancel = false;
    const app = boot(t,[row('a'),row('b','a')],{preDrawCallback:() => !cancel});
    cancel = true; app.dt.draw(false);
    assert.deepEqual(app.visible(),['a']);
    assert.equal(app.dt.rows({search:'applied'}).count(),2);
    cancel = false; app.click('a');
    assert.deepEqual(app.visible(),['a','b']);
});

domTest('incremental patches recreate controls and reparent rows while preserving focused dirty editor drafts', t => {
    const app = boot(t,[row('a',null,true),row('b','a'),row('c',null,true)],{pageLength:10});
    app.window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.rowPatch.js','utf8'));
    const input = app.window.document.createElement('input');
    input.className = 'ib-editor-text editorchanged'; input.value = 'Unsaved draft';
    app.dt.cell(app.dt.row('#b').index(),2).node().replaceChildren(input);
    input.focus(); input.setSelectionRange(2,5);
    const patch = app.window.ibDtPatchRenderedRow;
    patch(app.dt, app.dt.row('#a'), row('a',null,true,'Updated'));
    assert.ok(app.window.document.getElementById('a').querySelector('[data-ib-dt-tree-action="children"]'));
    patch(app.dt, app.dt.row('#b'), row('b','c',false,'Server value'));
    assert.deepEqual(app.visible(),['a','c','b']);
    assert.equal(input.value,'Unsaved draft');
    assert.equal(app.window.document.activeElement,input);
    assert.equal(input.selectionStart,2); assert.equal(input.selectionEnd,5);
    assert.equal(app.dt.row('#b').data()[2],'b');
});

domTest('native DataTables child rows stay attached only while their owning data row is visible', t => {
    const app = boot(t,[row('a',null,true),row('b','a')]);
    app.dt.row('#b').child('<input value="Inline edit">').show();
    assert.equal(app.window.document.querySelectorAll('tbody tr').length,3);
    app.click('a');
    assert.equal(app.window.document.querySelectorAll('tbody tr').length,1);
    app.click('a');
    assert.equal(app.window.document.querySelectorAll('tbody tr').length,3);
});

domTest('changing page length, shrinking a dataset and empty search keep root paging valid', t => {
    const app = boot(t,[row('a'),row('b'),row('c'),row('d')]);
    app.dt.page('last').draw('page');
    assert.deepEqual(app.visible(),['d']);
    app.dt.rows(['#b','#c','#d']).remove().draw(false);
    assert.deepEqual(app.visible(),['a']); assert.equal(app.dt.page.info().page,0);
    app.dt.search('No match').draw();
    assert.deepEqual(app.visible(),[]); assert.equal(app.dt.rows({page:'current'}).count(),0);
    app.dt.search('').page.len(-1).draw();
    assert.deepEqual(app.visible(),['a']);
});

extensionTest('Select preserves hidden selections and selecting the current page includes visible descendants', 'datatables.net-select', t => {
    const app = boot(t,[row('a',null,true),row('b','a'),row('c',null)],{select:{style:'multi'}});
    app.dt.rows({page:'current'}).select();
    assert.equal(app.dt.rows({selected:true}).count(),2);
    app.click('a');
    assert.deepEqual(app.visible(),['a']);
    assert.equal(app.dt.rows({selected:true}).count(),2);
    assert.equal(app.dt.rows({selected:true,page:'current'}).count(),1);
    app.dt.rows().deselect(); app.click('a');
    assert.equal(app.dt.rows({selected:true}).count(),0);
});

extensionTest('automatic hierarchy disables RowReorder before its init handler', 'datatables.net-rowreorder', t => {
    const app = boot(t,[row('a'),row('b','a')],{rowReorder:true});
    assert.equal(app.dt.settings()[0].rowreorder.c.enable,false);
    app.click('a'); assert.deepEqual(app.visible(),['a','b']);
});


function installHeaderSort(app) {
    const source = fs.readFileSync(process.env.IB_HIERARCHY_MAIN || 'resources/assets/js/datatables.vendor.main.min.js', 'utf8');
    const start = source.indexOf('    function drawPreservingHorizontalScroll(table)');
    const end = source.indexOf('    function toggleSort(', start);
    assert.ok(start >= 0 && end > start);
    app.window.eval(source.slice(start, end) + '\nwindow.drawHeaderSort = drawPreservingHorizontalScroll;');
}

extensionTest('header sorting releases stale KeyTable focus on a hierarchical page', 'datatables.net-keytable', t => {
    const app = boot(t, [row('a', null, true, 'Root'), row('b', 'a', false, 'B child'),
        row('c', 'a', false, 'A child')], {keys:true, pageLength:10});
    installHeaderSort(app);
    app.dt.cell('#b', 2).focus();
    assert.ok(app.dt.settings()[0].keytable.s.lastFocus);
    app.window.drawHeaderSort(app.dt.order([2, 'asc']));
    assert.equal(app.dt.settings()[0].keytable.s.lastFocus, null);
    assert.equal(app.dt.keys.enabled(), true);
    assert.deepEqual(app.visible(), ['a', 'c', 'b']);
});

extensionTest('header sorting preserves KeyTable behavior on a plain table', 'datatables.net-keytable', t => {
    const app = boot(t, [row('a', null, false, 'A row'), row('b', null, false, 'B row')],
        {keys:true, pageLength:10, columnDefs:[{targets:0,visible:false}]});
    installHeaderSort(app);
    app.dt.cell('#a', 2).focus();
    app.window.drawHeaderSort(app.dt.order([2, 'desc']));
    assert.ok(app.dt.settings()[0].keytable.s.lastFocus);
    assert.equal(app.dt.keys.enabled(), true);
});


domTest('global header order excludes virtual groups on the first click and restores the opened tree', async t => {
    const group = (id, parent, opened) => {
        const item = row(id, parent, opened);
        item[1].row_group = true;
        return item;
    };
    const app = boot(t, [group('root', null, true), group('group', 'root', false),
        row('z', 'group', false, 'Zulu'), row('a', 'root', false, 'Alpha'),
        row('m', 'group', false, 'Mike')], {flatOnOrder:true, order:[[1,'asc']], pageLength:2});
    app.click('group');
    const tree = app.visible();
    const header = app.dt.column(2).header();
    const sort = async () => {
        const drawn = new Promise(resolve => app.dt.one('draw', () => resolve()));
        header.click();
        await drawn;
    };
    await sort();
    assert.deepEqual(app.visible(), ['a','m']);
    assert.equal(app.dt.page.info().recordsDisplay, 3);
    assert.equal(app.dt.page.info().recordsTotal, 3);
    assert.equal(app.dt.rows({search:'applied'}).count(), 3);
    assert.equal(app.window.document.querySelectorAll('tbody .ib-dt-tree-controls').length, 0);
    app.dt.page('next').draw('page');
    assert.deepEqual(app.visible(), ['z']);
    await sort();
    assert.deepEqual(app.visible(), ['z','m']);
    app.dt.search('Mike').draw();
    assert.deepEqual(app.visible(), ['m']);
    app.window.ibDtRowHierarchy.restoreOrder(app.dt);
    assert.deepEqual(app.visible(), ['root','group','m']);
    app.dt.search('').draw();
    assert.deepEqual(app.visible(), tree);
    assert.equal(app.dt.page.info().recordsTotal, 1);
});

domTest('header toggle expands and collapses all descendants across pages and follows individual row choices', t => {
    const app = boot(t, [row('a',null,true),row('a1','a'),row('a2','a1'),row('b'),row('b1','b')]);
    const header = () => app.dt.column(1).header().querySelector('.ib-dt-tree-toggle-all');
    assert.equal(header().getAttribute('aria-label'),'Espandi tutto');
    assert.match(header().innerHTML,/fa-angles-right/);
    const order = JSON.stringify(app.dt.order());
    header().click();
    assert.deepEqual(app.visible(),['a','a1','a2']);
    assert.equal(JSON.stringify(app.dt.order()),order);
    assert.equal(header().getAttribute('aria-expanded'),'true');
    assert.equal(header().title,'Comprimi tutto');
    assert.match(header().innerHTML,/fa-angles-down/);
    assert.equal(app.window.document.activeElement,header());
    app.dt.page('next').draw('page');
    assert.deepEqual(app.visible(),['b','b1']);
    header().click();
    assert.deepEqual(app.visible(),['b']);
    assert.equal(app.dt.page.info().page,1);
    app.dt.page('first').draw('page');
    assert.deepEqual(app.visible(),['a']);
    app.click('a');
    assert.deepEqual(app.visible(),['a','a1']);
    assert.equal(header().title,'Espandi tutto');
    header().click();
    app.click('a1');
    assert.equal(header().title,'Espandi tutto');
    app.click('a1');
    assert.equal(header().title,'Comprimi tutto');
    assert.equal(app.dt.rows({search:'applied'}).count(),5);
});

domTest('header toggle preserves global choices over AJAX reload and disables itself during search or with no branches', t => {
    let payload = [row('a'),row('a1','a'),row('b'),row('b1','b')];
    const app = boot(t,[],{ajax:(_request, callback) => callback({data:payload}),pageLength:10});
    const header = () => app.dt.column(1).header().querySelector('.ib-dt-tree-toggle-all');
    header().click();
    payload = [row('b'),row('b1','b'),row('a'),row('a1','a')];
    app.dt.ajax.reload(null,false);
    assert.deepEqual(app.visible(),['b','b1','a','a1']);
    assert.equal(header().title,'Comprimi tutto');
    app.dt.search('a1').draw();
    assert.equal(header().disabled,true);
    header().click();
    app.dt.search('').draw();
    assert.deepEqual(app.visible(),['b','b1','a','a1']);
    assert.equal(header().disabled,false);
    payload = [row('leaf')];
    app.dt.ajax.reload(null,false);
    assert.equal(header().disabled,true);
    assert.equal(header().title,'Nessun ramo da espandere');
    app.dt.clear().draw();
    assert.equal(header().disabled,true);
});

domTest('scrolling and cloned title controls remain scoped to their table after column reorder and destroy', t => {
    const app = boot(t,[row('a'),row('a1','a'),row('b')],{scrollX:true,colReorder:true,pageLength:10});
    const other = app.window.document.createElement('table');
    other.id = 'other'; other.innerHTML = '<thead><tr><th>ID</th><th>Tree</th><th>Name</th><th>Amount</th></tr></thead>';
    app.window.document.body.appendChild(other);
    const definition = app.window.eval('(' + fixture.column + ')');
    const second = app.window.jQuery(other).DataTable({data:[row('x'),row('x1','x')],rowId:0,order:[],pageLength:10,
        columnDefs:[{targets:0,visible:false},{...fixture.options,...definition}]});
    app.dt.colReorder.order([3,2,0,1]);
    app.dt.column(3).visible(false); app.dt.draw(false); app.dt.column(3).visible(true);
    const header = app.dt.column(3).header();
    const floating = app.window.document.createElement('table');
    floating.innerHTML = '<thead><tr></tr></thead>';
    floating.querySelector('tr').appendChild(header.cloneNode(true));
    app.window.document.body.appendChild(floating);
    floating.querySelector('button').click();
    assert.deepEqual(app.visible(),['a','a1','b']);
    assert.equal(floating.querySelector('button').title,'Comprimi tutto');
    assert.equal(second.column(1).header().querySelector('button').title,'Espandi tutto');
    assert.equal(other.querySelectorAll('tbody > tr').length,1);
    for (let i=0;i<3;i++) app.dt.draw(false);
    for (const th of app.window.document.querySelectorAll('th')) {
        assert.ok(th.querySelectorAll('.ib-dt-tree-toggle-all').length <= 1);
    }
    app.dt.column(3).visible(false);
    app.dt.destroy();
    assert.equal(floating.querySelector('button'),null);
    assert.equal(header.querySelector('button'),null);
    assert.equal(app.window.document.querySelectorAll('[data-ib-dt-tree-table-id="tree"]').length,0);
    assert.equal(other.querySelectorAll('thead .ib-dt-tree-toggle-all').length,1);
});

domTest('a header toggle is disabled in flat order and restored with the tree', t => {
    const app = boot(t,[row('a'),row('a1','a')],{flatOnOrder:true,order:[[1,'asc']],pageLength:10});
    const header = () => app.dt.column(1).header().querySelector('.ib-dt-tree-toggle-all');
    app.dt.order([2,'asc']).draw();
    assert.equal(header().disabled,true);
    app.window.ibDtRowHierarchy.restoreOrder(app.dt);
    assert.equal(header().disabled,false);
    header().click();
    assert.deepEqual(app.visible(),['a','a1']);
});

extensionTest('header collapse clears stale KeyTable focus while keeping keyboard navigation enabled', 'datatables.net-keytable', t => {
    const app = boot(t,[row('a',null,true),row('a1','a')],{keys:true,pageLength:10});
    app.dt.cell('#a1',2).focus();
    app.dt.column(1).header().querySelector('.ib-dt-tree-toggle-all').click();
    assert.deepEqual(app.visible(),['a']);
    assert.equal(app.dt.settings()[0].keytable.s.lastFocus,null);
    assert.equal(app.dt.keys.enabled(),true);
});
