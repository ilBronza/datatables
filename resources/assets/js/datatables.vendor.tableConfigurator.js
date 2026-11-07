/* One configurator per table; the style editor and contextual picker are created on demand. */
(function (root, factory) {
    const api = factory();
    if (typeof module === 'object' && module.exports) module.exports = api;
    if (root && root.jQuery) {
        root.ibDtTableConfigurator = api;
        Object.assign(api, api.bind(root, root.document, root.jQuery));
    }
})(typeof window === 'undefined' ? null : window, function () {
    'use strict';

    // Keep each main-header span (and the select-all column) in its original slots.
    // Unknown/removed fields disappear; new fields retain their backend position in their group.
    function reconcileOrder(fields, saved) {
        const wanted = Array.isArray(saved) ? Array.from(new Set(saved)) : [];
        const groups = new Map();
        fields.forEach(field => {
            const key = field.locked ? 'locked:' + field.name : field.group;
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(field.name);
        });
        groups.forEach((names, key) => groups.set(key, wanted.filter(name => names.includes(name)).concat(names.filter(name => !wanted.includes(name)))));
        return fields.map(field => groups.get(field.locked ? 'locked:' + field.name : field.group).shift());
    }

    function bind(window, document, $) {
        const instances = new Map();
        let drag = null;
        const configurationList = table => document.getElementById('togglefields' + table.id);
        function metadata(table) {
            const list = configurationList(table);
            if (!list || !list.dataset.styleLabels) return null;
            const fields = Array.from(list.querySelectorAll('.toggle-vis'), node => {
                const definition = JSON.parse(node.dataset.configuration || '{}');
                return Object.assign({name:node.dataset.name, column:node.dataset.styleColumn, index:Number(node.dataset.column)}, definition, {node});
            });
            return {list, fields, labels:JSON.parse(list.dataset.styleLabels)};
        }
        function prepare(table, options) {
            if (!metadata(table)) return;
            // ColReorder 2 preserves the original row data sources. Our handles call its API;
            // no whole-header drag listener can steal sorting, filtering or selection clicks.
            if ($.fn.dataTable.ColReorder && /^2\./.test($.fn.dataTable.ColReorder.version))
                options.colReorder = {enable:false, headerRows:[]};
            const load = options.stateLoadParams;
            options.stateLoadParams = function (settings, state) {
                // The personal named order is authoritative, including with optional stateSave.
                const order = state.colReorder;
                if (Array.isArray(order)) {
                    if (Array.isArray(state.columns) && order.length === state.columns.length) {
                        const columns = [];
                        order.forEach((original, current) => { columns[original] = state.columns[current]; });
                        state.columns = columns;
                        // DataTables 2.3 saves named columns as strings. Only legacy
                        // numeric indexes refer to positions in the saved layout.
                        if (Array.isArray(state.order)) state.order.forEach(item => {
                            if (Array.isArray(item) && Number.isInteger(item[0])) item[0] = order[item[0]];
                        });
                    }
                    delete state.colReorder;
                }
                // Earlier restores could save undefined column references as null.
                // Keep valid entries, or let the configured default order apply.
                if (Array.isArray(state.order) && state.order.length) {
                    state.order = state.order.filter(item => Array.isArray(item) &&
                        (typeof item[0] === 'string' || (Number.isInteger(item[0]) && item[0] >= 0)));
                    if (!state.order.length) delete state.order;
                }
                if (load) return load.apply(this, arguments);
            };
        }

        function attach(dt) {
            const table = dt.table().node(), id = table.id;
            if (instances.has(id)) return instances.get(id);
            const data = metadata(table);
            if (!data) return null;
            const {list, fields, labels} = data;
            const settings = dt.settings()[0];
            const row = settings.aoHeader.find(row => row.row && row.row.classList.contains('mainheader'));
            const groupCells = new Map();
            let blankGroup = 0, previousBlank = false;
            fields.forEach((field, index) => {
                const cell = row && row[index] && row[index].cell;
                const blank = !cell || !cell.textContent.trim();
                if (blank && !previousBlank) blankGroup++;
                if (cell && !groupCells.has(cell)) groupCells.set(cell, groupCells.size);
                field.group = blank ? 'ungrouped:' + blankGroup : 'header:' + groupCells.get(cell);
                previousBlank = blank;
            });
            const instance = {dt, table, id, list, fields, labels, active:false, saving:false, queued:null, picker:null, tabStops:new Map(), acknowledged:fields.map(field=>field.name)};
            instances.set(id, instance);
            const reorderAvailable = !!settings._colReorder && /^2\./.test($.fn.dataTable.ColReorder.version);
            instance.reorderAvailable = reorderAvailable;
            const tools = list.parentElement.querySelector('.ib-config-list-tools');
            if (tools) {
                tools.querySelector('.ib-config-order-hint').textContent = row ? labels.groupOrderHint : labels.orderHint;
                tools.querySelector('.ib-config-reset-order').addEventListener('click', () => {
                    if (instance.active && !busy(instance)) setOrder(instance, fields.map(field=>field.name), true);
                });
            }

            function sync(mapping) {
                const order = current(instance);
                const indexes = Object.fromEntries(order.map((name,index)=>[name,index]));
                fields.forEach(field => { field.node.dataset.column=indexes[field.name]; $(field.node).data('column', indexes[field.name]); });
                const map = window.__ibDatatableColumnIndexToFieldName || (window.__ibDatatableColumnIndexToFieldName = {});
                map[id] = Object.fromEntries(order.map((name,index)=>[index,name]));
                // Keep legacy consumers' live index arrays synchronized; raw row data is untouched.
                if (mapping) {
                    const remap = array => { if (Array.isArray(array)) array.splice(0,array.length,...array.map(index=>mapping[index])); };
                    Object.values(((window.__ibDatatableFieldsGroups || {})[id]) || {}).forEach(group=>remap(group.columnIndexes));
                    Object.values(((window.__ibDtFieldsGroupsHidden || {})[id]) || {}).forEach(remap);
                    const bulk = (window.__ibDatatableBulkEditableFields || window.__ibDatatableToggleableFields || {})[id] || [];
                    bulk.forEach(field=>{ field.index=mapping[field.index]; });
                    remap(window[id + 'nullLastColumns']);
                }
                // Includes detached headers of hidden columns, scrolling headers and floating clones.
                const headers = new Set();
                [settings.aoHeader,settings.aoFooter].forEach(rows=>rows.forEach(row=>row.forEach(entry=>{if(entry)headers.add(entry.cell);} )));
                document.querySelectorAll('table').forEach(node=>{
                    if (node === table || node.dataset.realid === id || node.closest('[id="' + id + '_wrapper"]'))
                        node.querySelectorAll('th[data-field-name]').forEach(header=>headers.add(header));
                });
                headers.forEach(header=>{
                    const index = indexes[header.dataset.fieldName];
                    if (index === undefined) return;
                    // data-original-column-index is emitted by PHP and never rewritten here.
                    header.dataset.column=index; $(header).data('column',index);
                    if (header.parentElement && header.parentElement.matches('.summary,.inlinesearchsummary')) {
                        Array.from(header.classList).filter(name=>/^summary\d+$/.test(name)).forEach(name=>header.classList.remove(name));
                        header.classList.add('summary'+index);
                    }
                });
                order.forEach(name=>list.appendChild(fields.find(field=>field.name===name).node.closest('li')));
                decorate(instance);
            }
            instance.sync = sync;
            dt.on('column-reorder.dt.ibConfigurator', (_event, _settings, details)=>sync(details.mapping));
            dt.on('draw.dt.ibConfigurator column-visibility.dt.ibConfigurator fixedHeader-mode.dt.ibConfigurator', ()=>sync());
            dt.on('destroy.dt.ibConfigurator', ()=>{setMode(instance,false);instances.delete(id);dt.off('.ibConfigurator');});
            sync();
            const wanted = reconcileOrder(fields, ((window.__ibDatatableUiSettings || {})[id] || {}).columnOrder);
            setOrder(instance,wanted,false);
            instance.acknowledged=current(instance);
            return instance;
        }

        function current(instance) {
            if (!instance.reorderAvailable) return instance.fields.map(field=>field.name);
            return instance.dt.colReorder.order().map(index=>instance.fields[index].name);
        }
        function status(instance, message) {
            const node = instance.list.parentElement.querySelector('.ib-config-order-status');
            if (node) node.textContent=message;
        }
        function busy(instance) {
            const blocked = (window.ibDtColumnStyles && window.ibDtColumnStyles.isSaving())
                || instance.table.querySelector('.editorchanged, .editorchangedrow, .ib-datatable-inline-create-row, .ib-datatable-inline-edit-child')
                || Array.from(instance.table.querySelectorAll('tbody td')).some(cell=>cell.__ibDtEditorSaving)
                || $(instance.dt.table().container()).hasClass('ib-datatable-inline-create-locked');
            if (blocked) {
                status(instance, instance.labels.finishCellEdit);
                if (window.addWarningNotification) window.addWarningNotification(instance.labels.finishCellEdit);
            }
            return !!blocked;
        }
        function setOrder(instance, wanted, persist) {
            if (!instance.reorderAvailable) { if (persist) status(instance,instance.labels.reorderUnavailable); return; }
            const safe = reconcileOrder(instance.fields, wanted);
            instance.dt.colReorder.order(safe.map(name=>instance.fields.findIndex(field=>field.name===name)),true);
            instance.sync();
            instance.dt.draw(false);
            instance.dt.columns.adjust();
            if (instance.dt.fixedHeader && instance.dt.fixedHeader.adjust) instance.dt.fixedHeader.adjust();
            if (persist) { instance.queued=safe; persistOrder(instance); }
        }
        function persistOrder(instance) {
            if (instance.saving || !instance.queued) return;
            const order = instance.queued; instance.queued=null;
            const url = instance.table.dataset.uisettingsroute;
            if (!url) { setOrder(instance,instance.acknowledged,false); status(instance,instance.labels.orderFailed); return; }
            instance.saving=true;status(instance,instance.labels.saving);
            const token=document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
            $.ajax({url,type:'POST',dataType:'json',headers:token?{'X-CSRF-TOKEN':token}:{},
                contentType:'application/json', data:JSON.stringify({key:'columnOrder',value:order})})
                .done(response=>{
                    if (!response || response.success !== true) { failed(); return; }
                    instance.acknowledged=order;
                    window.__ibDatatableUiSettings = window.__ibDatatableUiSettings || {};
                    window.__ibDatatableUiSettings[instance.id] = Object.assign({},window.__ibDatatableUiSettings[instance.id],{columnOrder:order});
                    status(instance,instance.labels.orderSaved);
                }).fail(failed).always(()=>{instance.saving=false;persistOrder(instance);});
            function failed() {
                if (!instance.queued && instances.has(instance.id)) setOrder(instance,instance.acknowledged,false);
                status(instance,instance.labels.orderFailed);
                if (window.addDangerNotification) window.addDangerNotification(instance.labels.orderFailed);
            }
        }
        function move(instance, name, target) {
            if (!instance.active || busy(instance)) return false;
            const field=instance.fields.find(field=>field.name===name), destination=instance.fields.find(field=>field.name===target);
            if (!field || !destination || field.locked || destination.locked || field.group!==destination.group) {
                status(instance,instance.labels.groupOrderHint); return false;
            }
            const order=current(instance), from=order.indexOf(name), to=order.indexOf(target);
            order.splice(from,1);order.splice(to,0,name);setOrder(instance,order,true);return true;
        }
        function button(label, className, icon) {
            const node=document.createElement('button');node.type='button';node.className=className;
            node.setAttribute('aria-label',label);node.title=label;node.dataset.ibConfigControl='';
            const glyph=document.createElement('i');glyph.className='fa-solid fa-'+icon;glyph.setAttribute('aria-hidden','true');node.appendChild(glyph);return node;
        }
        function decorate(instance) {
            const {table,dt,fields,active,labels,list} = instance;
            $(dt.table().container()).toggleClass('ib-configuring',active);
            table.classList.toggle('ib-configuring',active);
            const tools=list.parentElement.querySelector('.ib-config-list-tools');if(tools)tools.hidden=!active;
            const headers=new Set();
            dt.settings()[0].aoHeader.forEach(row=>row.forEach(entry=>{if(entry && entry.cell.parentElement?.classList.contains('columnsintestations'))headers.add(entry.cell);}));
            document.querySelectorAll('table[data-realid]').forEach(node=>{if(node.dataset.realid===table.id)node.querySelectorAll('.columnsintestations th').forEach(th=>headers.add(th));});
            headers.forEach(th=>{
                const field=fields.find(field=>field.name===th.dataset.fieldName);
                if(!field)return;
                let actions=th.querySelector('.ib-config-header-actions');
                if(!actions){
                    actions=document.createElement('span');actions.className='ib-config-header-actions';actions.dataset.ibConfigControl='';
                    actions.dataset.tableid=table.id;actions.dataset.fieldName=field.name;
                    actions.appendChild(button(labels.title+': '+field.node.textContent.trim(),'ib-config-open','gear'));
                    if(instance.reorderAvailable && !field.locked)actions.appendChild(button(labels.move+': '+field.node.textContent.trim(),'ib-config-handle','grip-vertical'));
                    th.appendChild(actions);
                }
                actions.hidden=!active;
                actions.querySelectorAll('.ib-config-handle').forEach(handle=>{handle.draggable=active;});
            });
            list.querySelectorAll('.ib-config-list-handle').forEach(handle=>{
                const field=fields.find(field=>field.name===handle.dataset.fieldName);
                handle.hidden=!active || !instance.reorderAvailable || field.locked;
                handle.draggable=active && !field.locked;handle.dataset.tableid=table.id;
            });
            if (active) table.querySelectorAll('tbody td').forEach(cell=>{
                if(!instance.tabStops.has(cell))instance.tabStops.set(cell,cell.getAttribute('tabindex'));
                cell.setAttribute('tabindex','0');
            });
        }
        function setMode(instance, active) {
            if (active && busy(instance)) return;
            if (!active && window.__ibColumnStylePreview?.tableId === instance.id && window.ibDtColumnStyles && window.ibDtColumnStyles.close() === false) return;
            instance.active=active;
            if (instance.reorderAvailable) instance.dt.colReorder.enable(active);
            if (instance.dt.keys && instance.dt.keys.disable) {
                if (active) { instance.keysEnabled=instance.dt.keys.enabled(); instance.dt.keys.disable(); }
                else if(instance.keysEnabled)instance.dt.keys.enable();
            }
            if(!active){
                if(instance.picker)instance.picker.remove();instance.picker=null;
                instance.tabStops.forEach((value,node)=>{if(value===null)node.removeAttribute('tabindex');else node.setAttribute('tabindex',value);});instance.tabStops.clear();
            }
            const buttons=instance.dt.table().container().querySelectorAll('.ib-config-toggle');
            buttons.forEach(node=>{node.textContent=active?instance.labels.finishEditing:instance.labels.editTable;node.setAttribute('aria-pressed',String(active));});
            decorate(instance);
        }
        function open(instance, field, target, returnFocus) {
            if (!instance.active || !field || !window.ibDtColumnStyles || window.ibDtColumnStyles.isSaving())return;
            const gear=field.node.closest('li').querySelector('.ib-colvis-gear');
            if (!gear)return;
            if(instance.picker)instance.picker.hidden=true;
            const canvas=instance.list.closest('[uk-offcanvas]');
            if(window.UIkit && canvas)window.UIkit.offcanvas(canvas).show();
            window.ibDtColumnStyles.open(gear,target,returnFocus);
        }
        function context(target) {
            const cell=target.closest && target.closest('tbody td');
            if (!cell || target.closest('[data-ib-config-control]'))return null;
            if (target.closest('.fieldoperations')) target=cell;
            const table=cell.closest('table'),instance=instances.get(table.id || table.dataset.realid);
            if(!instance || !instance.active || table!==instance.table)return null;
            const index=instance.dt.cell(cell).index();if(!index)return null;
            const field=instance.fields.find(field=>field.name===current(instance)[index.column]);
            if (!field || field.locked)return null;
            const svg=target.closest('svg');if(svg)target=svg;
            return {instance,cell,field,target};
        }
        function picker(ctx) {
            const {instance,cell,field,target}=ctx, engine=window.ibDtColumnStyles;
            if(!engine)return;
            let node=instance.picker;
            if(!node){
                node=document.createElement('div');node.className='ib-config-picker';node.dataset.ibConfigControl='';
                const select=document.createElement('select');select.setAttribute('aria-label',instance.labels.target);node.appendChild(select);
                const edit=button(instance.labels.configureElement,'ib-config-pick','gear');node.appendChild(edit);
                edit.addEventListener('click',()=>open(instance,node.context.field,select.value,node.context.cell));
                document.body.appendChild(node);instance.picker=node;
            }
            node.context=ctx;node.hidden=false;
            const select=node.querySelector('select');select.replaceChildren();
            let element=target;
            while(element && cell.contains(element)){
                if(element===cell || engine.paths(engine.structuralPath(element,cell)).length){
                    const option=document.createElement('option');option.value=element===cell?'td':engine.structuralPath(element,cell);
                    option.textContent=field.node.textContent.trim()+' · '+instance.labels[element===cell?'cell':engine.kind(element)];select.appendChild(option);
                }
                if(element===cell)break;element=element.parentElement;
            }
            const rect=target.getBoundingClientRect();
            node.style.left=Math.max(4,Math.min(window.innerWidth-node.offsetWidth-4,rect.left))+window.scrollX+'px';
            node.style.top=Math.max(4,rect.top-node.offsetHeight-3)+window.scrollY+'px';
        }
        document.addEventListener('pointerover',event=>{const ctx=context(event.target);if(ctx)picker(ctx);},true);
        document.addEventListener('focusin',event=>{const ctx=context(event.target);if(ctx)picker(ctx);},true);
        // Capture before delegated editors, row selection, links and toggle handlers.
        ['pointerdown','mousedown','touchstart','dblclick','click','keydown'].forEach(type=>document.addEventListener(type,event=>{
            const ctx=context(event.target);if(!ctx)return;
            if(type==='keydown' && !['Enter',' '].includes(event.key))return;
            if(type!=='touchstart')event.preventDefault();event.stopImmediatePropagation();
            if(type==='click' || type==='keydown')open(ctx.instance,ctx.field,ctx.target===ctx.cell?'td':window.ibDtColumnStyles.structuralPath(ctx.target,ctx.cell),ctx.cell);
        },true));
        // Controls are isolated from DataTables ordering (including keyboard ordering).
        ['click','mousedown','pointerdown','touchstart','keydown'].forEach(type=>document.addEventListener(type,event=>{
            const control=event.target.closest && event.target.closest('.ib-config-header-actions,.ib-config-list-handle');
            if(!control)return;
            const instance=instances.get(control.dataset.tableid);if(!instance || !instance.active)return;
            const field=instance.fields.find(field=>field.name===control.dataset.fieldName);
            event.stopPropagation();
            if(type==='click' && event.target.closest('.ib-config-open')){event.preventDefault();open(instance,field,'td',event.target.closest('button'));}
            if(type==='keydown' && event.target.closest('.ib-config-handle') && ['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(event.key)){
                event.preventDefault();const order=current(instance),index=order.indexOf(field.name),delta=['ArrowLeft','ArrowUp'].includes(event.key)?-1:1;
                if(order[index+delta] && move(instance,field.name,order[index+delta])) {
                    const scope=control.classList.contains('ib-config-list-handle')?instance.list:
                        instance.dt.table().container().querySelector('.dt-scroll-head') || instance.dt.table().header();
                    const handle=Array.from(scope.querySelectorAll('.ib-config-handle')).find(node=>
                        (node.dataset.fieldName || node.closest('[data-field-name]')?.dataset.fieldName)===field.name);
                    if(handle)handle.focus();
                }
            }
        },true));
        document.addEventListener('dragstart',event=>{
            const handle=event.target.closest && event.target.closest('.ib-config-handle');if(!handle)return;
            const control=handle.closest('[data-tableid]'),instance=instances.get(control.dataset.tableid);
            if(!instance || !instance.active || busy(instance)){event.preventDefault();return;}
            drag={instance,name:control.dataset.fieldName};event.stopPropagation();
            if(event.dataTransfer){event.dataTransfer.effectAllowed='move';event.dataTransfer.setData('text/plain',drag.name);}
        });
        function destination(event){
            const element=event.target.closest && event.target.closest('th[data-field-name],.ib-colvis-item');
            if(!element || !drag)return null;
            const fieldName=element.dataset.fieldName || element.querySelector('.toggle-vis')?.dataset.name;
            const tableId=element.closest('table')?.dataset.realid || element.closest('.toggle-vis-container')?.dataset.tableid;
            return tableId===drag.instance.id?fieldName:null;
        }
        document.addEventListener('dragover',event=>{if(destination(event)){event.preventDefault();if(event.dataTransfer)event.dataTransfer.dropEffect='move';}});
        document.addEventListener('drop',event=>{const name=destination(event);if(name){event.preventDefault();move(drag.instance,drag.name,name);}drag=null;});
        document.addEventListener('dragend',()=>{drag=null;});
        window.addEventListener('scroll',()=>instances.forEach(instance=>{if(instance.picker)instance.picker.hidden=true;}),true);
        $(document).on('init.dt.ibConfigurator',(_event,settings)=>attach(new $.fn.dataTable.Api(settings)));
        return {prepare, attach, toggle:dt=>{const instance=attach(dt);if(instance)setMode(instance,!instance.active);},
            current:dt=>{const instance=instances.get(dt.table().node().id);return instance?current(instance):[];},
            move:(dt,name,target)=>{const instance=instances.get(dt.table().node().id);return instance && move(instance,name,target);}};
    }
    return {reconcileOrder,bind};
});
