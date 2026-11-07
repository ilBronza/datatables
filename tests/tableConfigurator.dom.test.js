const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const configuration = require('../resources/assets/js/datatables.vendor.tableConfigurator.js');
let JSDOM;
try { ({JSDOM} = require('jsdom')); require.resolve('datatables.net-colreorder'); }
catch (_) { JSDOM = null; }
const domTest = (name, fn) => test(name, {skip:!JSDOM && 'Install DOM dependencies; see tests/README.md'}, fn);
const labels = {editTable:'Edit table',finishEditing:'Finish editing',title:'Column style',columns:'Columns',allRows:'All rows',
    move:'Move',target:'Element',cell:'Cell',text:'Text',icon:'Icon',link:'Link',list:'List',button:'Button',input:'Input',
    noChanges:'No changes',unsaved:'Unsaved',saving:'Saving',saved:'Saved',failed:'Failed',resetElement:'Reset',
    original:'Default',custom:'Reset',stateTrue:'True',stateFalse:'False',stateNull:'Null',statePreview:'Preview',stateIcon:'Icon name',
    orderSaved:'Order saved',orderFailed:'Order failed',groupOrderHint:'Keep fields in their header group',orderHint:'Drag fields',
    finishCellEdit:'Finish editing the data',configureElement:'Configure element'};

function boot(t, options = {}) {
    const dom = new JSDOM('<!doctype html><html><head></head><body></body></html>',{runScripts:'outside-only',pretendToBeVisual:true,url:'https://example.test'});
    const {window} = dom, document = window.document;
    t.after(()=>window.close());
    ['jquery','datatables.net',...(options.noReorder?[]:['datatables.net-colreorder'])].forEach(module=>window.eval(fs.readFileSync(require.resolve(module),'utf8')));
    const $ = window.jQuery;
    const names=['selection','customer','contact','active','amount'];
    const states={true:{icon:'check',family:'solid'},false:{icon:'close',family:'solid'},null:{icon:'minus',family:'solid'}};
    document.body.innerHTML = '<div uk-offcanvas><div class="uk-offcanvas-bar"><div class="ib-config-list-tools" hidden><button class="ib-config-reset-order">Reset order</button><p class="ib-config-order-hint"></p><p class="ib-config-order-status"></p></div><ul id="togglefieldsorders" class="toggle-vis-container" data-tableid="orders" data-settings-store-url="/styles"></ul></div></div>'
        + '<table id="orders" data-realid="orders" data-uisettingsroute="/ui"><thead><tr class="mainheader" data-dt-order="disable"><th></th><th colspan="2">People</th><th colspan="2">Values</th></tr>'
        + '<tr class="columnsintestations">'+names.map((name,i)=>'<th class="'+name+'" data-column="'+i+'" data-original-column-index="'+i+'" data-field-name="'+name+'">'+name+'</th>').join('')+'</tr>'
        + '<tr class="columns" data-dt-order="disable">'+names.map((name,i)=>'<th class="'+name+'" data-column="'+i+'" data-original-column-index="'+i+'" data-field-name="'+name+'"><input aria-label="Filter '+name+'"></th>').join('')+'</tr>'
        + '<tr class="summary" data-dt-order="disable">'+names.map((name,i)=>'<th class="summary'+i+'" data-column="'+i+'" data-original-column-index="'+i+'" data-field-name="'+name+'"></th>').join('')+'</tr></thead><tfoot><tr class="columns">'+names.map((name,i)=>'<th data-field-name="'+name+'" data-column="'+i+'" data-original-column-index="'+i+'"></th>').join('')+'</tr></tfoot></table>';
    const list=document.querySelector('ul');list.dataset.styleLabels=JSON.stringify(labels);list.dataset.styleFonts='{}';
    if(options.withoutConfiguration)delete list.dataset.styleLabels;
    names.forEach((name,i)=>{
        const li=document.createElement('li');li.className='ib-colvis-item';
        li.innerHTML='<button class="ib-config-handle ib-config-list-handle" hidden data-field-name="'+name+'">Move</button><a class="toggle-vis" data-name="'+name+'" data-style-column="'+name+'" data-column="'+i+'">'+name+'</a><button class="ib-colvis-gear">Style</button>';
        li.querySelector('a').dataset.configuration=JSON.stringify({name,column:name,index:i,width:'300px',locked:i===0,states:name==='active'?states:{}});list.appendChild(li);
    });
    window.__ibDatatableUiSettings={orders:{columnOrder:options.order||[]}};
    window.__ibDatatableBulkEditableFields={orders:[{name:'active',index:3}]};
    window.__ibDatatableFieldsGroups={orders:{people:{columnIndexes:[1,2]}}};
    window.__ibDtFieldsGroupsHidden={orders:{people:[2]}};
    window.ordersnullLastColumns=[4];
    window.__ibColumnStyleDraftByTable={orders:{}};
    const requests=[];
    $.ajax=options=>{const deferred=$.Deferred();requests.push({options,deferred});return deferred.promise();};
    ['columnIndexes','columnStyles','tableConfigurator'].forEach(name=>window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.'+name+'.js','utf8')));
    const data=[['','<a href="/customer"><i class="fa-solid fa-user"></i><span data-style-target="customer-name">Acme</span></a>','Jane','<span class="ib-toggle fa-solid fa-check" data-value="1" data-style-default-icon="check" data-style-applied-icon="check" data-style-target="boolean-true" data-style-state="true"></span>',42],
        ['','<span>Beta</span>','John','<span class="ib-toggle fa-solid fa-close" data-value="0" data-style-default-icon="close" data-style-applied-icon="close" data-style-target="boolean-false" data-style-state="false"></span>',12]];
    const settings={data,columns:names.map(name=>({name,className:name})),paging:false,info:false,order:[],...options.dt};
    window.ibDtTableConfigurator.prepare(document.getElementById('orders'),settings);
    const dt=$('#orders').DataTable(settings);
    const original=JSON.stringify(dt.rows().data().toArray());
    const api=window.ibDtTableConfigurator;
    function toggle(){api.toggle(dt);}
    function move(name,target){return api.move(dt,name,target);}
    function header(name){return dt.table().header().querySelector('.columnsintestations [data-field-name="'+name+'"]');}
    function open(name){header(name).querySelector('.ib-config-open').click();}
    function input(property,value){const node=document.querySelector('[data-property="'+property+'"]');node.value=value;node.dispatchEvent(new window.Event(node.tagName==='SELECT'?'change':'input',{bubbles:true}));}
    return {window,document,$,dt,api,requests,toggle,move,header,open,input,original};
}

test('saved order is reconciled within original groups, retaining new fields and locked columns',()=>{
    const fields=[{name:'select',locked:true,group:'a'},{name:'a',group:'a'},{name:'b',group:'a'},{name:'c',group:'b'}];
    assert.deepEqual(configuration.reconcileOrder(fields,['c','b','b','removed','a','select']),['select','b','a','c']);
    assert.deepEqual(configuration.reconcileOrder(fields,['b']),['select','b','a','c']);
});

domTest('edit mode exposes contextual controls without sorting or activating links and toggles',t=>{
    const app=boot(t),{document,window,dt}=app;
    assert.equal(document.querySelector('.ib-column-style-editor'),null);
    assert.equal(app.header('customer').querySelector('.ib-config-header-actions').hidden,true);
    assert.equal(app.move('customer','contact'),false);
    app.toggle();app.open('customer');
    assert.equal(document.querySelector('.ib-column-style-editor h3').textContent,'customer');
    assert.deepEqual(Array.from(dt.order()),[]);
    document.querySelector('.ib-style-cancel').click();
    let clicks=0;
    document.querySelector('#orders a').addEventListener('click',()=>clicks++);
    const text=document.querySelector('#orders [data-style-target="customer-name"]');text.click();
    assert.equal(clicks,0);
    assert.equal(document.querySelector('.ib-style-target').value,'td [data-style-target="customer-name"]');
    document.querySelector('.ib-style-cancel').click();
    const toggle=document.querySelector('#orders .ib-toggle');toggle.addEventListener('click',()=>clicks++);toggle.click();
    assert.equal(clicks,0);assert.equal(toggle.dataset.value,'1');
    assert.equal(document.querySelector('.ib-style-target').value,'td [data-style-target="boolean-true"]');
    app.toggle();assert.equal(document.querySelector('.ib-column-style-editor'),null);
    assert.equal(app.header('customer').querySelector('.ib-config-header-actions').hidden,true);
    toggle.click();assert.equal(clicks,1);
});

domTest('all boolean states can be styled independently, including absent null, with cancel and save',t=>{
    const app=boot(t),{document,window}=app;app.toggle();app.open('active');
    const target=document.querySelector('.ib-style-target');
    assert.ok(Array.from(target.options).some(option=>option.text==='Null'));
    target.value='td [data-style-target="boolean-null"]';target.dispatchEvent(new window.Event('change',{bubbles:true}));
    app.input('__icon','circle-question');app.input('font-size','22');
    assert.ok(document.querySelector('.ib-style-state-preview i').classList.contains('fa-circle-question'));
    assert.equal(document.querySelector('#orders [data-value="null"]'),null);
    document.querySelector('.ib-style-save').click();
    const request=app.requests[0];
    assert.equal(JSON.parse(request.options.data.settings_json).selectors[target.value].__icon,'circle-question');
    request.deferred.resolve({success:true});document.querySelector('.ib-style-cancel').click();
    assert.equal(window.ibDtColumnStyles.icon('orders','active','td [data-style-target="boolean-null"]','minus'),'circle-question');
    app.open('active');
    const select=document.querySelector('.ib-style-target');
    select.value='td [data-style-target="boolean-null"]';select.dispatchEvent(new window.Event('change',{bubbles:true}));
    assert.equal(document.querySelector('[data-property="font-size"]').value,'22');
    assert.equal(document.querySelector('[data-property="font-weight"]'),null);
    select.value='td [data-style-target="boolean-true"]';select.dispatchEvent(new window.Event('change',{bubbles:true}));
    app.input('__icon','star');assert.ok(document.querySelector('#orders .ib-toggle').classList.contains('fa-star'));
    document.querySelector('.ib-style-cancel').click();
    assert.ok(document.querySelector('#orders .ib-toggle').classList.contains('fa-check'));
    assert.ok(!document.querySelector('#orders .ib-toggle').classList.contains('fa-star'));
});

domTest('reorder follows identities across filters, bulk editors, summary, raw data and persistence',t=>{
    const app=boot(t),{window,document,dt}=app;app.toggle();
    app.move('active','amount');
    assert.deepEqual(Array.from(app.api.current(dt)),['selection','customer','contact','amount','active']);
    assert.equal(dt.column(4).dataSrc(),3);
    assert.equal(window.__ibDatatableBulkEditableFields.orders[0].index,4);
    assert.deepEqual(Array.from(window.ordersnullLastColumns),[3]);
    assert.equal(app.header('active').dataset.column,'4');
    assert.equal(dt.table().header().querySelector('tr.columns [data-field-name="active"]').dataset.column,'4');
    assert.ok(dt.table().header().querySelector('tr.summary [data-field-name="active"]').classList.contains('summary4'));
    assert.equal(JSON.stringify(dt.rows().data().toArray()),app.original);
    assert.deepEqual(JSON.parse(app.requests[0].options.data),{key:'columnOrder',value:['selection','customer','contact','amount','active']});
    app.requests[0].deferred.resolve({success:true});
    app.move('contact','customer');app.requests[1].deferred.resolve({success:true});
    assert.deepEqual(Array.from(window.__ibDatatableFieldsGroups.orders.people.columnIndexes),[2,1]);
    assert.deepEqual(Array.from(window.__ibDtFieldsGroupsHidden.orders.people),[1]);
    dt.column(1).search('Jane').draw();assert.equal(dt.rows({search:'applied'}).count(),1);
    assert.equal(dt.cell(dt.rows({search:'applied'}).indexes()[0],2).data().includes('Acme'),true);
    document.querySelector('.ib-config-reset-order').click();app.requests[2].deferred.resolve({success:true});
    assert.deepEqual(Array.from(app.api.current(dt)),['selection','customer','contact','active','amount']);
});

domTest('main header spans remain attached to their fields through hiding, moves, restore and stale saved orders',t=>{
    const app=boot(t,{order:['amount','contact','active','customer','selection','removed']}),{dt}=app;
    assert.deepEqual(Array.from(app.api.current(dt)),['selection','contact','customer','amount','active']);
    app.toggle();assert.equal(app.move('customer','active'),false);assert.equal(app.move('selection','customer'),false);
    dt.column(2).visible(false);
    let groups=dt.table().header().querySelector('.mainheader').cells;
    assert.deepEqual(Array.from(groups,cell=>[cell.textContent,cell.colSpan]),[['',1],['People',1],['Values',2]]);
    app.move('customer','contact');app.requests[0].deferred.resolve({success:true});
    assert.equal(dt.column(1).visible(),false);
    assert.deepEqual(Array.from(dt.table().header().querySelector('.mainheader').cells,cell=>[cell.textContent,cell.colSpan]),[['',1],['People',1],['Values',2]]);
    dt.column(1).visible(true);
    assert.equal(dt.table().header().querySelector('.mainheader').cells[1].colSpan,2);
});

domTest('keyboard reordering is mode-gated and failed saves restore the last acknowledged order',t=>{
    const app=boot(t),{window}=app;app.toggle();
    const handle=app.header('customer').querySelector('.ib-config-handle');
    handle.dispatchEvent(new window.KeyboardEvent('keydown',{key:'ArrowRight',bubbles:true,cancelable:true}));
    assert.equal(app.api.current(app.dt)[2],'customer');
    assert.equal(app.document.activeElement.closest('[data-field-name]').dataset.fieldName,'customer');
    app.requests[0].deferred.reject();
    assert.equal(app.api.current(app.dt)[1],'customer');
    app.toggle();assert.equal(app.move('customer','contact'),false);
    assert.equal(handle.draggable,false);
});

domTest('optional stateSave index arrays are normalized before personal named order is restored',t=>{
    const app=boot(t), options={};app.api.prepare(app.dt.table().node(),options);
    const state={colReorder:[0,2,1,3,4],columns:['select','contact','customer','active','amount'],order:[[2,'asc']]};
    options.stateLoadParams({},state);
    assert.equal(state.colReorder,undefined);
    assert.deepEqual(Array.from(state.columns),['select','customer','contact','active','amount']);
    assert.deepEqual(state.order,[[1,'asc']]);
});

domTest('a smaller width overrides inner important widths and cancel restores the backend and original inline style',t=>{
    const app=boot(t),{document,dt}=app;
    const text=document.querySelector('#orders [data-style-target="customer-name"]');
    text.style.setProperty('min-width','300px','important');text.style.setProperty('width','300px','important');
    app.toggle();app.open('customer');app.input('width','150');
    app.window.ibDtColumnStyles.layout(dt);
    assert.equal(dt.settings()[0].aoColumns[1].sWidthOrig,'150px');
    assert.equal(text.style.minWidth,'0');assert.equal(text.style.maxWidth,'126px');
    assert.equal(document.querySelector('#orders').style.tableLayout,'fixed');
    document.querySelector('.ib-style-cancel').click();
    assert.equal(dt.settings()[0].aoColumns[1].sWidthOrig,'300px');
    // jsdom 27's CSSOM drops priorities for min/max-width even before this code runs.
    // Check width's preserved priority here; min/max priorities are checked in the browser harness.
    assert.equal(text.style.minWidth,'300px');assert.equal(text.style.getPropertyPriority('width'),'important');
    assert.equal(text.style.width,'300px');assert.equal(text.style.maxWidth,'');
});

domTest('group boundaries survive when initial visibility detaches the first header cell',t=>{
    const app=boot(t,{order:['amount','contact','customer','active'],dt:{columnDefs:[{targets:[0,1],visible:false}]}});
    app.toggle();
    assert.equal(app.move('contact','active'),false);
    assert.deepEqual(Array.from(app.api.current(app.dt)),['selection','contact','customer','amount','active']);
    assert.deepEqual(Array.from(app.dt.table().header().querySelector('.mainheader').cells,cell=>[cell.textContent,cell.colSpan]),[['People',1],['Values',2]]);
});

domTest('a missing preference route restores the acknowledged column order',t=>{
    const app=boot(t);delete app.dt.table().node().dataset.uisettingsroute;app.toggle();app.move('customer','contact');
    assert.deepEqual(Array.from(app.api.current(app.dt)),['selection','customer','contact','active','amount']);
    assert.equal(app.requests.length,0);
    assert.equal(app.document.querySelector('.ib-config-order-status').textContent,'Order failed');
});

domTest('an async response and its cache follow the field when reordered during the request',t=>{
    const app=boot(t),{window,$,dt}=app, calls=[];
    $.fn.dataTable.Api.register('column().getHeaderData()',function(name){
        const field=this.header().dataset.fieldName;
        return name==='name'?field:field==='customer'?({asyncRoute:'/customer',asyncCache:1,asyncRenderer:'text'}[name]):false;
    });
    window.ibDtGetRowIdFromRowIndex=(_table,index)=>index;
    window.__ibCallAjax=(_url,_type,_data,options)=>calls.push(options);
    window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.asyncColumns.js','utf8'));
    window.ibResolveAsyncColumns(dt);
    assert.equal(calls.length,1);
    app.toggle();app.move('customer','contact');app.requests[0].deferred.resolve({success:true});
    assert.equal(calls.length,1);
    calls[0].onSuccess({0:'Customer received',1:'Other customer'});calls[0].onComplete();
    assert.equal(dt.cell(0,2).node().textContent,'Customer received');
    assert.equal(dt.cell(0,1).node().textContent,'Jane');
    dt.draw(false);
    assert.equal(calls.length,1);
    assert.equal(dt.cell(0,2).node().textContent,'Customer received');
});

domTest('row responses retain cell revisions and original data sources after a move',t=>{
    const app=boot(t),{window,dt}=app;
    window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.rowPatch.js','utf8'));
    dt.cell(0,1).node().__ibDtEditorRevision=5;
    dt.cell(0,2).node().__ibDtEditorRevision=2;
    const snapshot=window.ibDtCaptureRowRevisionSnapshot(dt,dt.row(0));
    app.toggle();app.move('customer','contact');app.requests[0].deferred.resolve({success:true});
    const response=Array.from(dt.row(0).data());response[1]='Updated customer';response[2]='Updated contact';
    const result=window.ibDtPatchRenderedRow(dt,dt.row(0),response,snapshot);
    assert.equal(result.staleCells,0);
    assert.equal(dt.cell(0,1).data(),'Updated contact');assert.equal(dt.cell(0,2).data(),'Updated customer');
});

domTest('deferred cell refresh follows the data source when its focused column moves',t=>{
    const app=boot(t),{window,dt}=app;
    window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.rowPatch.js','utf8'));
    app.toggle();const customer=dt.cell(0,1).node();customer.focus();
    const response=Array.from(dt.row(0).data());response[1]='Deferred customer';response[2]='New contact';
    window.ibDtPatchRenderedRow(dt,dt.row(0),response);
    assert.notEqual(dt.cell(0,1).data(),'Deferred customer');
    app.move('customer','contact');app.requests[0].deferred.resolve({success:true});
    customer.blur();window.ibDtFlushPendingRenderedCells();
    assert.equal(dt.cell(0,2).data(),'Deferred customer');assert.equal(dt.cell(0,1).data(),'New contact');
});

domTest('original indexes stay immutable in live, hidden and cloned headers across moves and reset',t=>{
    const app=boot(t,{dt:{scrollX:true}}),{dt,document}=app;
    const names=['selection','customer','contact','active','amount'];
    const capturedOriginal=app.header('customer').dataset.originalColumnIndex;
    const retainedHeaders=new Set(dt.settings()[0].aoHeader.flatMap(row=>row.map(entry=>entry.cell)));
    function verify(expected){
        const nodes=new Set([...retainedHeaders,...document.querySelectorAll('th[data-field-name]')]);
        [dt.settings()[0].aoHeader,dt.settings()[0].aoFooter].forEach(rows=>rows.forEach(row=>row.forEach(entry=>nodes.add(entry.cell))));
        nodes.forEach(node=>{
            const name=node.dataset.fieldName;
            if(!name){assert.equal(node.hasAttribute('data-original-column-index'),false);return;}
            assert.equal(node.dataset.originalColumnIndex,String(names.indexOf(name)));
            assert.equal(node.dataset.column,String(expected.indexOf(name)));
        });
        names.forEach((name,original)=>assert.equal(dt.columnIndexFromOriginal(original),expected.indexOf(name)));
        assert.ok(document.querySelectorAll('.dt-scroll-head th[data-original-column-index]').length>0);
        assert.ok(document.querySelectorAll('.dt-scroll-body thead th[data-original-column-index]').length>0);
    }
    verify(names);
    app.toggle();app.move('customer','contact');app.requests[0].deferred.resolve({success:true});
    verify(['selection','contact','customer','active','amount']);
    assert.equal(dt.columnIndexFromOriginal(capturedOriginal),2);
    assert.ok(dt.cell(0,dt.columnIndexFromOriginal(capturedOriginal)).data().includes('Acme'));
    assert.equal(dt.column(dt.columnIndexFromOriginal(capturedOriginal)).dataSrc(),1);
    // The current API index stays 2 while the visible index becomes 1, then null.
    dt.column(1).visible(false);
    assert.equal(dt.column(2).index('visible'),1);
    verify(['selection','contact','customer','active','amount']);
    dt.column(2).visible(false);
    assert.equal(dt.column(2).index('visible'),null);
    assert.equal(dt.columnIndexFromOriginal(capturedOriginal),2);
    assert.equal(dt.column(2).header().isConnected,false);
    app.move('customer','contact');app.requests[1].deferred.resolve({success:true});
    verify(names);
    dt.column(1).visible(true);dt.column(2).visible(true);
    app.move('active','amount');app.requests[2].deferred.resolve({success:true});
    verify(['selection','customer','contact','amount','active']);
    document.querySelector('.ib-config-reset-order').click();app.requests[3].deferred.resolve({success:true});
    verify(names);
    assert.equal(JSON.stringify(dt.rows().data().toArray()),app.original);
});

domTest('original index lookup uses restored personal order and works without ColReorder or configuration',t=>{
    const restored=boot(t,{order:['contact','customer','amount','active']});
    assert.equal(restored.dt.columnIndexFromOriginal('1'),2);
    assert.equal(restored.dt.columnIndexFromOriginal(3),4);
    const plain=boot(t,{noReorder:true,withoutConfiguration:true,dt:{columnDefs:[{targets:1,visible:false}]}});
    assert.equal(plain.$.fn.dataTable.ColReorder,undefined);
    assert.equal(plain.dt.columnIndexFromOriginal(1),1);
    assert.equal(plain.dt.columnIndexFromOriginal(' 2 '),2);
    assert.equal(plain.dt.column(2).index('visible'),1);
});

domTest('original index lookup rejects invalid, missing and ambiguous references',t=>{
    const {dt,$,document}=boot(t);
    for(const invalid of [undefined,null,'',' ',true,false,{},[],[1],-1,1.5,NaN,Infinity,'1.0','1e0','0x1','1x',Number.MAX_SAFE_INTEGER+1,99])
        assert.equal(dt.columnIndexFromOriginal(invalid),null,String(invalid));
    assert.equal(dt.columnIndexFromOriginal(0),0);
    const missing=new $.fn.dataTable.Api([]);
    assert.equal(missing.columnIndexFromOriginal(0),null);
    document.querySelectorAll('th[data-field-name="customer"]').forEach(node=>node.removeAttribute('data-original-column-index'));
    assert.equal(dt.columnIndexFromOriginal(1),null);
    document.querySelectorAll('th[data-field-name="contact"]').forEach(node=>node.dataset.originalColumnIndex='0');
    assert.equal(dt.columnIndexFromOriginal(0),null);
});
