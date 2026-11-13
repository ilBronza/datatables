(function(root, factory) {
    const hierarchy = factory();
    if (typeof module === 'object' && module.exports) module.exports = hierarchy;
    root.ibDtRowHierarchy = hierarchy;
    if (root.jQuery && root.document) hierarchy.install(root.jQuery, root.document);
})(typeof window === 'undefined' ? globalThis : window, function() {
    'use strict';

    const installations = new WeakSet();
    const key = value => value === null || value === undefined || value === '' ? null : String(value);

    /** Build a forest in O(n), without recursion or a depth limit. */
    function build(entries, opened, matches, searching = false) {
        const nodes = new Map(), ids = new Map(), duplicates = new Set();
        for (const entry of entries) {
            const id = key(entry.id);
            if (id !== null && ids.has(id)) duplicates.add(id);
            ids.set(id, entry.index);
            const cell = entry.cell !== null && typeof entry.cell === 'object'
                ? entry.cell : {value: entry.cell};
            nodes.set(entry.index, {index: entry.index, id, parentId: key(cell.value),
                defaultOpened: cell.row_opened === true, children: [], parent: null, issue: null});
        }
        ids.delete(null);
        for (const node of nodes.values()) {
            node.stateKey = node.id === null || duplicates.has(node.id)
                ? 'index:' + node.index + ':' + node.id : 'id:' + node.id;
            node.opened = opened.has(node.stateKey) ? opened.get(node.stateKey) : node.defaultOpened;
            if (node.id === null) node.issue = 'missingId';
            else if (duplicates.has(node.id)) node.issue = 'duplicate';
            else if (node.parentId !== null) {
                if (!ids.has(node.parentId) || duplicates.has(node.parentId)) node.issue = 'orphan';
                else node.parent = nodes.get(ids.get(node.parentId));
            }
        }

        // Detach every member of a cycle, retaining valid descendants below it.
        const finished = new Set();
        for (const start of nodes.values()) {
            if (finished.has(start)) continue;
            const path = [], positions = new Map();
            let current = start;
            while (current && !finished.has(current) && !positions.has(current)) {
                positions.set(current, path.length); path.push(current); current = current.parent;
            }
            if (current && positions.has(current)) {
                for (let i = positions.get(current); i < path.length; i++) {
                    path[i].parent = null; path[i].issue = 'cycle';
                }
            }
            for (const node of path) finished.add(node);
        }

        const roots = [];
        for (const node of nodes.values()) {
            if (node.parent) node.parent.children.push(node);
            else roots.push(node);
        }
        const included = new Set(matches), forced = new Set();
        for (const index of matches) {
            let current = nodes.get(index)?.parent;
            while (current && !forced.has(current.index)) {
                included.add(current.index);
                if (searching) forced.add(current.index);
                // Also stop repeated ancestor walks when no filter is active.
                if (!searching) break;
                current = current.parent;
            }
        }

        const order = [], stack = roots.slice().reverse();
        while (stack.length) {
            const node = stack.pop();
            node.depth = node.parent ? node.parent.depth + 1 : 0;
            node.effectiveOpened = node.opened || forced.has(node.index);
            order.push(node);
            for (let i = node.children.length - 1; i >= 0; i--) stack.push(node.children[i]);
        }
        for (let i = order.length - 1; i >= 0; i--) {
            const node = order[i];
            node.total = 0;
            node.allOpened = node.effectiveOpened;
            for (const child of node.children) {
                node.total += 1 + child.total;
                if (child.children.length && !child.allOpened) node.allOpened = false;
            }
        }
        return {nodes, roots, included, forced, searching,
            filteredRoots: roots.filter(node => included.has(node.index))};
    }

    function page(graph, start, length) {
        const roots = graph.filteredRoots;
        const paged = length < 0 ? roots : roots.slice(start, start + length);
        const result = [], stack = paged.slice().reverse();
        while (stack.length) {
            const node = stack.pop();
            if (!graph.included.has(node.index)) continue;
            result.push(node.index);
            if (node.effectiveOpened) {
                for (let i = node.children.length - 1; i >= 0; i--) stack.push(node.children[i]);
            }
        }
        return result;
    }

    function toggle(graph, opened, index, branch) {
        const node = graph.nodes.get(index);
        if (!node || !node.children.length || graph.searching) return;
        const value = branch ? !node.allOpened : !node.opened;
        const stack = [node];
        while (stack.length) {
            const current = stack.pop();
            if (current.children.length) opened.set(current.stateKey, value);
            if (branch) for (const child of current.children) stack.push(child);
        }
    }

    function restore(settings) {
        const state = settings._ibDtHierarchy;
        if (!state?.snapshot) return;
        Object.assign(settings, state.snapshot);
        state.snapshot = null;
    }

    /** Native selectors see the actual visible branch page, including child rows.
     * Delegate through an original API instance, retaining DataTables' own selector
     * implementation and extension chain (Select, CSS, functions, column/cell queries).
     */
    function installPageSelectors(DataTable) {
        function withPage(context, enabled, callback) {
            const snapshots = [];
            if (enabled) {
                for (const settings of context) {
                    const state = settings._ibDtHierarchy;
                    if (!state?.graph) continue;
                    snapshots.push([settings, settings.aiDisplay, settings.aiDisplayMaster,
                        settings._iDisplayStart, settings._iDisplayLength]);
                    settings.aiDisplay = state.pageRows;
                    settings.aiDisplayMaster = state.pageRows;
                    settings._iDisplayStart = 0; settings._iDisplayLength = -1;
                    state.selecting = (state.selecting || 0) + 1;
                }
            }
            try { return callback(); }
            finally {
                for (const [settings, display, master, start, length] of snapshots) {
                    settings.aiDisplay = display; settings.aiDisplayMaster = master;
                    settings._iDisplayStart = start; settings._iDisplayLength = length;
                    settings._ibDtHierarchy.selecting--;
                }
            }
        }
        // Column consumers defer row selection until data(), nodes(), cache(), etc.
        const iterate = DataTable.Api.prototype.iterator;
        DataTable.Api.prototype.iterator = function(...args) {
            return withPage(this.context, this.selector?.opts?.page === 'current', () => iterate.apply(this, args));
        };
        DataTable.ext.selector.row.push((settings, options, indexes) => {
            const state = settings._ibDtHierarchy;
            return state?.graph && options.page === 'current'
                ? indexes.filter(index => state.pageSet.has(index)) : indexes;
        });
        const native = new DataTable.Api();
        const methods = {};
        for (const name of ['rows', 'row', 'columns', 'column', 'cells', 'cell']) methods[name] = native[name];
        for (const name of Object.keys(methods)) {
            DataTable.Api.register(name + '()', function(...args) {
                const previousContext = native.context, previousSelector = native.selector;
                const previousData = native.toArray();
                native.context = this.context; native.selector = this.selector;
                native.length = 0;
                for (const item of this.toArray()) native.push(item);
                try { return withPage(this.context, args.some(arg => arg && arg.page === 'current'), () => methods[name](...args)); }
                finally {
                    native.context = previousContext; native.selector = previousSelector;
                    native.length = 0;
                    for (const item of previousData) native.push(item);
                }
            });
        }
    }

    function decorate(settings, row, index) {
        const state = settings._ibDtHierarchy;
        if (state.flat) {
            const cell = settings.aoData[index].anCells[settings.aoColumns.indexOf(state.column)];
            cell.replaceChildren();
            delete row.dataset.ibDtTreeDepth;
            row.classList.remove('ib-dt-tree-context');
            return;
        }
        const node = state.graph?.nodes.get(index);
        if (!node) return;
        const cell = settings.aoData[index].anCells[settings.aoColumns.indexOf(state.column)];
        let controls = cell.querySelector('.ib-dt-tree-controls');
        if (!controls) {
            controls = cell.ownerDocument.createElement('span');
            controls.className = 'ib-dt-tree-controls'; cell.replaceChildren(controls);
        }
        controls.replaceChildren();
        controls.style.setProperty('--ib-dt-tree-depth', Math.min(node.depth, 12));
        row.dataset.ibDtTreeDepth = node.depth;
        row.classList.toggle('ib-dt-tree-context', !state.matches.has(index));
        if (node.children.length) {
            for (const branch of [false, true]) {
                const open = branch ? node.allOpened : node.effectiveOpened;
                const button = cell.ownerDocument.createElement('button');
                button.type = 'button'; button.className = 'ib-dt-tree-toggle';
                button.dataset.ibDtTreeAction = branch ? 'branch' : 'children';
                button.dataset.ibDtTreeIndex = index;
                const label = state.labels[branch ? (open ? 'closeBranch' : 'openBranch')
                    : (open ? 'closeChildren' : 'openChildren')];
                const count = branch ? node.total : node.children.length;
                button.title = state.graph.searching ? state.labels.searchOpened : label + ' (' + count + ')';
                button.setAttribute('aria-label', button.title);
                button.setAttribute('aria-expanded', String(open));
                button.disabled = state.graph.searching;
                const icon = cell.ownerDocument.createElement('i');
                icon.className = 'fa-solid fa-' + (branch ? 'angles-' : 'chevron-') + (open ? 'down' : 'right');
                icon.setAttribute('aria-hidden', 'true'); button.appendChild(icon); controls.appendChild(button);
            }
        }
        if (node.issue) {
            const issue = cell.ownerDocument.createElement('span');
            issue.className = 'ib-dt-tree-issue'; issue.title = state.labels[node.issue];
            issue.setAttribute('role', 'img'); issue.setAttribute('aria-label', issue.title);
            issue.textContent = '⚠'; controls.appendChild(issue);
        }
        controls.title = state.matches.has(index) ? '' : state.labels.context;
    }

    function prepare(settings) {
        restore(settings);
        const state = settings._ibDtHierarchy, api = new state.DataTable.Api(settings);
        if (state.flat) {
            state.graph = null;
            const isRecord = index => !state.column.fnGetData(settings.aoData[index]._aData)?.row_group;
            // A native header click sorts the previous display without rerunning filters.
            settings.aiDisplay = settings.aiDisplay.filter(isRecord);
            state.flatTotal = settings.aiDisplayMaster.filter(isRecord).length;
            return;
        }
        const columnIndex = settings.aoColumns.indexOf(state.column);
        const matched = settings.aiDisplay.slice(), matches = new Set(matched);
        // Preserve the package's null-last ordering, including with active filters.
        let position = 0;
        const order = settings.aiDisplayMaster.map(index => matches.has(index) ? matched[position++] : index);
        const entries = order.map(index => ({index, id: settings.rowIdFn(settings.aoData[index]._aData),
            cell: api.cell(index, columnIndex).data()}));
        const searching = matched.length !== order.length || !!settings.oPreviousSearch.search
            || settings.aoPreSearchCols.some(search => !!search.search);
        state.matches = matches;
        state.graph = build(entries, state.opened, matches, searching);
        const activeKeys = new Set(Array.from(state.graph.nodes.values(), node => node.stateKey));
        for (const id of state.opened.keys()) if (!activeKeys.has(id)) state.opened.delete(id);
        const length = settings.oFeatures.bPaginate ? settings._iDisplayLength : -1;
        const count = state.graph.filteredRoots.length;
        settings._iDisplayStart = length < 0 ? 0 : Math.min(settings._iDisplayStart,
            Math.max(0, Math.ceil(count / length) - 1) * length);
        state.pageRows = page(state.graph, settings._iDisplayStart, length);
        state.pageSet = new Set(state.pageRows);
        state.snapshot = {aiDisplay: settings.aiDisplay,
            _iDisplayStart: settings._iDisplayStart, _iDisplayLength: settings._iDisplayLength};
        settings.aiDisplay = state.pageRows;
        settings._iDisplayStart = 0; settings._iDisplayLength = -1;
        // A cancelled preDraw event never reaches the header callback.
        Promise.resolve().then(() => restore(settings));
    }

    /** Incremental editor/broadcast patches normally don't draw the table. */
    function refresh(api, index) {
        const settings = api.settings()[0], state = settings?._ibDtHierarchy;
        if (!state?.graph) return;
        const node = state.graph.nodes.get(index);
        if (!node || !settings.aoData[index]) return;
        const cell = api.cell(index, settings.aoColumns.indexOf(state.column)).data();
        const value = cell !== null && typeof cell === 'object' ? cell : {value:cell};
        if (key(value.value) !== node.parentId || (value.row_opened === true) !== node.defaultOpened
            || key(settings.rowIdFn(settings.aoData[index]._aData)) !== node.id) {
            const document = settings.nTable.ownerDocument, active = document.activeElement;
            const selection = active && typeof active.selectionStart === 'number'
                ? [active.selectionStart, active.selectionEnd] : null;
            api.draw(false);
            if (active && document.contains(active)) {
                active.focus();
                if (selection) active.setSelectionRange(...selection);
            }
        }
        else if (settings.aoData[index].nTr) decorate(settings, settings.aoData[index].nTr, index);
    }

    function attach($, DataTable, settings) {
        const columns = settings.aoColumns.filter(column => column.ibDtRowParent);
        if (!columns.length || settings._ibDtHierarchy) return;
        if (columns.length !== 1) throw new Error('Only one row_parent_id field is allowed per table.');
        if (settings.oFeatures.bServerSide) throw new Error('row_parent_id requires the complete client-side dataset.');
        // RowReorder normally initializes on init.dt, after this preInit handler.
        if (settings.oInit.rowReorder || DataTable.defaults.rowReorder) {
            settings.oInit.rowReorder = Object.assign({}, settings.oInit.rowReorder, {enable:false});
        }
        const original = {rowIdFn: settings.rowIdFn, fnRecordsDisplay: settings.fnRecordsDisplay, fnRecordsTotal: settings.fnRecordsTotal,
            fnDisplayEnd: settings.fnDisplayEnd};
        const state = settings._ibDtHierarchy = {DataTable, column: columns[0], labels: columns[0].ibDtRowParent,
            opened: new Map(), graph: null, pageRows: [], snapshot: null};
        // ColReorder moves array data as well as columns. Retain the identity column
        // object so numeric rowId references continue to identify the original field.
        if (typeof settings.rowId === 'number' || /^\d+$/.test(settings.rowId)) {
            const idColumn = settings.aoColumns[Number(settings.rowId)];
            if (idColumn) settings.rowIdFn = data => idColumn.fnGetData(data, undefined);
        }
        settings.fnRecordsDisplay = function() { return state.graph ? state.graph.filteredRoots.length : original.fnRecordsDisplay.call(this); };
        settings.fnRecordsTotal = function() {
            if (state.flat) return state.flatTotal;
            return state.graph ? state.graph.roots.length : original.fnRecordsTotal.call(this);
        };
        settings.fnDisplayEnd = function() {
            if (state.snapshot || state.selecting) return state.pageRows.length;
            if (!state.graph) return original.fnDisplayEnd.call(this);
            return !this.oFeatures.bPaginate || this._iDisplayLength < 0 ? state.graph.filteredRoots.length
                : Math.min(this._iDisplayStart + this._iDisplayLength, state.graph.filteredRoots.length);
        };

        const before = settings.aoPreDrawCallback.slice();
        settings.aoPreDrawCallback = [function() {
            restore(settings);
            for (let i = before.length - 1; i >= 0; i--) {
                if (before[i].apply(this, arguments) === false) return false;
            }
            prepare(settings);
        }];
        // Restore native matches before summaries, exports, info, paging or application callbacks.
        settings.aoHeaderCallback.push(() => restore(settings));
        settings.aoRowCallback.push((row, data, display, full, index) => decorate(settings, row, index));
        const $table = $(settings.nTable);
        $table.addClass('ib-dt-hierarchy');
        if (state.column.ibDtFlatOnOrder) {
            const api = new DataTable.Api(settings);
            // _fnSort emits order before DataTables applies its search and paging.
            $table.on('order.dt.ibDtRowHierarchy', (event, source) => {
                if (source !== settings) return;
                const order = api.order();
                state.flat = order.length !== 1 || settings.aoColumns[order[0][0]] !== state.column;
                if (state.flat) state.graph = null;
                $table.toggleClass('ib-dt-hierarchy', !state.flat);
                if (api.button) api.button('ibDtHierarchy:name').enable(state.flat);
            });
        }
        $table.on('columns-reordered.dt.ibDtRowHierarchy', () => new DataTable.Api(settings).draw(false));
        $table.on('info.dt.ibDtRowHierarchy', (event, source, element) => {
            if (source !== settings || !state.graph) return;
            const count = state.graph.filteredRoots.length, total = state.graph.roots.length;
            const start = settings._iDisplayStart;
            const end = settings.fnDisplayEnd();
            const text = count ? state.labels.info + (count !== total ? ' ' + state.labels.infoFiltered : '') : state.labels.infoEmpty;
            element.textContent = text.replace(/_START_/g, settings.fnFormatNumber(start + 1))
                .replace(/_END_/g, settings.fnFormatNumber(end)).replace(/_TOTAL_/g, settings.fnFormatNumber(count))
                .replace(/_MAX_/g, settings.fnFormatNumber(total));
        });
        $table.on('click.ibDtRowHierarchy', '.ib-dt-tree-toggle', function(event) {
            event.preventDefault(); event.stopImmediatePropagation();
            if (this.disabled) return;
            const index = Number(this.dataset.ibDtTreeIndex), action = this.dataset.ibDtTreeAction;
            toggle(state.graph, state.opened, index, action === 'branch');
            const id = state.graph.nodes.get(index).id;
            const api = new DataTable.Api(settings);
            api.draw(false);
            // Restore keyboard focus after replacing the controls during draw.
            const node = Array.from(state.graph.nodes.values()).find(node => node.id === id);
            if (node) settings.aoData[node.index].nTr?.querySelector('[data-ib-dt-tree-action="' + action + '"]')?.focus();
        });
        settings.aoDestroyCallback.push(() => {
            restore(settings); Object.assign(settings, original);
            $table.off('.ibDtRowHierarchy').removeClass('ib-dt-hierarchy');
            state.opened.clear(); delete settings._ibDtHierarchy;
        });
        // Manual drag ordering cannot move a child away from its parent.
        const api = new DataTable.Api(settings);
        if (api.rowReorder) api.rowReorder.disable();
    }

    function install($, document) {
        const DataTable = $.fn.dataTable;
        if (!DataTable || installations.has(DataTable)) return;
        installations.add(DataTable);
        installPageSelectors(DataTable);
        $(document).on('preInit.dt.ibDtRowHierarchy', (event, settings) => attach($, DataTable, settings));
    }

    function restoreOrder(api) {
        const settings = api.settings()[0];
        if (!settings?._ibDtHierarchy?.column.ibDtFlatOnOrder) return;
        if (typeof api.cell.blur === 'function') api.cell.blur();
        api.order([[settings.aoColumns.indexOf(settings._ibDtHierarchy.column), 'asc']]).draw();
    }

    return {build, page, toggle, install, refresh, restoreOrder};
});
