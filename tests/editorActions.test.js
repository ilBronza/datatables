const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.editorActions.js',
    'utf8'
);

function boot()
{
    const window = {};

    vm.runInNewContext(moduleSource, { window: window });

    return window;
}

test('reloadTable true in field ajax extra data always requests a table reload', () => {
    const runtime = boot();
    const fieldExtraData = {
        ajaxextradata: {
            reloadTable: true,
            refreshRow: false,
        },
    };

    assert.equal(runtime.ibDtEditorFieldRequestsTableReload(fieldExtraData), true);
});

test('reloadTable accepts serialized true values and both ajax-extra key variants', () => {
    const runtime = boot();

    assert.equal(runtime.ibDtEditorFieldRequestsTableReload({
        ajaxExtraData: { reloadTable: 'true' },
    }), true);
    assert.equal(runtime.ibDtEditorFieldRequestsTableReload({
        ajaxextradata: { reloadTable: '1' },
    }), true);
});

test('refreshRow cannot imply reloadTable when reloadTable is false', () => {
    const runtime = boot();

    assert.equal(runtime.ibDtEditorFieldRequestsTableReload({
        ajaxextradata: {
            reloadTable: false,
            refreshRow: true,
        },
    }), false);
});

test('reloadTable response actions are recognized in action and ibaction', () => {
    const runtime = boot();

    assert.equal(runtime.ibDtEditorResponseRequestsTableReload({ action: 'reloadTable' }), true);
    assert.equal(runtime.ibDtEditorResponseRequestsTableReload({ ibaction: 'reloadTable' }), true);
    assert.equal(runtime.ibDtEditorResponseRequestsTableReload({ action: 'refreshRow' }), false);
});
