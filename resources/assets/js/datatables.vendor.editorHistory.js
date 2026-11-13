(function (window, document) {
    'use strict';

    const editors = window.IlBronza.datatables.editors;
    const helpers = editors.helpers;
    const states = new WeakMap();
    const limit = 100;
    const clone = value => helpers.clone(value);
    const same = (a, b, change) => change?.valueType === 'decimal' ? helpers.numbers.equal(a, b) : helpers.same(a, b);
    const displayedSame = (a, b) => helpers.displayedSame(a, b);
    const hasId = value => helpers.hasId(value);
    const api = table => helpers.table(table);

    function stateFor(table) {
        return states.get(editors.forTable(table).history);
    }

    function status(state) {
        return { undo: state.undo.length, redo: state.redo.length,
            pending: state.pending, busy: state.busy, lastError: state.lastError };
    }

    function changed(state) {
        state.table.table().node().dispatchEvent(new window.CustomEvent('ib:dt-editor-history', { detail: status(state) }));
    }

    const valueOf = (raw, format) => helpers.valueOf(raw, format);
    const fieldKey = change => JSON.stringify([change.rowId, change.column, change.field, change.targetId]);

    function describe(element) {
        const editor = editors.forInput(element);
        return editor.managed ? editor.describe() : null;
    }

    function operation(state, batch, url) {
        const result = { state, batch, url, tokens: [], closed: true };
        state.operations.push(result);
        return result;
    }

    function flush(state) {
        while (state.operations.length && state.operations[0].closed
            && state.operations[0].tokens.every(token => token.finished)) {
            const op = state.operations.shift();
            const entries = new Map();
            for (const token of op.tokens) {
                if (!token.ok) continue;
                for (const change of token.changes) {
                    const key = fieldKey(change);
                    const before = change.overlap && state.confirmed.has(key)
                        ? clone(state.confirmed.get(key)) : change.before;
                    state.confirmed.set(key, clone(change.after));
                    if (same(before, change.after, change)) continue;
                    const previous = entries.get(key);
                    entries.set(key, { ...change, before: previous ? previous.before : before });
                }
            }
            const changes = Array.from(entries.values()).filter(change => !same(change.before, change.after, change));
            if (changes.length) {
                const owner = changes.find(change => change.url === op.url);
                state.undo.push({ batch: op.batch, url: op.url, routeOwner: owner ? fieldKey(owner) : null, changes });
                if (state.undo.length > limit) state.undo.shift();
                state.redo.length = 0;
                state.lastError = null;
            }
        }
        const retained = new Set();
        for (const entry of [...state.undo, ...state.redo])
            for (const change of entry.changes) retained.add(fieldKey(change));
        for (const op of state.operations)
            for (const token of op.tokens)
                for (const change of token.changes) retained.add(fieldKey(change));
        for (const key of state.confirmed.keys()) if (!retained.has(key)) state.confirmed.delete(key);
        changed(state);
    }

    function beginGroup(table) {
        table = api(table);
        if (!table) return null;
        const op = operation(stateFor(table), false, null);
        op.closed = false;
        return op;
    }

    function endGroup(group) {
        if (!group) return;
        group.closed = true;
        flush(group.state);
    }

    function capture(state, params, data, url, group) {
        if (!data || !data['ib-editor'] || params?.ibDtHistoryReplay) return null;
        const submitted = params.inlineEditSubmittedFields || [{ element: params.target, value: data.value }];
        const described = submitted.map(item => {
            const change = describe(item.element);
            return change && (!data.field || data.field === change.field) && { ...change, submitted: clone(item.value) };
        }).filter(Boolean);
        if (!described.length) return null;
        if (state.busy) return null;
        const owned = described.filter(change => change.table.settings()[0] === state.table.settings()[0]);
        if (!owned.length) return null;
        const op = group && group.state === state ? group : operation(state, !!data['ib-editor-batch'], url);
        const changes = owned.map(change => {
            const key = fieldKey(change);
            const baseline = state.confirmed.get(key);
            const before = state.confirmed.has(key) && displayedSame(change.original, baseline)
                ? clone(baseline) : change.before;
            const overlap = (state.active.get(key) || 0) > 0;
            state.active.set(key, (state.active.get(key) || 0) + 1);
            // No DOM nodes, row indexes, or live Eloquent/DT data are retained.
            const { table, element, original, ...saved } = change;
            const requestData = {};
            for (const name of ['fieldExtraData', 'fieldData'])
                if (data[name] !== undefined) requestData[name] = clone(data[name]);
            return { ...saved, before, overlap, requestData };
        });
        const token = { state, op, changes, finished: false, ok: false };
        op.tokens.push(token);
        state.pending++;
        changed(state);
        return token;
    }

    function locate(table, change, rowData) {
        return editors.forTable(table).locate(change, rowData);
    }

    function freshRow(table, rowId) {
        return editors.forTable(table).readRow(rowId);
    }

    function responseValue(response, change, single) {
        if (single && Object.prototype.hasOwnProperty.call(response, 'value')) return { value: response.value };
        if (Object.prototype.hasOwnProperty.call(response, change.field)) return { value: response[change.field] };
        if (response.fields && !Array.isArray(response.fields) && Object.prototype.hasOwnProperty.call(response.fields, change.field))
            return { value: response.fields[change.field] };
        return null;
    }

    async function complete(token, response, failed = false) {
        if (!token || token.finished || token.settling) return;
        token.settling = true;
        response = response && typeof response === 'object' ? response : {};
        try {
            if (!failed && response.success !== false) {
                const rows = new Map();
                for (const change of token.changes) {
                    const returned = responseValue(response, change, !token.op.batch && token.changes.length === 1);
                    if (returned) change.after = clone(returned.value);
                    else {
                        if (!rows.has(change.rowId)) rows.set(change.rowId, await freshRow(token.state.table, change.rowId));
                        const data = rows.get(change.rowId);
                        change.after = data === null ? clone(change.submitted)
                            : valueOf(locate(token.state.table, change, data).raw, change.format);
                    }
                }
                token.ok = true;
            }
        }
        catch (error) { token.state.lastError = 'Salvataggio riuscito, ma cronologia non disponibile: ' + error.message; }
        finally {
            token.finished = true;
            token.state.pending--;
            for (const change of token.changes) {
                const key = fieldKey(change);
                const count = (token.state.active.get(key) || 1) - 1;
                count ? token.state.active.set(key, count) : token.state.active.delete(key);
            }
            flush(token.state);
        }
    }

    function dirty(element) {
        if (typeof window.editorFieldHasChanged === 'function') return window.editorFieldHasChanged(element);
        return String(element.value ?? '') !== String(element.getAttribute('data-originalvalue') ?? '');
    }

    function hasDrafts(table) {
        const node = table.table().node();
        return Array.from(node.querySelectorAll('.ib-dt-editor-v2')).some(dirty)
            || !!node.querySelector('.ib-datatable-inline-edit-child .editorchanged');
    }

    function resolve(table, change, data, locked) {
        const context = locate(table, change, data);
        const route = context.settings.aoColumns[context.index].ibDtEditor;
        if ((change.hasColumnRoute && !route) || route?.history === false)
            throw new Error('La colonna non consente più questa modifica.');
        const header = context.settings.aoHeader.map(row => row[context.index]?.cell)
            .find(cell => cell?.hasAttribute('data-field'));
        if (route && header?.getAttribute('data-field') !== change.field)
            throw new Error('Il parametro della colonna è cambiato.');
        const targetId = route?.target === 'element' ? context.raw?.targetId : context.settings.rowIdFn(context.data);
        if (!hasId(targetId) || String(targetId) !== change.targetId)
            throw new Error('Il destinatario della modifica è cambiato.');
        const input = table.cell(context.rowIndex, context.index).node()?.querySelector('.ib-dt-editor-v2');
        if (context.raw?.disabled === true || (input && input.disabled && !locked.includes(input)))
            throw new Error('Il campo non è attualmente modificabile.');
        if (!route) return { ...context, url: change.url, input };
        const custom = route.updateUrlFromCell ? context.raw?.updateUrl : null;
        const url = helpers.resolveUrl(route, targetId, custom);
        return { ...context, url, input };
    }

    function notifyError(state, error) {
        state.lastError = error.message || 'Impossibile completare undo/redo.';
        if (typeof window.addDangerNotification === 'function') window.addDangerNotification(state.lastError);
    }

    async function replay(table, direction) {
        table = api(table);
        if (!table) return false;
        const state = stateFor(table);
        const source = state[direction];
        const destination = state[direction === 'undo' ? 'redo' : 'undo'];
        if (!source.length || state.busy || state.pending) return false;
        if (hasDrafts(table)) {
            notifyError(state, new Error('Salva o annulla le modifiche in corso prima di usare undo/redo.'));
            changed(state);
            return false;
        }
        const entry = source[source.length - 1];
        const completed = [];
        const fresh = new Map();
        const snapshots = new Map();
        const locked = [];
        let focus = null;
        try { focus = describe(document.activeElement); } catch (_) {}
        state.busy = true;
        state.lastError = null;
        // Lock before the asynchronous verification, as well as during POSTs.
        for (const input of table.table().node().querySelectorAll('.ib-dt-editor-v2:not(:disabled)')) {
            locked.push(input);
            input.disabled = true;
        }
        changed(state);
        try {
            // Read every affected row before issuing any inverse write.
            for (const change of entry.changes) {
                if (!fresh.has(change.rowId)) fresh.set(change.rowId, await freshRow(table, change.rowId));
                const context = resolve(table, change, fresh.get(change.rowId), locked);
                const expected = direction === 'undo' ? change.after : change.before;
                if (!same(valueOf(context.raw, change.format), expected, change))
                    throw new Error('Il valore è cambiato dopo il salvataggio: undo/redo interrotto.');
                if (!snapshots.has(change.rowId) && typeof window.ibDtCaptureRowRevisionSnapshot === 'function')
                    snapshots.set(change.rowId, window.ibDtCaptureRowRevisionSnapshot(table, table.row(context.rowIndex)));
            }
            const changes = direction === 'undo' ? entry.changes.slice().reverse() : entry.changes.slice();
            const groups = entry.batch ? [changes] : changes.map(change => [change]);
            for (const group of groups) {
                const contexts = group.map(change => {
                    // Input disabling is our temporary lock, not a permission change.
                    const context = locate(table, change, fresh.get(change.rowId));
                    const route = context.settings.aoColumns[context.index].ibDtEditor;
                    const custom = route?.updateUrlFromCell ? context.raw?.updateUrl : null;
                    const url = route ? helpers.resolveUrl(route, change.targetId, custom) : change.url;
                    return { ...context, url };
                });
                const payload = { ...clone(group[0].requestData), 'ib-editor': true, _method: 'PUT' };
                for (const change of group) {
                    const context = locate(table, change, fresh.get(change.rowId));
                    const input = table.cell(context.rowIndex, context.index).node()?.querySelector('.ib-dt-numeric-editor');
                    if (input && !editors.forInput(input).validateValue(direction === 'undo' ? change.before : change.after))
                        throw new Error(input.validationMessage);
                }
                if (entry.batch) {
                    payload['ib-editor-batch'] = true;
                    payload.fields = Object.fromEntries(group.map(change => [change.field, direction === 'undo' ? change.before : change.after]));
                }
                else {
                    payload.field = group[0].field;
                    payload.value = direction === 'undo' ? group[0].before : group[0].after;
                }
                const rowNode = table.row(contexts[0].rowIndex).node();
                const ownerIndex = entry.routeOwner ? group.findIndex(change => fieldKey(change) === entry.routeOwner) : -1;
                const url = entry.batch ? (ownerIndex >= 0 ? contexts[ownerIndex].url : entry.url || contexts[0].url) : contexts[0].url;
                const response = await window.jQuery.ajax({ url,
                    type: 'POST', dataType: 'json', data: payload,
                    headers: typeof window.ibGetDatatableBroadcastHeaders === 'function' ? window.ibGetDatatableBroadcastHeaders(rowNode) : {} });
                if (response && response.success === false) throw new Error(response.message || 'Salvataggio undo/redo rifiutato.');
                // The POST succeeded even if a later verification/refresh fails.
                for (const change of group) {
                    completed.push(change);
                }
                fresh.delete(group[0].rowId);
                const rowData = await freshRow(table, group[0].rowId);
                fresh.set(group[0].rowId, rowData);
                for (let index = 0; index < group.length; index++) {
                    const change = group[index];
                    const returned = responseValue(response || {}, change, !entry.batch);
                    const requested = direction === 'undo' ? change.before : change.after;
                    const actual = rowData === null ? clone(returned ? returned.value : requested)
                        : valueOf(locate(table, change, rowData).raw, change.format);
                    change[direction === 'undo' ? 'before' : 'after'] = actual;
                    state.confirmed.set(fieldKey(change), clone(actual));
                    if (rowData === null) {
                        const cell = table.cell(contexts[index].rowIndex, contexts[index].index);
                        cell.data(helpers.updateCell(cell.data(), actual, change.format, change.targetId));
                    }
                }
            }
            return true;
        }
        catch (error) { notifyError(state, error); return false; }
        finally {
            if (completed.length) {
                source.pop();
                const remaining = entry.changes.filter(change => !completed.includes(change));
                if (remaining.length) source.push({ ...entry, changes: remaining });
                destination.push({ ...entry, changes: completed });
                if (destination.length > limit) destination.shift();
            }
            for (const [rowId, rowData] of fresh) {
                if (!rowData || !completed.some(change => change.rowId === rowId)) continue;
                try {
                    const context = locate(table, completed.find(change => change.rowId === rowId));
                    const row = table.row(context.rowIndex);
                    if (typeof window.ibDtPatchRenderedRow === 'function')
                        window.ibDtPatchRenderedRow(table, row, rowData, snapshots.get(rowId), { refreshActiveTargetCell: true,
                            target: table.cell(context.rowIndex, context.index).node()?.querySelector('.ib-dt-editor-v2') });
                    else row.data(rowData).invalidate();
                }
                catch (error) { notifyError(state, error); }
            }
            for (const input of locked) if (input.isConnected) input.disabled = false;
            state.busy = false;
            if (focus && (document.activeElement === document.body || locked.includes(document.activeElement))) {
                try {
                    const context = locate(table, focus);
                    table.cell(context.rowIndex, context.index).node()?.querySelector('.ib-dt-editor-v2:not(:disabled)')?.focus();
                } catch (_) {}
            }
            changed(state);
        }
    }

    class EditorHistory {
        constructor(controller) {
            this.controller = controller;
            states.set(this, { table: controller.table, undo: [], redo: [], operations: [], pending: 0, busy: false,
                confirmed: new Map(), active: new Map(), lastError: null });
        }
        capture(params, data, url, group) { return capture(states.get(this), params, data, url, group); }
        complete(token, response, failed = false) {
            if (token?.state === states.get(this)) return complete(token, response, failed);
        }
        beginGroup() { return beginGroup(this.controller.table); }
        endGroup(group) { if (group?.state === states.get(this)) return endGroup(group); }
        undo() { return replay(this.controller.table, 'undo'); }
        redo() { return replay(this.controller.table, 'redo'); }
        state() { return status(states.get(this)); }
        clear() {
            const state = states.get(this);
            if (state.pending || state.busy) return false;
            state.undo.length = state.redo.length = 0;
            state.confirmed.clear();
            changed(state);
            return true;
        }
    }
    editors.History = EditorHistory;

    // Shared AJAX/bulk code can use this facade without owning table state.
    editors.history = {
        capture(params, data, url, group) {
            if (!data || !data['ib-editor'] || params?.ibDtHistoryReplay) return null;
            const submitted = params.inlineEditSubmittedFields || [{ element: params.target }];
            for (const item of submitted) {
                const editor = editors.forInput(item.element);
                if (editor.managed && editor.getController())
                    return editor.getController().history.capture(params, data, url, group);
            }
            return null;
        },
        complete(token, response, failed = false) {
            if (token) return editors.forTable(token.state.table).history.complete(token, response, failed);
        },
        beginGroup(table) { return editors.forTable(table)?.history.beginGroup() ?? null; },
        endGroup(group) { if (group) editors.forTable(group.state.table).history.endGroup(group); },
        undo(table) { return editors.forTable(table)?.history.undo() ?? Promise.resolve(false); },
        redo(table) { return editors.forTable(table)?.history.redo() ?? Promise.resolve(false); },
        state(table) { return editors.forTable(table)?.history.state() ?? null; },
        clear(table) { return editors.forTable(table)?.history.clear() ?? false; },
    };

    document.addEventListener('keydown', function (event) {
        if (event.defaultPrevented || event.isComposing || event.altKey || (!event.ctrlKey && !event.metaKey)) return;
        const key = String(event.key).toLowerCase();
        const direction = key === 'z' ? (event.shiftKey ? 'redo' : 'undo') : key === 'y' && event.ctrlKey ? 'redo' : null;
        if (!direction) return;
        const target = event.target;
        const field = target.closest?.('.ib-dt-editor-v2');
        if (target.isContentEditable || (target.matches?.('input, textarea, select') && !field) || (field && dirty(field))) return;
        const table = api(target.closest?.('table.dataTable') || target.closest?.('.dt-container')?.querySelector('table.dataTable'));
        if (!table) return;
        const state = stateFor(table);
        if (!state[direction].length || state.pending || state.busy) return;
        event.preventDefault();
        void replay(table, direction);
    });
})(window, document);
