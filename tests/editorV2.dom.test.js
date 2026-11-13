const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const test = require('node:test');

let JSDOM;
try { ({ JSDOM } = require('jsdom')); require.resolve('datatables.net-colreorder'); }
catch (_) { JSDOM = null; }
const domTest = (name, fn) => test(name, { skip: !JSDOM && 'Install DOM dependencies; see tests/README.md' }, fn);
const ajaxSource = fs.readFileSync('resources/assets/js/datatables.vendor.ajaxCall.min.js', 'utf8');
const buttonsSource = fs.readFileSync('resources/assets/js/datatables.vendor.buttons.min.js', 'utf8');
const inlineSource = fs.readFileSync('resources/assets/js/datatables.vendor.ajaxButton.min.js', 'utf8');
let definitions;

function extract(source, start, end) {
    const first = source.indexOf(start);
    const last = source.indexOf(end, first);
    assert.ok(first >= 0 && last > first);
    return source.slice(first, last);
}

function boot(t, { customUrl = false, groupedHeader = false, scrolling = false, renamedField = false, textarea = false, numeric = false } = {}) {
    if (!definitions) {
        const bootstrap = process.env.IB_DT_BOOTSTRAP || (fs.existsSync('vendor/autoload.php')
            ? 'vendor/autoload.php' : '../.ibtest/bootstrap_datatables.php');
        const generated = spawnSync(process.env.IB_DT_PHP || 'php', [
            '-d', 'error_reporting=8191', 'tests/fixtures/editorV2Fixtures.php.inc', path.resolve(bootstrap),
        ], { encoding: 'utf8' });
        assert.equal(generated.status, 0, generated.error?.message || generated.stderr || generated.stdout);
        definitions = JSON.parse(generated.stdout);
    }

    const dom = new JSDOM('<!doctype html><html><body><table class="datatable" id="records" data-editor-save-trigger="enter"><thead>'
        + (groupedHeader ? '<tr><th colspan="5">Group</th></tr>' : '') + '<tr class="columns">'
        + ['other', 'title', 'id', 'child', 'legacy'].map(name => '<th data-name="' + name + '" data-field-name="' + name + '">' + name + '</th>').join('')
        + '</tr></thead></table></body></html>', { runScripts: 'outside-only', url: 'https://example.test' });
    t.after(() => dom.window.close());
    const { window } = dom;
    for (const module of ['jquery', 'datatables.net', 'datatables.net-colreorder'])
        window.eval(fs.readFileSync(require.resolve(module), 'utf8'));
    for (const name of ['editorHelpers', 'numericHelpers', 'editors', 'numericEditor'])
        window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.' + name + '.js', 'utf8'));
    window.eval(extract(ajaxSource, '    function __getUrl(params)', '    window.__checkResultGeneric = function'));
    window.eval(extract(ajaxSource, '    window.ibCallAjax = function(params)', '    function __manageSuccessMessages'));
    window.eval(extract(buttonsSource, '    window.ibDtEditorUpdateFromElement = function', '    window.ibDtEditorUpdateFromToggle'));
    window.eval(extract(inlineSource, 'window.saveDatatableInlineEditRow = function', 'window.__ibCompleteDatatableInlineEditSave'));
    window.eval(extract(inlineSource, '    window.__ibGetEditorSaveTrigger = function(el)', '    window.__ibGetEditorSaveTriggerLabel'));

    const $ = window.jQuery;
    const definition = numeric ? (typeof numeric === 'string' ? numeric : 'numeric')
        : textarea ? (renamedField ? 'textareaAttributes' : 'textarea') : customUrl ? 'customUrl' : (renamedField ? 'attributes' : 'plain');
    for (const [name, header] of [[definition, 'title'], ['foreign', 'child']])
        for (const [key, value] of Object.entries(definitions[name + 'HeaderData']))
            $('#records th[data-name="' + header + '"]').attr('data-' + key, value);
    if (!textarea) $('#records th[data-name="title"]').attr('data-save-trigger', 'tab');
    $('#records th[data-name="child"]').attr('data-save-trigger', 'blur');
    const plain = window.eval('(' + definitions[definition] + ')');
    const foreign = window.eval('(' + definitions.foreign + ')');
    foreign.targets = [3];
    const data = [
        ['A', numeric ? definitions[definition + 'Cells'][0] : customUrl ? definitions.customUrlCells[0] : 'Note one', 'key/a', { value: 'Child one', targetId: 'child/a' }, '<input class="legacy" data-url="/legacy/one">'],
        ['B', numeric ? definitions[definition + 'Cells'][1] : customUrl ? definitions.customUrlCells[1] : 'Note two', 'second/b', { value: 'Child two', targetId: 'child/b' }, '<input class="legacy" data-url="/legacy/two">'],
    ];
    const dt = $('#records').DataTable({ data, columnDefs: [plain, foreign], rowId: 2, colReorder: true, paging: false, order: [], ...(scrolling ? { scrollX: true } : {}) });
    const calls = [];
    window.alert = message => { throw new Error(message); };
    window.__getTH = () => $(dt.column(1).header());
    window.ibDtGetDeclaredElementData = () => ({});
    window.__isEnabled = window.__isConfirmed = window.__isHeaderConfirmed = () => true;
    window.__manageSpinner = () => {};
    window.__getType = () => 'POST';
    window.__getData = params => params.data;
    window.__ibAjaxDataContainsFile = () => false;
    window.__ibCallAjax = (url, type, payload, params) => calls.push({ url, type, payload, params });
    window.ibDtGetBulkKindFromEditorElement = () => 'text';
    window.ibDtNormalizeBulkSubmitValue = (kind, value) => value;
    window.ibGetDatatableBroadcastHeaders = () => ({});
    window.__ibGetEditorSubmitValue = element => element.value;
    $.ajax = options => { calls.push(options); return {}; };

    const field = (row = 0, column = 1) => dt.cell(row, column).node().querySelector('input, textarea');
    const save = target => window.ibCallAjax({ target, e: { preventDefault() {} }, data: { 'ib-editor': true, field: window.ibDtGetEditorData(target, 'field'), value: window.__ibGetEditorSubmitValue(target), _method: 'PUT' } });
    return { window, document: window.document, $, dt, calls, field, save };
}

function installTextEvents(app) {
    const { window } = app;
    window.eval(extract(inlineSource, 'window.__ibEditorLooksLikePlainNumber = function', 'window.__ibIsDatatablePriceEditor = function'));
    window.eval(extract(inlineSource, 'window.editorFieldHasChanged = function', 'window.fieldHasUpdateOnSave = function'));
    window.eval(extract(inlineSource, "    $('body').on('keydown', '.ib-editor-text', function (e) {", "    $('body').on('click', '.ib-editor-save'"));
    window.eval(extract(inlineSource, '    // Save on blur (when config says so)', "    $('body').on('change', '.ib-editor-file-upload'"));
    window.__ibLogEditorDebug = window.__ibUnmarkFieldDirty = window.__ibTogglePerTableSaveButton = () => {};
    window.manageEditorFieldSavingPermission = () => true;
    window.__ibFocusAdjacentEditorField = () => true;
}

function installNumericEvents(app) {
    installTextEvents(app);
    app.window.eval(extract(inlineSource, 'window.__ibIsDatatablePriceEditor = function', 'window.__ibLogEditorDebug = function'));
}

domTest('numeric renderers format exact decimal strings while sorting/export use unrounded values', t => {
    const app = boot(t, { numeric: true });
    installNumericEvents(app);
    const raw = '9007199254740993.123456789012345678900';
    assert.equal(app.field().value, '9.007.199.254.740.993,12');
    assert.equal(app.field().getAttribute('data-originalvalue'), raw);
    assert.equal(app.field().type, 'text');
    assert.equal(app.field().getAttribute('inputmode'), 'decimal');
    assert.equal(app.dt.cell(0, 1).data(), raw);
    const column = app.window.eval('(' + definitions.numeric + ')');
    for (const mode of ['sort', 'filter', 'export', 'type']) assert.equal(column.render(raw, mode, [], {}), raw);
    assert.equal(app.dt.cell(0, 1).node().textContent.includes('EUR'), true);
    app.dt.row.add(['C', '9007199254740993.123456789012345678901', 'next', { value: 'Other', targetId: 'other' }, '']);
    app.dt.row.add(['D', null, 'empty', { value: 'Other', targetId: 'other' }, '']);
    app.dt.order([1, 'asc']).draw();
    assert.deepEqual(Array.from(app.dt.rows({ order: 'applied' }).data().toArray(), row => row[2]), ['second/b', 'key/a', 'next', 'empty']);
    app.dt.order([1, 'desc']).draw();
    assert.deepEqual(Array.from(app.dt.rows({ order: 'applied' }).data().toArray(), row => row[2]), ['next', 'key/a', 'second/b', 'empty']);
    const holder = app.document.createElement('div');
    const denied = app.window.eval('(' + definitions.numericDenied + ')');
    holder.innerHTML = denied.render(raw, 'display', [], {});
    assert.equal(holder.querySelector('input'), null);
    assert.equal(holder.textContent, '9.007.199.254.740.993,12');
});

domTest('numeric focus and blur preserve hidden precision; explicitly editing the rounded value saves that exact draft', t => {
    const app = boot(t, { numeric: true });
    installNumericEvents(app);
    const input = app.field();
    const raw = '9007199254740993.123456789012345678900';
    const editor = app.window.IlBronza.datatables.editors.forInput(input);
    assert.ok(editor instanceof app.window.IlBronza.datatables.editors.NumericEditorContext);
    assert.equal(editor.getValue(), raw);
    input.focus();
    assert.equal(input.value, raw.replace('.', ','));
    assert.equal(app.window.editorFieldHasChanged(input), false);
    input.blur();
    assert.equal(app.calls.length, 0);
    assert.equal(input.value, '9.007.199.254.740.993,12');
    editor.save();
    assert.equal(app.calls[0].payload.value, raw);
    assert.equal(typeof app.calls[0].payload.value, 'string');
    input.focus();
    input.value = '9007199254740993,12';
    input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
    app.$(input).trigger('ib:dt-editor-save');
    assert.equal(app.calls[1].payload.value, '9007199254740993.12');
});

domTest('numeric validation blocks invalid drafts and direct transport calls before dirty state is acknowledged', t => {
    const app = boot(t, { numeric: 'numericConstraints' });
    installNumericEvents(app);
    const input = app.field();
    input.focus();
    for (const value of ['0,04', '0,031', '100,18', '-0,02', '12abc']) {
        input.value = value;
        input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
        app.$(input).addClass('editorchanged').trigger('ib:dt-editor-save');
        assert.equal(app.calls.length, 0, value);
        assert.ok(input.validationMessage, value);
        assert.equal(input.getAttribute('data-originalvalue'), '0.08');
        assert.equal(input.classList.contains('editorchanged'), true);
    }
    app.window.ibCallAjax({ target: input, data: { 'ib-editor': true, field: 'title', value: '0.04' } });
    assert.equal(app.calls.length, 0);
    input.blur();
    input.reportValidity = () => { input.focus(); return false; };
    input.value = '0,04';
    input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
    app.save(input);
    assert.equal(app.calls.length, 0);
    assert.ok(input.validationMessage, 'Focusing the invalid field must preserve the validation error');
    input.value = '0,13';
    input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
    app.$(input).trigger('ib:dt-editor-save');
    assert.equal(app.calls[0].payload.value, '0.13');
    input.value = '0,1300';
    input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
    assert.equal(app.window.editorFieldHasChanged(input), false);
    assert.equal(input.validationMessage, '');
});

domTest('numeric input accepts localized pastes and keeps zero/null distinct without altering legacy price parsing', t => {
    const app = boot(t, { numeric: true });
    installNumericEvents(app);
    const input = app.field();
    input.focus();
    for (const [draft, expected] of [['12,202', '12.202'], ['12.202', '12.202'], ['1.234,5678901234567890', '1234.5678901234567890'], ['0', '0'], ['', null]]) {
        input.value = draft;
        input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
        app.save(input);
        assert.equal(app.calls.at(-1).payload.value, expected);
    }
    const options = JSON.parse(app.$(app.dt.column(1).header()).attr('data-ib-dt-number-options'));
    options.nullable = false;
    app.$(app.dt.column(1).header()).attr('data-ib-dt-number-options', JSON.stringify(options));
    const before = app.calls.length;
    app.save(input);
    assert.equal(app.calls.length, before);
    assert.match(input.validationMessage, /obbligatorio/);
    const legacy = app.field(0, 4);
    app.$(legacy).addClass('ib-dt-price-editor').attr('data-dt-price-decimals', 2).val('12,202');
    assert.equal(app.window.__ibGetEditorSubmitValue(legacy), '12.202');
    assert.equal(app.window.__getTargetUrl(legacy), '/legacy/one');
});

domTest('numeric inline edit/create batches keep full precision and reject invalid fields without closing the form', t => {
    const app = boot(t, { numeric: true });
    installNumericEvents(app);
    const raw = '9007199254740993.123456789012345678900';
    for (const [fixture, url] of [['numericInlineEdit', '/records/key%2Fa?from="table"&mode=edit'], ['numericInlineCreate', '/records?create=1']]) {
        const row = app.$('<tr class="child ib-datatable-inline-edit-child"><td></td></tr>');
        row.children('td').append(definitions[fixture]);
        const button = app.$('<button class="ib-datatable-inline-edit-save">Save</button>').appendTo(row.children('td'))[0];
        app.dt.row(0).child(row).show();
        app.window.IlBronza.datatables.editors.initialize(row);
        const input = row.find('input')[0];
        assert.equal(input.value, '9.007.199.254.740.993,12');
        app.$(input).addClass('editorchanged');
        app.window.saveDatatableInlineEditRow(button, { preventDefault() {} });
        assert.equal(app.calls.at(-1).url, url);
        assert.equal(app.calls.at(-1).payload.fields.notes, raw);
        const before = app.calls.length;
        input.value = 'invalid';
        input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
        app.window.saveDatatableInlineEditRow(button, { preventDefault() {} });
        assert.equal(app.calls.length, before);
        assert.equal(input.classList.contains('editorchanged'), true);
    }
});

domTest('numeric bulk controls parse and validate with the column options and send decimal strings', t => {
    const app = boot(t, { numeric: true });
    installNumericEvents(app);
    const { window } = app;
    window.eval(extract(buttonsSource, '    window.ibDtDetectBulkKindFromCell = function', '    window.ibDtGetBulkPossibleValues'));
    window.eval(extract(buttonsSource, '    window.ibDtNormalizeBulkSubmitValue = function', '    window.ibDtGetBulkValueLabel'));
    window.eval(extract(buttonsSource, '    window.ibDtNormalizeToggleValue = function', '    window.ibDtToggleValueEquals'));
    window.eval(extract(buttonsSource, '    window.ibDtSetBulkModalFieldRowDirty = function', '    window.ibDtApplyBulkModalFieldValue'));
    const options = JSON.parse(app.$(app.dt.column(1).header()).attr('data-ib-dt-number-options'));
    const row = app.document.createElement('div');
    row.appendChild(window.ibDtCreateBulkModalFieldControl(app.dt, { name: 'title', numericOptions: options }, 'decimal', {}));
    const input = row.querySelector('input');
    assert.equal(input.type, 'text');
    assert.equal(input.getAttribute('inputmode'), 'decimal');
    input.value = '9.007.199.254.740.993,123456789012345678900';
    const value = window.ibDtReadBulkModalFieldValue(row, 'decimal');
    assert.equal(value, '9007199254740993.123456789012345678900');
    assert.equal(window.ibDtGetBulkKindFromEditorElement(app.field()), 'decimal');
    window.ibDtEditorUpdateFromElement(app.field(), value);
    assert.equal(app.calls[0].data.value, value);
    assert.equal(typeof app.calls[0].data.value, 'string');
    input.value = '12abc';
    assert.equal(window.ibDtReadBulkModalFieldValue(row, 'decimal'), undefined);
    assert.ok(input.validationMessage);
});

domTest('numeric inline create validates the actual store flow before sending flat form values', t => {
    const app = boot(t, { numeric: true });
    installNumericEvents(app);
    app.window.eval(extract(inlineSource, 'window.saveDatatableInlineCreateRow = function', '// Debug editor save'));
    const row = app.$('<tr class="ib-datatable-inline-create-row"><td></td></tr>').prependTo(app.$(app.dt.table().body()));
    row.children('td').append(definitions.numericInlineCreate);
    row.data('storeUrl', '/records?create=1');
    const button = app.$('<button>Save</button>').appendTo(row.children('td'))[0];
    app.window.__ibGetDatatableInlineCreateRow = () => row;
    app.window.__ibSetDatatableInlineCreateActive = () => {};
    const input = row.find('input')[0];
    input.value = 'broken';
    input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
    app.window.saveDatatableInlineCreateRow(app.dt, button, { preventDefault() {} });
    assert.equal(app.calls.length, 0);
    input.value = '9007199254740993,123456789012345678900';
    input.dispatchEvent(new app.window.Event('input', { bubbles: true }));
    app.window.saveDatatableInlineCreateRow(app.dt, button, { preventDefault() {} });
    assert.equal(app.calls[0].url, '/records?create=1');
    assert.equal(app.calls[0].payload.notes, '9007199254740993.123456789012345678900');
});

domTest('numeric bulk refuses a value outside column constraints before any selected row is written', t => {
    const app = boot(t, { numeric: 'numericConstraints' });
    installNumericEvents(app);
    const { window } = app;
    window.eval(extract(buttonsSource, '    window.ibDtApplyBulkEdit = function', '    window.ibDtApplyBulkToggle'));
    window.ibDtGetBulkToggleLabels = () => ({});
    window.ibDtGetRowEditorElement = (table, row, column) => table.cell(row, column).node().querySelector('input');
    window.ibDtGetRowIdFromEditorElement = () => 'row';
    window.ibDtGetCurrentValueFromEditorElement = element => window.IlBronza.datatables.editors.forInput(element).getValue();
    window.ibDtBulkValueEquals = (kind, current, target) => window.IlBronza.datatables.editors.helpers.numbers.equal(current, target);
    let result;
    window.ibDtApplyBulkEdit(app.dt, { index: 1 }, '0.04', { onComplete(value) { result = value; } });
    assert.equal(result.ok, false);
    assert.equal(app.calls.length, 0);
});

domTest('numeric route lookup follows hidden/reordered columns and related or per-record targets', t => {
    for (const numeric of ['numeric', 'numericCustomUrl', 'numericForeign']) {
        const app = boot(t, { numeric });
        installNumericEvents(app);
        app.dt.column(0).visible(false);
        app.dt.colReorder.move(1, 4);
        const input = app.dt.row(0).node().querySelector('.ib-dt-numeric-editor');
        const editor = app.window.IlBronza.datatables.editors.forInput(input);
        const url = numeric === 'numericCustomUrl' ? '/special/numeric/0'
            : numeric === 'numericForeign' ? '/records/42?from="table"&mode=edit' : '/records/key%2Fa?from="table"&mode=edit';
        assert.equal(editor.resolveUrl(), url);
        editor.save('12.3456789');
        assert.equal(app.calls[0].url, url);
        assert.equal(app.calls[0].payload.value, '12.3456789');
    }
});

domTest('numeric undo/redo keeps exact confirmed values and tolerates equivalent server decimal scales', async t => {
    const app = historyRuntime(t, { numeric: true });
    installNumericEvents(app);
    const original = app.value;
    const value = '9007199254740993.00000000000000000001';
    app.save(value).succeed({ success: true, value });
    await app.idle();
    assert.equal(app.value, value);
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.value, original);
    assert.equal(await app.history.redo(app.dt), true);
    assert.equal(app.value, value);
    app.save('1.0000').succeed({ success: true, value: '1.0000' });
    await app.idle();
    app.rows.get('key/a')[1] = '1.0';
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.value, value);
});

domTest('textarea renderers preserve multiline content, leading blank lines and orthogonal values without HTML injection', t => {
    const app = boot(t, { textarea: true });
    installTextEvents(app);
    const column = app.window.eval('(' + definitions.textarea + ')');
    for (const value of [null, '', 0, '001', '\n', '\n\nPrima\nSeconda\n', 'Uno\r\nDue\rTre', '</textarea><script>test</script>"<&']) {
        const holder = app.document.createElement('div');
        const row = [null, value, 42];
        holder.innerHTML = column.render(value, 'display', row, {});
        const textarea = holder.querySelector('textarea');
        const expected = String(value ?? '').replace(/\r\n?/g, '\n');
        assert.equal(textarea.value, expected);
        assert.equal(textarea.getAttribute('data-originalvalue'), expected);
        assert.equal(textarea.rows, 3);
        assert.equal(textarea.hasAttribute('value'), false);
        assert.equal(textarea.hasAttribute('type'), false);
        assert.equal(holder.querySelectorAll('textarea').length, 1);
        assert.equal(holder.querySelector('script'), null);
        assert.equal(app.window.editorFieldHasChanged(textarea), false);
        assert.equal(row[1], value);
        for (const mode of ['filter', 'sort', 'export', 'type'])
            assert.equal(column.render(value, mode, row, {}), value);
    }
    const attributes = app.window.eval('(' + definitions.textareaAttributes + ')');
    const holder = app.document.createElement('div');
    holder.innerHTML = attributes.render('Actual\nvalue', 'display', [null, null, 42], {});
    assert.equal(holder.querySelector('textarea').rows, 5);
    assert.equal(holder.querySelector('textarea').maxLength, 400);
    assert.equal(holder.querySelector('textarea').value, 'Actual\nvalue');
    assert.equal(holder.querySelector('textarea').classList.contains('notes'), true);
    assert.equal(holder.querySelector('textarea').classList.contains('uk-textarea'), true);
    const denied = app.window.eval('(' + definitions.textareaDenied + ')');
    holder.innerHTML = denied.render('<script>\nText', 'display', [], {});
    assert.equal(holder.querySelector('textarea, script'), null);
    assert.equal(holder.textContent, '<script>\nText');
    const conditional = app.window.eval('(' + definitions.textareaConditional + ')');
    holder.innerHTML = conditional.render({ value: 'Locked\ntext', disabled: true }, 'display', [null, null, 42], {});
    assert.equal(holder.querySelector('textarea').disabled, true);
    const foreign = app.window.eval('(' + definitions.textareaForeign + ')');
    holder.innerHTML = foreign.render({ value: 'Child\ntext', targetId: 'child/a' }, 'display', [], {});
    assert.equal(holder.querySelector('textarea').getAttribute('data-ib-dt-target-id'), 'child/a');
});

domTest('textarea keeps Enter and arrow editing native, saves on blur or Tab and restores multiline drafts on Escape', t => {
    const app = boot(t, { textarea: true });
    installTextEvents(app);
    const input = app.field();
    assert.equal(app.window.__ibGetEditorSaveTrigger(input), 'blur');
    input.value = 'Prima\nSeconda';
    input.select();
    for (const key of ['Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown']) {
        const event = app.$.Event('keydown', { key });
        app.$(input).trigger(event);
        assert.equal(event.isDefaultPrevented(), false, key);
        assert.equal(event.isPropagationStopped(), true, key);
        assert.equal(app.calls.length, 0);
    }
    app.$(input).trigger('blur');
    assert.equal(app.calls.length, 1);
    assert.equal(app.calls[0].payload.value, 'Prima\nSeconda');
    app.$(app.dt.column(1).header()).attr('data-save-trigger', 'tab');
    input.value = '\nTerza\n';
    const tab = app.$.Event('keydown', { key: 'Tab' });
    app.$(input).trigger(tab);
    assert.equal(tab.isDefaultPrevented(), true);
    assert.equal(app.calls.length, 2);
    assert.equal(app.calls[1].payload.value, '\nTerza\n');
    input.value = 'Draft\nunsaved';
    app.window.__ibSetEditorVisibleValue = (target, value) => { target.value = value; };
    app.$(input).trigger(app.$.Event('keydown', { key: 'Escape' }));
    assert.equal(input.value, '\nTerza\n');
    assert.equal(app.calls.length, 2);
});

domTest('inline textarea edit/create preserves leading newlines and saves only with its batch action', t => {
    const app = boot(t, { textarea: true });
    installTextEvents(app);
    for (const [fixture, url] of [['textareaInlineEdit', '/records/key%2Fa?from="table"&mode=edit'], ['textareaInlineCreate', '/records?create=1']]) {
        const row = app.$('<tr class="child ib-datatable-inline-edit-child"><td></td></tr>');
        row.children('td').append(definitions[fixture]);
        const button = app.$('<button class="ib-datatable-inline-edit-save">Save</button>').appendTo(row.children('td'))[0];
        app.dt.row(0).child(row).show();
        const input = row.find('textarea')[0];
        assert.equal(input.value, '\nPrima riga\n</textarea><script>test</script>\n');
        const before = app.calls.length;
        input.value = '\nInline\nnotes\n';
        app.$(input).addClass('editorchanged').trigger(app.$.Event('keydown', { key: 'Enter' })).trigger('blur');
        assert.equal(app.calls.length, before);
        app.window.saveDatatableInlineEditRow(button, { preventDefault() {} });
        assert.equal(app.calls.at(-1).url, url);
        assert.equal(app.calls.at(-1).payload.fields.notes, '\nInline\nnotes\n');
    }
});

domTest('bulk textarea controls accept multiline values and keep trailing blank lines significant', t => {
    const app = boot(t, { textarea: true });
    const { window } = app;
    window.eval(extract(buttonsSource, '    window.ibDtDetectBulkKindFromCell = function', '    window.ibDtGetBulkPossibleValues'));
    window.eval(extract(buttonsSource, '    window.ibDtNormalizeToggleValue = function', '    window.ibDtToggleValueEquals'));
    window.eval(extract(buttonsSource, '    window.ibDtSetBulkModalFieldRowDirty = function', '    window.ibDtApplyBulkModalFieldValue'));
    window.eval(extract(buttonsSource, '    window.ibDtWireBulkModalFieldRow = function', '    window.ibDtBuildBulkModalFieldRow'));
    assert.equal(window.ibDtDetectBulkKindFromCell(app.dt.cell(0, 1).node()), 'textarea');
    const row = app.document.createElement('div');
    row.innerHTML = '<button class="ib-dt-bulk-field-save" disabled>Save</button>';
    row.appendChild(window.ibDtCreateBulkModalFieldControl(app.dt, { name: 'title' }, 'textarea', {}));
    const input = row.querySelector('textarea');
    assert.ok(input);
    window.ibDtWireBulkModalFieldRow(row, app.dt, { name: 'title' }, 'textarea', {});
    input.value = '\nBulk\nnotes\n';
    input.dispatchEvent(new window.Event('input'));
    assert.equal(row.querySelector('button').disabled, false);
    const value = window.ibDtReadBulkModalFieldValue(row, 'textarea');
    assert.equal(value, '\nBulk\nnotes\n');
    assert.equal(window.ibDtBulkValueEquals('textarea', value, value.trim()), false);
    window.ibDtEditorUpdateFromElement(app.field(), value);
    assert.equal(app.calls[0].data.value, value);
    window.ibDtResetBulkModalFieldRow(row, 'textarea', {});
    assert.equal(input.value, '');
    assert.equal(row.querySelector('button').disabled, true);
    input.value = '\n';
    input.dispatchEvent(new window.Event('input'));
    assert.equal(window.ibDtReadBulkModalFieldValue(row, 'textarea'), '\n');
    assert.equal(row.querySelector('button').disabled, false);
});

domTest('textarea shares confirmed-value history and undo/redo preserves leading and trailing newlines', async t => {
    const app = historyRuntime(t, { textarea: true });
    const value = '\nFirst\nSecond\n';
    app.save(value).succeed({ success: true, value });
    await app.idle();
    assert.equal(app.value, value);
    assert.equal(app.field().value, value);
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.value, 'Note one');
    assert.equal(await app.history.redo(app.dt), true);
    assert.equal(app.value, value);
    assert.equal(app.field().value, value);
});

domTest('object contexts save through the shared transport and preserve legacy routes and data', t => {
    const app = boot(t, { renamedField: true });
    const editors = app.window.IlBronza.datatables.editors;
    const controller = editors.forTable(app.dt);
    const editor = controller.editorFor(app.field());
    assert.ok(editor instanceof editors.EditorContext);
    assert.equal(editor.managed, true);
    assert.equal(editor.data('field'), 'notes');
    app.field().value = '001';
    assert.equal(editor.getValue(), '001');
    editor.save(0, { data: { extraContext: 'object' }, deferRowRefresh: true });
    assert.equal(app.calls.length, 1);
    assert.equal(app.calls[0].url, '/records/key%2Fa?from="table"&mode=edit');
    assert.equal(app.calls[0].payload.field, 'notes');
    assert.equal(app.calls[0].payload.value, 0);
    assert.equal(app.calls[0].payload.extraContext, 'object');
    assert.equal(app.calls[0].params.deferRowRefresh, true);

    const legacyInput = app.field(0, 4);
    app.$(legacyInput).attr('data-field', 'old_notes').data('field', 'cached_notes');
    const legacy = controller.editorFor(legacyInput);
    assert.ok(legacy instanceof editors.LegacyEditorContext);
    assert.equal(legacy.managed, false);
    assert.equal(legacy.data('field'), 'cached_notes');
    assert.equal(legacy.resolveUrl(), '/legacy/one');
    legacy.save('Legacy');
    assert.equal(app.calls[1].url, '/legacy/one');
    assert.equal(app.calls[1].payload.field, 'cached_notes');
    assert.equal(app.calls[1].payload.value, 'Legacy');
    assert.deepEqual(Object.keys(app.window).filter(name => /^ibDt.*V2/.test(name)), []);
});

domTest('controllers survive redraws, release input contexts and restart on table recreation', t => {
    const app = historyRuntime(t);
    const editors = app.window.IlBronza.datatables.editors;
    const node = app.dt.table().node();
    const controller = editors.forTable(app.dt);
    assert.equal(editors.forTable(node), controller);
    assert.equal(editors.forTable('#records'), controller);
    assert.equal(controller.history, editors.forTable(app.dt).history);
    const firstInput = app.field();
    const firstContext = controller.editorFor(firstInput);
    app.dt.cell(0, 1).data('Redrawn').draw(false);
    assert.notEqual(app.field(), firstInput);
    assert.equal(firstInput.isConnected, false);
    const nextContext = controller.editorFor(app.field());
    assert.notEqual(nextContext, firstContext);
    assert.equal(nextContext.getValue(), 'Redrawn');
    assert.equal(nextContext.getController(), controller);
    app.dt.column(0).visible(false);
    app.dt.colReorder.move(1, 3);
    assert.equal(editors.forTable(app.dt), controller);
    const rows = app.dt.rows().data().toArray();
    app.dt.destroy();
    const recreated = app.$(node).DataTable({ data: rows, paging: false, order: [] });
    const replacement = editors.forTable(recreated);
    assert.notEqual(replacement, controller);
    assert.notEqual(replacement.history, controller.history);
    assert.equal(replacement.history.state().undo, 0);
});

domTest('a table controller rejects foreign inputs and keeps history capture inside its own table', t => {
    const app = historyRuntime(t);
    const editors = app.window.IlBronza.datatables.editors;
    const first = editors.forTable(app.dt);
    const other = app.$('<table><thead><tr><th data-field-name="title" data-field="title" data-ib-dt-cell-format="scalar">Title</th></tr></thead></table>')
        .appendTo(app.document.body).DataTable({ data: [['Other']], paging: false, order: [] });
    const second = editors.forTable(other);
    assert.notEqual(first, second);
    assert.notEqual(first.history, second.history);
    const input = app.field();
    assert.throws(() => second.editorFor(input), /different DataTable/);
    assert.equal(second.history.capture({ target: input }, { 'ib-editor': true, field: 'title', value: 'Wrong owner' }, '/records/key%2Fa'), null);
    assert.equal(first.history.state().pending, 0);
    assert.equal(second.history.state().pending, 0);
});

domTest('V2 save triggers come from the column th rather than the submitted field name or input', t => {
    const app = boot(t);
    const input = app.field();
    app.$(app.dt.column(1).header()).attr('data-field', 'notes');
    app.window.__ibDatatableFieldSettings = { records: { notes: { saveTrigger: 'blur' } } };
    assert.equal(input.hasAttribute('data-save-trigger'), false);
    assert.equal(app.window.__ibGetEditorSaveTrigger(input), 'tab');
    assert.equal(app.window.__ibGetEditorSaveTrigger(app.field(0, 3)), 'blur');
    app.$(app.dt.column(1).header()).attr('data-save-trigger', 'enter');
    assert.equal(app.window.__ibGetEditorSaveTrigger(input), 'enter');
});

domTest('save triggers follow hidden/reordered columns, scrolling headers and grouped headings', t => {
    const app = boot(t, { groupedHeader: true, scrolling: true });
    app.dt.column(0).visible(false);
    app.dt.column(2).visible(false);
    app.dt.colReorder.move(1, 4);
    const input = app.dt.row(0).node().querySelector('.ib-dt-editor-v2:not([data-ib-dt-target-id])');
    assert.equal(app.window.__ibGetEditorSaveTrigger(input), 'tab');
    const header = app.document.querySelector('.dt-scroll-head th[data-name="title"]');
    assert.equal(header.getAttribute('data-save-trigger'), 'tab');
    header.setAttribute('data-save-trigger', 'blur');
    assert.equal(app.window.__ibGetEditorSaveTrigger(input), 'blur');
});

domTest('inline controls read the container trigger while legacy input and table defaults keep working', t => {
    const app = boot(t);
    const inline = app.$('<div>').html(definitions.inlineEdit).appendTo(app.document.body);
    const input = inline.find('input')[0];
    assert.equal(input.hasAttribute('data-save-trigger'), false);
    assert.equal(app.window.__ibGetEditorSaveTrigger(input), 'enter');
    input.closest('[data-ib-dt-inline-control]').setAttribute('data-save-trigger', 'blur');
    assert.equal(app.window.__ibGetEditorSaveTrigger(input), 'blur');
    const legacy = app.field(0, 4);
    legacy.setAttribute('data-save-trigger', 'tab');
    assert.equal(app.window.__ibGetEditorSaveTrigger(legacy), 'tab');
    legacy.removeAttribute('data-save-trigger');
    app.$('#records').data('editor-save-trigger', 'blur');
    assert.equal(app.window.__ibGetEditorSaveTrigger(legacy), 'blur');
});

domTest('input metadata comes from th and real save events send the configured parameter with literal text comparison', t => {
    const app = boot(t, { renamedField: true });
    const input = app.field();
    for (const name of ['field', 'ib-dt-cell-format', 'ib-dt-value-type'])
        assert.equal(input.hasAttribute('data-' + name), false);
    assert.equal(app.window.ibDtGetEditorData(input, 'field'), 'notes');
    assert.equal(app.window.ibDtGetEditorData(input, 'ib-dt-cell-format'), 'scalar');
    assert.equal(app.window.ibDtGetEditorData(input, 'ib-dt-value-type'), 'text');
    app.window.eval(extract(inlineSource, 'window.__ibGetEditorInputType = function', 'window.__ibIsDatatablePriceEditor = function'));
    app.window.eval(extract(inlineSource, 'window.editorFieldHasChanged = function', 'window.fieldHasUpdateOnSave = function'));
    app.window.eval(extract(inlineSource, "    $('body').on('ib:dt-editor-save', '.ib-editor-text'", '    // Save on Enter'));
    app.window.__ibLogEditorDebug = () => {};
    app.window.manageEditorFieldSavingPermission = () => true;
    app.window.__ibUnmarkFieldDirty = app.window.__ibTogglePerTableSaveButton = () => {};
    input.setAttribute('data-originalvalue', '001');
    input.value = '1';
    assert.equal(app.window.editorFieldHasChanged(input), true);
    app.$(input).trigger('ib:dt-editor-save', { source: 'enter' });
    assert.equal(app.calls[0].payload.field, 'notes');
    assert.equal(app.calls[0].payload.value, '1');
    assert.equal(input.getAttribute('data-originalvalue'), '1');
});

domTest('real AJAX cell commits read the header format and preserve structured cell metadata', t => {
    for (const customUrl of [false, true]) {
        const app = boot(t, { customUrl });
        app.window.eval(extract(ajaxSource, '    window.__checkResultGeneric = function', '    window.__getDataTableByClass'));
        app.window.__isEditorSaveTarget = () => false;
        const input = app.field();
        const original = app.dt.cell(0, 1).data();
        app.window.__checkResultGeneric({ title: 'Server value' }, {
            target: input, data: { field: 'title', value: 'Submitted' },
        });
        const committed = app.dt.cell(0, 1).data();
        if (customUrl) {
            assert.equal(committed.value, 'Server value');
            assert.equal(committed.updateUrl, original.updateUrl);
        }
        else assert.equal(committed, 'Server value');
        assert.equal(app.field().hasAttribute('data-ib-dt-cell-format'), false);
    }
});

domTest('dependent legacy selects read V2 drafts by the parameter stored on th', t => {
    const app = boot(t, { renamedField: true });
    app.window.eval(extract(inlineSource, '    window.ibDtGetSelectRowRouteFields = function', '    window.ibDtFlattenSelectPossibleValues'));
    const select = app.$('<select data-field="dependent" data-possible-values-row-route-fields=\'["notes"]\' data-possible-values-row-route-empty-value="null"><option value="old" selected>Old</option></select>')
        .appendTo(app.dt.cell(0, 0).node())[0];
    app.field().value = 'Current draft';
    assert.equal(app.field().hasAttribute('data-field'), false);
    assert.equal(app.window.ibDtGetSelectRowRouteFields(select).notes, 'Current draft');
    app.window.ibDtInvalidateSelectRowDependents(app.field());
    assert.equal(select.value, 'null');
    assert.equal(app.calls.length, 0);
});

domTest('single saves resolve the shared template at request time, encode IDs, and never cache a URL on inputs', t => {
    const app = boot(t);
    const input = app.field();
    assert.equal(input.hasAttribute('data-url'), false);
    assert.equal(app.$(input).data('url'), undefined);
    app.save(input);
    assert.equal(app.calls[0].url, '/records/key%2Fa?from="table"&mode=edit');
    app.dt.settings()[0].aoColumns[1].ibDtEditor.urlTemplate = '/renamed/__ROW_ID__/again/__ROW_ID__';
    app.save(input);
    assert.equal(app.calls[1].url, '/renamed/key%2Fa/again/key%2Fa');
    assert.equal(input.hasAttribute('data-url'), false);
    assert.equal(app.$(input).data('url'), undefined);
    app.dt.row.add(['C', 'Zero', 0, { value: 'Zero child', targetId: 0 }, '<input>']).draw();
    app.save(app.field(2));
    assert.equal(app.calls[2].url, '/renamed/0/again/0');
});

domTest('hidden and reordered columns, sorting and filtering retain the correct row and column routes', t => {
    const app = boot(t);
    app.dt.column(2).visible(false);
    app.dt.colReorder.move(1, 4);
    app.dt.order([[0, 'desc']]).search('Note two').draw();
    const input = app.dt.row(1).node().querySelector('.ib-dt-editor-v2:not([data-ib-dt-target-id])');
    app.save(input);
    assert.equal(app.calls[0].url, '/records/second%2Fb?from="table"&mode=edit');
});

domTest('single and bulk saves prefer the PHP cell URL and fall back to the column template per record', t => {
    const app = boot(t, { customUrl: true });
    app.save(app.field());
    app.save(app.field(1));
    app.window.ibDtEditorUpdateFromElement(app.field(), 'Bulk', 'notes');
    assert.deepEqual(app.calls.map(call => call.url), [
        '/special/one?next=%2F&mode=one', '/records/second%2Fb?from="table"&mode=edit', '/special/one?next=%2F&mode=one',
    ]);
    assert.equal(app.field().hasAttribute('data-url'), false);
    assert.equal(app.$(app.field()).data('url'), undefined);

    app.dt.cell(0, 1).data({ value: 'Changed', updateUrl: '/special/changed' });
    app.save(app.field());
    assert.equal(app.calls[3].url, '/special/changed');
    for (const updateUrl of [null, '', '   ']) {
        app.dt.cell(0, 1).data({ value: 'Changed', updateUrl });
        app.save(app.field());
        assert.equal(app.calls.at(-1).url, '/records/key%2Fa?from="table"&mode=edit');
    }
});

domTest('custom URLs follow their cells across hidden/reordered columns and require the optional setting', t => {
    const app = boot(t, { customUrl: true });
    app.dt.column(2).visible(false);
    app.dt.colReorder.move(1, 4);
    const input = app.dt.row(0).node().querySelector('.ib-dt-editor-v2:not([data-ib-dt-target-id])');
    app.save(input);
    assert.equal(app.calls[0].url, '/special/one?next=%2F&mode=one');
    const index = app.dt.cell(input.closest('td')).index();
    app.dt.settings()[0].aoColumns[index.column].ibDtEditor.updateUrlFromCell = false;
    app.save(input);
    assert.equal(app.calls[1].url, '/records/key%2Fa?from="table"&mode=edit');
});

domTest('related-model saves use the child ID and refuse missing targets or column metadata', t => {
    const app = boot(t);
    const input = app.field(0, 3);
    app.save(input);
    assert.equal(app.calls[0].url, '/records/child%2Fa?from="table"&mode=edit');
    input.removeAttribute('data-ib-dt-target-id');
    assert.throws(() => app.save(input), /requires a target ID/);
    delete app.dt.settings()[0].aoColumns[3].ibDtEditor;
    input.setAttribute('data-url', '/wrong-fallback');
    assert.throws(() => app.save(input), /requires column route metadata/);
    assert.equal(app.calls.length, 1);
});

domTest('missing row IDs and detached V2 inputs cannot fall back to another endpoint', t => {
    const app = boot(t);
    app.dt.row.add(['C', 'Missing ID', null, { value: 'Child', targetId: null }, '<input>']).draw();
    assert.throws(() => app.save(app.field(2)), /requires a target ID/);
    const detached = app.field().cloneNode(true);
    detached.setAttribute('data-url', '/wrong-fallback');
    assert.throws(() => app.save(detached), /requires a DataTable or an inline route/);
    assert.equal(app.calls.length, 0);
});

domTest('bulk updates share the V2 resolver while legacy controls keep their existing URL', t => {
    const app = boot(t);
    app.window.ibDtEditorUpdateFromElement(app.field(0), 'Bulk one', 'notes');
    app.window.ibDtEditorUpdateFromElement(app.field(1), 'Bulk two', 'notes');
    assert.deepEqual(app.calls.map(call => call.url), [
        '/records/key%2Fa?from="table"&mode=edit', '/records/second%2Fb?from="table"&mode=edit',
    ]);
    assert.equal(app.calls[0].data.field, 'notes');
    const legacy = app.field(0, 4);
    assert.equal(app.window.__getTargetUrl(legacy), '/legacy/one');
    app.window.ibDtEditorUpdateFromElement(legacy, 'Legacy');
    assert.equal(app.calls[2].url, '/legacy/one');
    assert.equal(app.window.__getUrl({ target: legacy }), '/legacy/one');
});

domTest('PHP-generated inline wrappers resolve edit and create routes without data-url on their inputs', t => {
    const app = boot(t);
    const inline = app.$('<div>').html(definitions.inlineEdit).appendTo(app.document.body);
    const input = inline.find('input')[0];
    assert.equal(input.hasAttribute('data-url'), false);
    assert.equal(input.closest('[data-ib-dt-inline-control]').className, 'inline-wrapper ib-suffix-container');
    assert.equal(app.window.__getTargetUrl(input), '/records/key%2Fa?from="table"&mode=edit');
    inline.html(definitions.inlineCreate);
    assert.equal(app.window.__getTargetUrl(inline.find('input')[0]), '/records?create=1');
    assert.equal(inline.find('input')[0].hasAttribute('data-url'), false);
});

domTest('inline custom URLs use the actual model and empty overrides retain the template fallback', t => {
    const app = boot(t);
    const inline = app.$('<div>').html(definitions.inlineCustomUrl).appendTo(app.document.body);
    const input = inline.find('input')[0];
    assert.equal(input.hasAttribute('data-url'), false);
    assert.equal(app.window.__getTargetUrl(input), '/special/inline?next=%2F&mode=one');
    const wrapper = input.closest('[data-ib-dt-editor-route]');
    const route = JSON.parse(wrapper.getAttribute('data-ib-dt-editor-route'));
    route.updateUrl = null;
    wrapper.setAttribute('data-ib-dt-editor-route', JSON.stringify(route));
    assert.equal(app.window.__getTargetUrl(input), '/records/key%2Fa?from="table"&mode=edit');
});

domTest('inline batch saves resolve the first V2 field route when the button has no data-url', t => {
    const app = boot(t);
    for (const [fixture, expected] of [
        ['inlineEdit', '/records/key%2Fa?from="table"&mode=edit'],
        ['inlineCustomUrl', '/special/inline?next=%2F&mode=one'],
    ]) {
        const row = app.$('<tr class="child ib-datatable-inline-edit-child"><td></td></tr>');
        row.children('td').append(definitions[fixture]);
        const button = app.$('<button class="ib-datatable-inline-edit-save">Save</button>').appendTo(row.children('td'))[0];
        app.dt.row(0).child(row).show();
        row.find('input').addClass('editorchanged').val('Changed');
        app.window.saveDatatableInlineEditRow(button, { preventDefault() {} });
        assert.equal(app.calls.at(-1).url, expected);
        assert.equal(app.calls.at(-1).payload.fields.notes, 'Changed');
        assert.equal(button.hasAttribute('data-url'), false);
        assert.equal(row.find('input')[0].hasAttribute('data-url'), false);
    }
});

domTest('opening a real inline action preserves the V2 wrapper and restores the button route after saving', t => {
    const app = boot(t);
    app.window.eval(extract(inlineSource, "    $('body').on('click', '.ib-cell-ajax-button', function(e)", '\n});\n\n/**\n * selectRowCheckboxCell'));
    app.window.eval(extract(inlineSource, 'window.__ibRestoreDatatableInlineEditButton = function', 'window.cancelDatatableInlineEditRow = function'));
    app.window.__getDataTable = () => app.dt;
    const button = app.$('<button class="ib-cell-ajax-button" data-inline-edit="true" data-url="/inline-form"><i class="edit-icon"></i></button>')
        .appendTo(app.dt.cell(0, 4).node());
    const send = app.window.ibCallAjax;
    app.window.ibCallAjax = params => params.onSuccess(definitions.inlineEdit, params);
    button.trigger('click');
    app.window.ibCallAjax = send;

    assert.equal(button.hasClass('ib-datatable-inline-edit-save'), true);
    assert.equal(button.attr('data-url'), undefined);
    assert.equal(button.data('url'), undefined);
    const input = app.$(app.dt.row(0).child()).find('.ib-dt-editor-v2');
    assert.equal(input.closest('[data-ib-dt-editor-route]').length, 1);
    input.val('Inline save').addClass('editorchanged');
    app.window.saveDatatableInlineEditRow(button[0], { preventDefault() {} });
    assert.equal(app.calls[0].url, '/records/key%2Fa?from="table"&mode=edit');
    assert.equal(app.calls[0].payload.fields.notes, 'Inline save');
    app.window.__ibRestoreDatatableInlineEditButton(button);
    assert.equal(button.attr('data-url'), '/inline-form');
    assert.equal(button.data('url'), '/inline-form');
});

function historyRuntime(t, options = {}) {
    const app = boot(t, options);
    const { window, $, dt } = app;
    window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.rowPatch.js', 'utf8'));
    window.eval(fs.readFileSync('resources/assets/js/datatables.vendor.editorHistory.js', 'utf8'));
    window.eval(extract(inlineSource, 'window.__ibGetEditorInputType = function', 'window.__ibIsDatatablePriceEditor = function'));
    window.eval(extract(inlineSource, 'window.editorFieldHasChanged = function', 'window.fieldHasUpdateOnSave = function'));
    window.eval(extract(inlineSource, 'window.__ibCompleteDatatableInlineEditSave = function', 'window.__ibRestoreDatatableInlineEditButton = function'));
    window.eval(extract(ajaxSource, '    window.__checkResultGeneric = function', '    window.__getDataTableByClass'));
    window.eval(extract(ajaxSource, '    window.__ibCallAjax = function', '    function ibDtWillRefreshRowAfterSave'));
    Object.assign(window, {
        __ibBeginDtEditorSave() {}, __ibCompleteDtEditorSave() {}, __ibClearDtDirtyGuardSaving() {},
        ibDtBeginLocalRowMutationForTarget() { return null; }, dtRefreshFieldsList() {}, __removeEditorSaveClasses() {},
        __showEditorSavedFeedbackForTarget() {}, __manageSpinnerRemoval() {}, __manageSuccessMessages() {},
        __checkResultToggle() {}, __checkResultAction() { return false; }, __manageRowsRemoving() { return false; },
        __isEditorSaveTarget() { return false; }, __displayResponseErrors() {},
        __ibUnmarkFieldDirty() {}, __ibTogglePerTableSaveButton() {},
        ibDtEditorTargetRequiresErrorReload() { return false; },
        cancelDatatableInlineEditRow() { dt.row(0).child.remove(); },
    });
    const rows = new Map(dt.rows().data().toArray().map(row => [String(row[2]), JSON.parse(JSON.stringify(row))]));
    const requests = [];
    const failures = [];
    window.addDangerNotification = message => failures.push(message);
    window.console.log = () => {};
    dt.ajax.url('/history-rows');
    let failReplayAt = 0;
    let replayNumber = 0;
    function write(request, response) {
        const id = decodeURIComponent(new URL(request.url, window.location.href).pathname.replace(/^\/records\//, ''));
        const direct = rows.get(id) || Array.from(rows.values()).find(row => row[1]?.updateUrl === request.url);
        const row = direct || Array.from(rows.values()).find(row => String(row[3]?.targetId) === id);
        assert.ok(row, request.url);
        const fields = request.data.fields || { [request.data.field]: Object.hasOwn(response, 'value') ? response.value : request.data.value };
        for (const [field, value] of Object.entries(fields)) {
            const index = dt.settings()[0].aoColumns.findIndex((col, index) =>
                (direct ? col.ibDtEditor?.target !== 'element' : col.ibDtEditor?.target === 'element')
                    && $(dt.column(index).header()).attr('data-field') === field);
            assert.ok(index >= 0, field);
            const column = dt.settings()[0].aoColumns[index];
            const format = $(dt.column(index).header()).attr('data-ib-dt-cell-format');
            column.fnSetData(row, window.IlBronza.datatables.editors.helpers.updateCell(column.fnGetData(row), value, format));
        }
    }
    $.ajax = request => {
        const deferred = $.Deferred();
        const record = { request, deferred };
        requests.push(record);
        deferred.done(response => request.success?.(response)).fail(response => request.error?.(response));
        record.succeed = (response = { success: true }) => {
            if (request.type !== 'GET' && response.success !== false) write(request, response);
            deferred.resolve(response);
        };
        record.fail = () => deferred.reject({ status: 422, message: 'Rejected' });
        if (request.type === 'GET')
            deferred.resolve({ data: rows.has(String(request.data.rowId)) ? [JSON.parse(JSON.stringify(rows.get(String(request.data.rowId))))] : [] });
        else if (typeof request.success !== 'function') {
            replayNumber++;
            if (replayNumber === failReplayAt) record.fail();
            else record.succeed({ success: true });
        }
        return deferred.promise();
    };
    window.__refreshRow = () => {
        const row = JSON.parse(JSON.stringify(rows.get('key/a')));
        dt.row(0).data(row);
    };
    async function idle() {
        for (let index = 0; index < 30; index++) {
            if (!window.IlBronza.datatables.editors.history.state(dt).pending && !window.IlBronza.datatables.editors.history.state(dt).busy) return;
            await new Promise(resolve => setTimeout(resolve, 2));
        }
        assert.fail('History did not become idle');
    }
    function save(value, row = 0) {
        const input = app.field(row);
        input.value = value;
        app.save(input);
        return requests.at(-1);
    }
    return { ...app, rows, requests, failures, idle, save,
        history: window.IlBronza.datatables.editors.history,
        failReplay(number) { failReplayAt = number; replayNumber = 0; },
        get value() { return dt.cell(0, 1).data(); },
    };
}

domTest('history records successful saves only and undo/redo write through the normal endpoint', async t => {
    const app = historyRuntime(t);
    const initial = app.value;
    const failed = app.save('Rejected');
    assert.equal(app.history.state(app.dt).undo, 0);
    assert.equal(app.history.state(app.dt).pending, 1);
    assert.equal(await app.history.undo(app.dt), false);
    failed.fail();
    await app.idle();
    assert.equal(app.history.state(app.dt).undo, 0);
    const rejected = app.save('Rejected JSON');
    rejected.succeed({ success: false });
    await app.idle();
    assert.equal(app.history.state(app.dt).undo, 0);

    app.save('Submitted').succeed({ success: true, value: 'Canonical' });
    await app.idle();
    assert.equal(app.history.state(app.dt).undo, 1);
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.value, initial);
    assert.deepEqual([app.history.state(app.dt).undo, app.history.state(app.dt).redo], [0, 1]);
    assert.equal(await app.history.redo(app.dt), true);
    assert.equal(app.value, 'Canonical');
    assert.ok(app.requests.filter(item => item.request.type === 'POST').every(item => item.request.url.includes('/records/key%2Fa')));
});

domTest('history survives redraw/reorder and preserves null, empty and zero states', async t => {
    const app = historyRuntime(t);
    app.dt.cell(0, 1).data(null);
    app.rows.get('key/a')[1] = null;
    app.save('0').succeed({ success: true, value: '0' });
    await app.idle();
    app.dt.column(2).visible(false);
    app.dt.colReorder.move(1, 4);
    app.dt.order([[0, 'desc']]).draw();
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.rows.get('key/a')[1], null);
    assert.equal(await app.history.redo(app.dt), true);
    assert.equal(app.rows.get('key/a')[1], '0');
});

domTest('new successful edits clear redo and failed inverse requests retain the available action', async t => {
    const app = historyRuntime(t);
    app.save('First').succeed({ success: true, value: 'First' });
    await app.idle();
    app.failReplay(1);
    assert.equal(await app.history.undo(app.dt), false);
    assert.deepEqual([app.history.state(app.dt).undo, app.history.state(app.dt).redo], [1, 0]);
    app.failReplay(0);
    assert.equal(await app.history.undo(app.dt), true);
    app.save('New branch').succeed({ success: true, value: 'New branch' });
    await app.idle();
    assert.equal(app.history.state(app.dt).redo, 0);
});

domTest('a changed server value aborts undo before any inverse writes', async t => {
    const app = historyRuntime(t);
    app.save('Mine').succeed({ success: true, value: 'Mine' });
    await app.idle();
    app.rows.get('key/a')[1] = 'Changed elsewhere';
    const writes = app.requests.filter(item => item.request.type === 'POST').length;
    assert.equal(await app.history.undo(app.dt), false);
    assert.equal(app.requests.filter(item => item.request.type === 'POST').length, writes);
    assert.equal(app.rows.get('key/a')[1], 'Changed elsewhere');
    assert.equal(app.history.state(app.dt).undo, 1);
    assert.match(app.history.state(app.dt).lastError, /valore è cambiato/);
});

domTest('Ctrl/Cmd Z keeps native draft undo and replays confirmed changes only for its own table', async t => {
    const app = historyRuntime(t);
    app.save('Saved').succeed({ success: true, value: 'Saved' });
    await app.idle();
    let input = app.field();
    input.value = 'Unsaved';
    let key = new app.window.KeyboardEvent('keydown', { key: 'z', metaKey: true, bubbles: true, cancelable: true });
    input.dispatchEvent(key);
    assert.equal(key.defaultPrevented, false);
    assert.equal(app.history.state(app.dt).undo, 1);
    input.value = input.getAttribute('data-originalvalue');
    key = new app.window.KeyboardEvent('keydown', { key: 'z', ctrlKey: true, bubbles: true, cancelable: true });
    input.dispatchEvent(key);
    assert.equal(key.defaultPrevented, true);
    await app.idle();
    assert.equal(app.value, 'Note one');
    input = app.field();
    key = new app.window.KeyboardEvent('keydown', { key: 'z', metaKey: true, shiftKey: true, bubbles: true, cancelable: true });
    input.dispatchEvent(key);
    assert.equal(key.defaultPrevented, true);
    await app.idle();
    assert.equal(app.value, 'Saved');
    const other = app.$('<table id="other-history"><thead><tr><th>ID</th></tr></thead></table>').appendTo(app.document.body).DataTable({ data: [[1]], paging: false });
    assert.equal(app.history.state(other).undo, 0);
    assert.equal(await app.history.undo(other), false);
});

domTest('bulk successes are grouped, including partial failures, and undo/redo remains consistent after an inverse failure', async t => {
    const app = historyRuntime(t);
    const group = app.history.beginGroup(app.dt);
    app.window.ibDtEditorUpdateFromElement(app.field(0), 'Bulk', null, group);
    const first = app.requests.at(-1);
    app.window.ibDtEditorUpdateFromElement(app.field(1), 'Bulk', null, group);
    const second = app.requests.at(-1);
    app.history.endGroup(group);
    first.succeed({ success: true, value: 'Bulk' });
    assert.equal(app.history.state(app.dt).undo, 0);
    second.succeed({ success: true, value: 'Bulk' });
    await app.idle();
    assert.equal(app.history.state(app.dt).undo, 1);
    app.failReplay(2);
    assert.equal(await app.history.undo(app.dt), false);
    assert.deepEqual([app.history.state(app.dt).undo, app.history.state(app.dt).redo], [1, 1]);
    app.failReplay(0);
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.rows.get('key/a')[1], 'Note one');
    assert.equal(app.rows.get('second/b')[1], 'Note two');
});

domTest('inline batches enter history only on success and are replayed as a batch', async t => {
    const app = historyRuntime(t, { renamedField: true });
    const row = app.$('<tr class="child ib-datatable-inline-edit-child"><td></td></tr>');
    row.children('td').append(definitions.inlineEdit);
    const button = app.$('<button class="ib-datatable-inline-edit-save">Save</button>').appendTo(row.children('td'))[0];
    app.dt.row(0).child(row).show();
    row.find('input').addClass('editorchanged').val('Batch');
    app.window.saveDatatableInlineEditRow(button, { preventDefault() {} });
    const request = app.requests.at(-1);
    assert.equal(app.history.state(app.dt).undo, 0);
    request.succeed({ success: true, action: 'refreshRow', fields: ['notes'] });
    await app.idle();
    assert.equal(app.history.state(app.dt).undo, 1);
    assert.equal(await app.history.undo(app.dt), true);
    const inverse = app.requests.filter(item => item.request.type === 'POST').at(-1).request;
    assert.equal(inverse.data['ib-editor-batch'], true);
    assert.equal(inverse.data.fields.notes, 'Note one');
});

domTest('successful empty responses use the server row and overlapping successful saves form a reversible chain', async t => {
    const app = historyRuntime(t);
    const first = app.save('First');
    const second = app.save('Second');
    first.succeed({ success: true, value: 'First' });
    second.succeed({ success: true, value: 'Second' });
    await app.idle();
    assert.equal(app.history.state(app.dt).undo, 2);
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.value, 'First');
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.value, 'Note one');
    const empty = app.save('Submitted 204');
    app.rows.get('key/a')[1] = 'Server 204';
    empty.deferred.resolve(undefined);
    await app.idle();
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.value, 'Note one');
    assert.equal(await app.history.redo(app.dt), true);
    assert.equal(app.value, 'Server 204');
});

domTest('history resolves current custom URLs and refuses a changed related-model target', async t => {
    const app = historyRuntime(t, { customUrl: true });
    app.save('Changed').succeed({ success: true, value: 'Changed' });
    await app.idle();
    app.rows.get('key/a')[1].updateUrl = '/special/changed';
    assert.equal(await app.history.undo(app.dt), true);
    assert.equal(app.requests.filter(item => item.request.type === 'POST').at(-1).request.url, '/special/changed');

    const child = app.field(0, 3);
    child.value = 'Changed child';
    app.window.ibCallAjax({ target: child, e: { preventDefault() {} }, data: {
        'ib-editor': true, field: 'title', value: 'Changed child', _method: 'PUT',
    } });
    app.requests.at(-1).succeed({ success: true, value: 'Changed child' });
    await app.idle();
    app.rows.get('key/a')[3].targetId = 'another-child';
    const count = app.requests.filter(item => item.request.type === 'POST').length;
    assert.equal(await app.history.undo(app.dt), false);
    assert.equal(app.requests.filter(item => item.request.type === 'POST').length, count);
    assert.match(app.history.state(app.dt).lastError, /destinatario/);
});

domTest('a partly rejected bulk records only confirmed members and retains request context on undo', async t => {
    const app = historyRuntime(t);
    const group = app.history.beginGroup(app.dt);
    app.window.ibDtEditorUpdateFromElement(app.field(0), 'Bulk', null, group);
    const first = app.requests.at(-1);
    app.window.ibDtEditorUpdateFromElement(app.field(1), 'Bulk', null, group);
    const second = app.requests.at(-1);
    app.history.endGroup(group);
    first.succeed({ success: true, value: 'Bulk' });
    second.fail();
    await app.idle();
    assert.equal(app.history.state(app.dt).undo, 1);
    assert.equal(await app.history.undo(app.dt), true);
    const inverse = app.requests.filter(item => item.request.type === 'POST').at(-1).request;
    assert.equal(inverse.data.fieldExtraData.field, 'title');
    assert.equal(app.rows.get('key/a')[1], 'Note one');
    assert.equal(app.rows.get('second/b')[1], 'Note two');
});

domTest('history is bounded in memory and clear resets both stacks', async t => {
    const app = historyRuntime(t);
    for (let index = 0; index < 101; index++)
        app.save('Value ' + index).succeed({ success: true, value: 'Value ' + index });
    await app.idle();
    assert.equal(app.history.state(app.dt).undo, 100);
    assert.equal(app.history.clear(app.dt), true);
    assert.deepEqual([app.history.state(app.dt).undo, app.history.state(app.dt).redo], [0, 0]);
});
