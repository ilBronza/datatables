const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const buttonSource = fs.readFileSync('resources/assets/js/datatables.vendor.ajaxButton.min.js', 'utf8');
const ajaxSource = fs.readFileSync('resources/assets/js/datatables.vendor.ajaxCall.min.js', 'utf8');

function extract(source, start, end)
{
    const first = source.indexOf(start);
    const last = source.indexOf(end, first);
    assert.ok(first >= 0 && last > first);
    return source.slice(first, last);
}

function boot({ reloadTable = true, otherDirty = false } = {})
{
    const calls = [];
    const parent = { classes: new Set(), fields: [] };
    const child = { classes: new Set(['ib-datatable-inline-edit-child']), fields: [], parent };
    const field = (name, value, dirty = true) => ({
        row: child, value, original: 'old', data: { field: name, ibDirtyTracked: dirty },
        classes: new Set(['ib-datatable-inline-edit-field', ...(dirty ? ['editorchanged', 'ib-editor-dirty'] : [])]),
    });
    child.fields = [field('sellable_id', 'new-sellable'), field('sellable_supplier_id', 'new-operator')];
    const other = field('other_row_field', 'unsaved', otherDirty);
    const button = { row: child, data: {}, classes: new Set(['ib-datatable-inline-edit-save']) };
    const cancel = { row: child, classes: new Set(['ib-datatable-inline-edit-cancel']) };
    child.cancel = cancel;
    parent.child = child;

    function wrap(input)
    {
        const nodes = Array.isArray(input) ? input : (input ? [input] : []);
        const node = nodes[0];
        return {
            ...nodes, length: nodes.length,
            closest(selector) { return wrap(selector === 'tr' ? node?.row : null); },
            next() { return wrap(node?.child); },
            hasClass(name) { return !! node?.classes?.has(name); },
            find(selector) {
                if (selector === '.ib-datatable-inline-edit-cancel') return wrap(node?.cancel);
                return wrap((node?.fields || []).filter(item => item.classes.has(selector.slice(1))));
            },
            filter(selector) {
                return wrap(nodes.filter(item => typeof selector === 'function'
                    ? selector.call(item) : item.classes.has(selector.slice(1))));
            },
            each(callback) { nodes.forEach(item => callback.call(item)); return this; },
            first() { return wrap(nodes.slice(0, 1)); },
            data(name, value) {
                if (arguments.length === 1) return node?.data?.[name];
                (node.data ??= {})[name] = value;
                return this;
            },
            toggleClass(names, enabled) {
                for (const item of nodes)
                    for (const name of names.split(' '))
                        enabled ? item.classes.add(name) : item.classes.delete(name);
                return this;
            },
            removeClass() { return this; },
        };
    }

    let params;
    let request;
    const $ = wrap;
    $.ajax = options => { request = options; };
    const hasDirty = () => other.classes.has('editorchanged')
        || (! child.closed && child.fields.some(item => item.classes.has('editorchanged')));
    const window = {
        __getTH() { return { data() { return { reloadTable }; } }; },
        __ibGetEditorSubmitValue(element) { return element.value; },
        __ibGetEditorInputType() { return 'select'; },
        __ibEditorValuesEqual(a, b) { return JSON.stringify(a) === JSON.stringify(b); },
        __ibSetEditorOriginalValue(element, value) { element.original = value; },
        __ibUnmarkFieldDirty(element) { element.data.ibDirtyTracked = false; },
        __ibTogglePerTableSaveButton() {},
        cancelDatatableInlineEditRow(target) {
            assert.equal(target, cancel);
            assert.equal(child.fields.some(item => item.classes.has('editorchanged')), false);
            calls.push('close');
            child.closed = true;
            button.row = parent;
        },
        __ibBeginDtEditorSave() {},
        __ibCompleteDtEditorSave() {},
        __ibClearDtDirtyGuardSaving() {},
        ibDtBeginLocalRowMutationForTarget() { return null; },
        ibGetDatatableBroadcastHeaders() { return {}; },
        dtRefreshFieldsList() {},
        __removeEditorSaveClasses() {},
        __reloadTable() { calls.push(hasDirty() ? 'reload-with-dirty' : 'reload-clean'); },
        __refreshRow() { calls.push('refresh-row'); },
        ibDtEditorTargetRequiresErrorReload() { return false; },
        ibCallAjax(value) {
            params = value;
            window.__ibCallAjax('/inline-edit', 'POST', value.data, value);
        },
    };
    const context = {
        window, $, console: { log() {} },
        __manageSpinnerRemoval() {},
        __showEditorSavedFeedbackForTarget() {},
        __displayResponseErrors() { calls.push('error'); },
    };
    vm.runInNewContext(extract(buttonSource,
        'window.saveDatatableInlineEditRow = function', 'window.__ibRestoreDatatableInlineEditButton = function'), context);
    vm.runInNewContext(extract(ajaxSource,
        'window.__ibCallAjax = function', 'function ibDtWillRefreshRowAfterSave'), context);

    return {
        window, child, button, other, calls,
        save() { window.saveDatatableInlineEditRow(button); },
        succeed(response = { success: true, action: reloadTable ? 'reloadTable' : 'refreshRow' }) { request.success(response); },
        fail() { request.error({ status: 422 }); },
        params() { return params; },
    };
}

test('batch success clears saved fields and closes the editor before the configured table reload', () => {
    const runtime = boot();
    runtime.save();
    assert.equal(runtime.child.fields[0].classes.has('editorchanged'), true);
    assert.equal(runtime.params().data.fieldExtraData.ajaxextradata.reloadTable, true);

    runtime.succeed();

    assert.deepEqual(runtime.calls, ['close', 'reload-clean']);
    for (const field of runtime.child.fields)
    {
        assert.equal(field.original, field.value);
        assert.equal(field.classes.has('editorchanged'), false);
        assert.equal(field.classes.has('ib-editor-dirty'), false);
        assert.equal(field.data.ibDirtyTracked, false);
    }
});

test('without table reload the editor is closed before refreshing only the saved row', () => {
    const runtime = boot({ reloadTable: false });
    runtime.save();
    runtime.succeed();
    assert.deepEqual(runtime.calls, ['close', 'refresh-row']);
});

test('validation errors leave the inline editor open and preserve its dirty values', () => {
    const runtime = boot();
    runtime.save();
    runtime.fail();
    assert.deepEqual(runtime.calls, ['error']);
    assert.equal(runtime.child.closed, undefined);
    assert.equal(runtime.child.fields[0].original, 'old');
    assert.equal(runtime.child.fields[0].value, 'new-sellable');
    assert.equal(runtime.child.fields[0].classes.has('editorchanged'), true);
});

test('other rows remain dirty and protected by the table guard', () => {
    const runtime = boot({ otherDirty: true });
    runtime.save();
    runtime.succeed();
    assert.deepEqual(runtime.calls, ['close', 'reload-with-dirty']);
    assert.equal(runtime.other.classes.has('editorchanged'), true);
    assert.equal(runtime.other.data.ibDirtyTracked, true);
});

test('edits made during the request are not mistaken for saved values', () => {
    const runtime = boot();
    runtime.save();
    // Even reverting to the old baseline while saving becomes an unsaved edit
    // once the server has committed the submitted value.
    runtime.child.fields[0].value = 'old';
    runtime.child.fields[0].classes.delete('editorchanged');
    runtime.succeed();
    assert.deepEqual(runtime.calls, ['reload-with-dirty']);
    assert.equal(runtime.child.closed, undefined);
    assert.equal(runtime.child.fields[0].original, 'new-sellable');
    assert.equal(runtime.child.fields[0].classes.has('editorchanged'), true);
});

test('hidden fields omitted from the submitted batch are not cleared or discarded', () => {
    const runtime = boot();
    const field = runtime.child.fields[1];
    field.hidden = true;
    runtime.save();
    assert.equal(Object.hasOwn(runtime.params().data.fields, 'sellable_supplier_id'), false);
    runtime.succeed();
    assert.equal(runtime.child.closed, undefined);
    assert.equal(field.original, 'old');
    assert.equal(field.classes.has('editorchanged'), true);
});

test('a late save response cannot close a different reopened editor', () => {
    const runtime = boot();
    runtime.save();
    runtime.button.row = { classes: new Set(['ib-datatable-inline-edit-child']), fields: [] };
    runtime.succeed();
    assert.equal(runtime.calls.includes('close'), false);
    assert.equal(runtime.child.fields[0].original, 'old');
});

test('an unsuccessful JSON response does not acknowledge the submitted fields', () => {
    const runtime = boot();
    runtime.save();
    runtime.succeed({ success: false, action: 'reloadTable' });
    assert.equal(runtime.calls.includes('close'), false);
    assert.equal(runtime.child.fields[0].original, 'old');
});
