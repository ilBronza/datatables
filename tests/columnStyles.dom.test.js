// Optional DOM integration tests. Run with jsdom, jquery@3 and datatables.net in NODE_PATH.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const engine = require('../resources/assets/js/datatables.vendor.columnStyles.js');
let JSDOM;
try { ({JSDOM} = require('jsdom')); require.resolve('jquery'); require.resolve('datatables.net'); }
catch (_) { JSDOM = null; }
const domTest = (name, fn) => test(name, {skip: !JSDOM && 'Install temporary DOM test dependencies; see tests/README.md'}, fn);
const labels = {title:'Column style', columns:'Columns', allRows:'All rows', target:'Element', cell:'Entire cell', text:'Text', link:'Link',
    icon:'Icon', image:'Image', button:'Button', input:'Input', badge:'Label', savedElement:'Saved element', notOnPage:'Not on page',
    original:'Default', custom:'Reset', resetProperty:'Reset property', resetElement:'Reset element', noChanges:'No changes', unsaved:'Unsaved',
    saving:'Saving', saved:'Saved', failed:'Failed', discovering:'Finding elements', cancel:'Cancel', save:'Save'};

function boot(t, saved = {}, tableOptions = {}) {
    const dom = new JSDOM('<!doctype html><html><head></head><body></body></html>', {runScripts:'outside-only', pretendToBeVisual:true, url:'https://example.test'});
    const {window} = dom;
    t.after(() => window.close());
    window.eval(fs.readFileSync(require.resolve('jquery'), 'utf8'));
    window.eval(fs.readFileSync(require.resolve('datatables.net'), 'utf8'));
    const $ = window.jQuery;
    const tables = {};
    ['orders','invoices'].forEach(id => {
        const wrapper = window.document.createElement('div');
        wrapper.innerHTML = '<div uk-offcanvas><div class="uk-offcanvas-bar"><ul class="toggle-vis-container"><li class="ib-colvis-item">'
            + '<a class="toggle-vis" data-column="0" data-style-column="customer">Customer</a><button class="ib-colvis-gear">Style</button></li></ul></div></div>'
            + '<table id="' + id + '"><thead><tr><th>Customer</th><th>Amount</th></tr></thead><tbody><tr><td><a href="/customer"><i class="fa-solid fa-user"></i><span data-style-target="name" data-style-label="Customer name" style="font-weight:500">Acme</span></a></td><td>10</td></tr></tbody></table>';
        window.document.body.appendChild(wrapper);
        const list = wrapper.querySelector('ul');
        list.dataset.tableid = id; list.dataset.settingsStoreUrl = '/settings/' + id;
        list.dataset.styleLabels = JSON.stringify(labels); list.dataset.styleFonts = JSON.stringify({Arial:'Arial, sans-serif'});
        tables[id] = $('#' + id).DataTable({paging:false, searching:false, info:false, ordering:false, columns:[{className:'customer'},{className:'amount'}], ...tableOptions});
    });
    window.__ibColumnStyleDraftByTable = {orders:{customer:{selectors:saved}}};
    let walks = 0;
    const walker = window.document.createTreeWalker.bind(window.document);
    window.document.createTreeWalker = (...args) => {
        // jsdom's selector engine also creates walkers; count only column discovery.
        if (args[0].tagName === 'TD' && args[1] === window.NodeFilter.SHOW_ELEMENT) walks++;
        return walker(...args);
    };
    const requests = [];
    $.ajax = options => { const deferred = $.Deferred(); requests.push({options, deferred}); return deferred.promise(); };
    window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.columnStyles.js','utf8'));
    function open(id='orders') { window.document.querySelector('ul[data-tableid="'+id+'"] .ib-colvis-gear').click(); }
    const panel = () => window.document.querySelector('.ib-column-style-editor');
    const sheet = (id='orders') => window.document.querySelector('style[data-table-id="'+id+'"]');
    function input(property, value) {
        const field = panel().querySelector('[data-property="'+property+'"]'); field.value=value;
        field.dispatchEvent(new window.Event(field.tagName === 'SELECT' ? 'change' : 'input', {bubbles:true}));
    }
    function target(value) {
        const select = panel().querySelector('.ib-style-target'); select.value=value;
        select.dispatchEvent(new window.Event('change',{bubbles:true}));
    }
    return {window, $, tables, requests, open, panel, sheet, input, target, walks:() => walks};
}

domTest('no editor or traversal before opening; semantic child names and one form', t => {
    const app = boot(t);
    assert.equal(app.walks(),0); assert.equal(app.panel(),null);
    app.open();
    const options = Array.from(app.panel().querySelector('select').options);
    assert.ok(options.some(option => option.text === 'Customer name'));
    assert.equal(app.window.document.querySelectorAll('.ib-column-style-editor').length,1);
    app.target('td [data-style-target="name"]');
    assert.equal(app.panel().querySelector('[data-property="width"]'),null);
    assert.ok(app.panel().querySelector('[data-property="font-size"]'));
});

domTest('cancel restores saved stylesheet and preserves renderer inline styles', t => {
    const saved = {td:{color:'#112233'}};
    const app = boot(t,saved); app.open();
    app.input('color','#aa0000');
    assert.ok(app.sheet().textContent.includes('#aa0000'));
    assert.equal(app.window.getComputedStyle(app.window.document.querySelector('#orders td.customer')).color,'rgb(170, 0, 0)');
    assert.notEqual(app.window.getComputedStyle(app.window.document.querySelector('#invoices td.customer')).color,'rgb(170, 0, 0)');
    app.panel().querySelector('.ib-style-cancel').click();
    assert.equal(app.sheet().textContent,engine.css('orders','customer',saved));
    assert.equal(app.window.getComputedStyle(app.window.document.querySelector('#orders td.customer')).color,'rgb(17, 34, 51)');
    assert.equal(app.window.document.querySelector('[data-style-target="name"]').style.fontWeight,'500');
    assert.equal(app.panel(),null);
});

domTest('save targets only the selected column and a reopen uses the acknowledged state', t => {
    const app=boot(t); app.open(); app.target('td [data-style-target="name"]'); app.input('font-size','20');
    assert.equal(app.window.getComputedStyle(app.window.document.querySelector('#orders [data-style-target]')).fontSize,'20px');
    app.panel().querySelector('.ib-style-save').click();
    assert.equal(app.requests.length,1);
    const request=app.requests[0], payload=JSON.parse(request.options.data.settings_json);
    assert.equal(request.options.url,'/settings/orders');
    assert.equal(payload.columnName,'customer'); assert.equal(payload.selectors['td [data-style-target="name"]']['font-size'],'20px');
    request.deferred.resolve({success:true});
    app.panel().querySelector('.ib-style-back').click(); app.open(); app.target('td [data-style-target="name"]');
    assert.equal(app.panel().querySelector('[data-property="font-size"]').value,'20');
    app.open('invoices'); assert.equal(app.sheet('invoices').textContent,'');
    assert.ok(app.sheet('orders').textContent.includes('20px'));
});

domTest('reset removes custom CSS; failed saves keep the draft for retry', t => {
    const app=boot(t,{td:{width:'240px',color:'#112233'}}); app.open();
    app.panel().querySelector('.ib-style-reset').click(); assert.equal(app.sheet().textContent,'');
    app.panel().querySelector('.ib-style-save').click(); app.requests[0].deferred.reject();
    assert.equal(app.panel().querySelector('footer p').textContent,'Failed');
    assert.equal(app.panel().querySelector('.ib-style-save').disabled,false);
    app.panel().querySelector('.ib-style-save').click();
    assert.deepEqual(JSON.parse(app.requests[1].options.data.settings_json).selectors,{});
    app.requests[1].deferred.resolve({success:true,selectors:{}});
    app.panel().querySelector('.ib-style-back').click(); app.open(); assert.equal(app.sheet().textContent,'');
});

domTest('draws discover new elements while styles remain scoped and opening another table cancels a draft', t => {
    const app=boot(t); app.open(); app.input('color','#aa0000');
    app.tables.orders.row.add(['<span data-style-target="note" data-style-label="Note">New</span>','25']).draw();
    assert.ok(Array.from(app.panel().querySelector('select').options).some(option=>option.text==='Note'));
    app.open('invoices'); assert.equal(app.sheet('orders').textContent,'');
    assert.equal(app.window.document.querySelectorAll('.ib-column-style-editor').length,1);
});

domTest('closing the offcanvas cancels a draft; a pending save blocks closing', t => {
    const app=boot(t); app.open(); app.input('color','#aa0000');
    const canvas=app.panel().closest('[uk-offcanvas]');
    app.panel().querySelector('.ib-style-save').click();
    const event=app.$.Event('beforehide'); app.$(canvas).trigger(event);
    assert.equal(event.isDefaultPrevented(),true); assert.ok(app.panel());
    app.requests[0].deferred.reject(); app.$(canvas).trigger('beforehide');
    assert.equal(app.panel(),null); assert.equal(app.sheet().textContent,'');
});

domTest('tooltip content is lazy and original titles survive cancel', t => {
    const app=boot(t,{ 'td [data-style-target="name"]': {'text-overflow-mode':'tooltip'} });
    const node=app.window.document.querySelector('#orders [data-style-target]');
    node.setAttribute('title','Original title');
    app.$(node).trigger('mouseover'); assert.equal(node.title,'Acme');
    app.$(node.closest('td')).trigger('mouseleave'); assert.equal(node.title,'Original title');
});

domTest('async cell replacements refresh targets and release the observer when closed', async t => {
    const app=boot(t); app.open();
    const cell=app.window.document.querySelector('#orders td.customer');
    cell.innerHTML='<span data-style-target="async-note" data-style-label="Loaded note">Loaded</span>';
    await new Promise(resolve=>app.window.setTimeout(resolve,40));
    assert.ok(Array.from(app.panel().querySelector('select').options).some(option=>option.text==='Loaded note'));
    app.panel().querySelector('.ib-style-cancel').click();
    const walks=app.walks(); cell.innerHTML='<b>Later update</b>';
    await new Promise(resolve=>app.window.setTimeout(resolve,40));
    assert.equal(app.walks(),walks);
});

domTest('discovery reaches every rendered cell and groups named targets independently of wrappers', t => {
    const app=boot(t), document=app.window.document;
    const cells=Array.from({length:100},(_,i)=>{
        const cell=document.createElement('td');
        cell.innerHTML='<div><a><span data-style-target="name" data-style-label="Customer name">Name '+i+'</span></a></div>';
        if (i===80) cell.innerHTML+='<span data-style-target="outside-sample">Later</span>';
        return cell;
    });
    const targets=engine.discover(cells,{},labels);
    assert.equal(targets.filter(item=>item.selector==='td [data-style-target="name"]').length,1);
    assert.ok(targets.some(item=>item.selector.includes('outside-sample')));
});


function scheduledFrames(window) {
    const jobs = new Map();
    let nextId = 0;
    const scheduler = {
        now: () => 0,
        schedule(callback) { const id = ++nextId; jobs.set(id, callback); return id; },
        cancel(id) { jobs.delete(id); },
        pending: () => jobs.size,
        flush() {
            let batches = 0;
            while (jobs.size) {
                assert.ok(++batches < 1000, 'discovery must eventually complete');
                const [id, callback] = jobs.entries().next().value;
                jobs.delete(id); callback();
            }
        }
    };
    if (window) {
        window.requestAnimationFrame = scheduler.schedule;
        window.cancelAnimationFrame = scheduler.cancel;
        window.performance.now = scheduler.now;
    }
    return scheduler;
}

domTest('a large cell is visited across frames without descendant or target caps', t => {
    const app = boot(t), cell = app.window.document.createElement('td');
    cell.innerHTML = Array.from({length:260}, (_, i) => '<span data-style-target="part-' + i + '">Part ' + i + '</span>').join('');
    const scheduler = scheduledFrames();
    let latest, complete = false, batches = 0;
    engine.discoverInChunks([cell], {}, labels, (targets, done) => { latest = targets; complete = done; batches++; }, scheduler);
    assert.equal(complete, false);
    assert.ok(latest.length > 1 && latest.length < 261);
    scheduler.flush();
    assert.equal(complete, true); assert.ok(batches > 1);
    assert.equal(latest.length, 261);
    assert.ok(latest.some(target => target.selector === 'td [data-style-target="part-259"]'));
});

domTest('late-row targets become editable without rescanning the column on selection', t => {
    const app = boot(t);
    app.tables.orders.rows.add(Array.from({length:160}, (_, i) => [
        i === 150 ? '<span data-style-target="late" data-style-label="Late detail">Last detail</span>' : '<span>Regular</span>', '10'
    ])).draw();
    const scheduler = scheduledFrames(app.window);
    app.open();
    assert.ok(app.walks() < 161);
    assert.equal(app.panel().querySelector('.ib-style-target').getAttribute('aria-busy'), 'true');
    scheduler.flush();
    assert.equal(app.walks(), 161);
    assert.equal(app.panel().querySelector('.ib-style-target').getAttribute('aria-busy'), 'false');
    const walks = app.walks();
    app.target('td [data-style-target="late"]');
    assert.equal(app.walks(), walks);
    assert.equal(app.panel().querySelector('.ib-style-sample').textContent, 'Last detail');
    assert.equal(app.panel().querySelector('.ib-style-unavailable').textContent, '');
    app.input('font-size', '22');
    assert.equal(app.window.getComputedStyle(app.window.document.querySelector('#orders [data-style-target="late"]')).fontSize, '22px');
});

domTest('closing or switching table cancels outstanding discovery frames', t => {
    const app = boot(t);
    app.tables.orders.rows.add(Array.from({length:150}, () => ['<span>Customer</span>', '10'])).draw();
    const scheduler = scheduledFrames(app.window);
    app.open(); assert.ok(scheduler.pending() > 0);
    app.panel().querySelector('.ib-style-cancel').click();
    const walks = app.walks(); scheduler.flush();
    assert.equal(app.walks(), walks); assert.equal(app.panel(), null);
    app.open(); app.open('invoices');
    const switchedWalks = app.walks(); scheduler.flush();
    assert.equal(app.walks(), switchedWalks);
    assert.equal(app.panel().querySelector('h3').textContent, 'Customer');
    assert.equal(app.panel().closest('[uk-offcanvas]').querySelector('ul').dataset.tableid, 'invoices');
});

domTest('pagination replaces an unfinished scan with the newly rendered page', t => {
    const app = boot(t, {}, {paging:true, pageLength:100});
    app.tables.orders.clear().rows.add(Array.from({length:200}, (_, i) => [
        i === 90 ? '<span data-style-target="old-page">Old page</span>'
            : i === 190 ? '<span data-style-target="new-page">New page</span>' : '<span>Regular</span>', '10'
    ])).draw();
    const scheduler = scheduledFrames(app.window);
    app.open(); assert.ok(scheduler.pending() > 0);
    app.tables.orders.page('next').draw('page');
    scheduler.flush();
    const options = Array.from(app.panel().querySelector('.ib-style-target').options, option => option.value);
    assert.ok(options.includes('td [data-style-target="new-page"]'));
    assert.ok(!options.includes('td [data-style-target="old-page"]'));
});

domTest('progressive discovery preserves a focused control and the save lock', t => {
    const app = boot(t);
    app.tables.orders.rows.add(Array.from({length:160}, () => ['<span>Customer</span>', '10'])).draw();
    const scheduler = scheduledFrames(app.window);
    app.open();
    const field = app.panel().querySelector('[data-property="font-size"]');
    field.focus(); app.input('font-size', '19');
    scheduler.flush();
    assert.equal(app.panel().querySelector('[data-property="font-size"]'), field);
    assert.equal(field.value, '19');
    app.tables.orders.draw();
    app.panel().querySelector('.ib-style-save').click();
    scheduler.flush();
    assert.ok(Array.from(app.panel().querySelectorAll('input,select,button')).every(node => node.disabled));
    app.requests[0].deferred.resolve({success:true});
    assert.equal(app.panel().querySelector('[data-property="font-size"]').value, '19');
});
