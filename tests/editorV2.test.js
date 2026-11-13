const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const test = require('node:test');
const vm = require('node:vm');

const runtimeSource = ['editorHelpers', 'numericHelpers', 'editors', 'numericEditor'].map(name =>
    fs.readFileSync('resources/assets/js/datatables.vendor.' + name + '.js', 'utf8')).join('\n');
const ajaxSource = fs.readFileSync('resources/assets/js/datatables.vendor.ajaxCall.min.js', 'utf8');
const bootstrap = process.env.IB_DT_BOOTSTRAP || (fs.existsSync('vendor/autoload.php')
    ? 'vendor/autoload.php' : '../.ibtest/bootstrap_datatables.php');
const generated = spawnSync(process.env.IB_DT_PHP || 'php', [
    '-d', 'error_reporting=8191', 'tests/fixtures/editorV2Fixtures.php.inc', path.resolve(bootstrap),
], { encoding: 'utf8' });
assert.equal(generated.status, 0, generated.error?.message || generated.stderr || generated.stdout);
const definitions = JSON.parse(generated.stdout);

function boot()
{
    const context = { window: {} };
    vm.runInNewContext(runtimeSource, context);
    return {
        window: context.window,
        column(name) { return vm.runInNewContext('(' + definitions[name] + ')', context); },
    };
}

function inputAttributes(html)
{
    const match = html.match(/<input\b([^>]*)\/>/);
    assert.ok(match, html);
    const result = {};
    for (const attribute of match[1].matchAll(/([\w-]+)="([^"]*)"/g))
    {
        assert.equal(Object.hasOwn(result, attribute[1]), false, 'duplicate attribute ' + attribute[1]);
        result[attribute[1]] = attribute[2].replace(/&(quot|#039|lt|gt|amp);/g,
            (_, entity) => ({ quot: '"', '#039': "'", lt: '<', gt: '>', amp: '&' })[entity]);
    }
    return result;
}

test('PHP-generated simple renderer reads ID from the row and preserves original data', () => {
    const runtime = boot();
    const column = runtime.column('plain');
    for (const value of [null, '', 0, '0', '001', 'Città "<&\' </script>'])
    {
        const row = [null, value, 'key/a'];
        const attributes = inputAttributes(column.render(value, 'display', row, {}));
        assert.equal(attributes.value, String(value ?? ''));
        assert.equal(attributes['data-originalvalue'], String(value ?? ''));
        assert.equal(attributes['data-url'], undefined);
        assert.equal(attributes['data-save-trigger'], undefined);
        assert.equal(attributes['data-ib-dt-cell-format'], undefined);
        assert.equal(attributes['data-ib-dt-value-type'], undefined);
        assert.equal(attributes['data-field'], undefined);
        assert.equal(definitions.plainHeaderData['ib-dt-cell-format'], 'scalar');
        assert.equal(attributes['data-ib-dt-target-id'], undefined);
        assert.equal(row[1], value);
        for (const mode of ['filter', 'sort', 'export', 'type'])
            assert.equal(column.render(value, mode, row, {}), value);
    }
});

test('conditional renderer reads disabled directly and does not mutate metadata', () => {
    const column = boot().column('conditional');
    for (const disabled of [false, true])
    {
        const cell = { value: 'Note', disabled };
        const html = column.render(cell, 'display', [null, cell, 42], {});
        assert.equal(/ disabled(?:[ =])/.test(html), disabled);
        assert.deepEqual(cell, { value: 'Note', disabled });
        assert.equal(inputAttributes(html)['data-ib-dt-cell-format'], undefined);
        assert.equal(definitions.conditionalHeaderData['ib-dt-cell-format'], 'value');
        for (const mode of ['filter', 'sort', 'export', 'type'])
            assert.equal(column.render(cell, mode, [], {}), 'Note');
    }
});

test('static disabled needs no structured payload, and missing target IDs disable editing', () => {
    const runtime = boot();
    assert.match(runtime.column('disabled').render('Note', 'display', [null, null, 42], {}), / disabled/);
    assert.match(runtime.column('foreign').render({ value: null, targetId: null }, 'display', [], {}), / disabled/);
    assert.doesNotMatch(runtime.column('plain').render('Note', 'display', [null, null, 0], {}), / disabled/);
});

test('related-model renderer retains its target ID without building a URL', () => {
    const column = boot().column('foreign');
    const attributes = inputAttributes(column.render({ value: 'Child', targetId: 'child/a' }, 'display', [null, null, 'parent'], {}));
    assert.equal(attributes['data-url'], undefined);
    assert.equal(column.ibDtEditor.target, 'element');
    assert.equal(attributes['data-ib-dt-target-id'], 'child/a');
});

test('PHP emits one route template per editable column, outside the render function', () => {
    const route = boot().column('plain').ibDtEditor;
    assert.equal(route.urlTemplate, '/records/__ROW_ID__?from="table"&mode=edit');
    assert.equal(route.idPlaceholder, '__ROW_ID__');
    assert.equal(route.target, 'row');
    assert.equal(boot().column('denied').ibDtEditor, undefined);
    assert.equal(definitions.plain.split('records').length - 1, 1);
    assert.doesNotMatch(boot().column('plain').render.toString(), /data-url|encodeURIComponent|\/records\//);
    assert.doesNotMatch(boot().column('plain').render.toString(), /data-save-trigger/);
});

test('custom update URLs remain in the PHP cell JSON and never enter the input or orthogonal values', () => {
    const column = boot().column('customUrl');
    assert.equal(column.ibDtEditor.updateUrlFromCell, true);
    for (const cell of definitions.customUrlCells) {
        const original = JSON.stringify(cell);
        const html = column.render(cell, 'display', [null, cell, 'key/a'], {});
        assert.equal(inputAttributes(html).value, '001');
        assert.equal(inputAttributes(html)['data-url'], undefined);
        assert.doesNotMatch(html, /\/special\/|updateUrl/);
        for (const mode of ['filter', 'sort', 'export', 'type'])
            assert.equal(column.render(cell, mode, [], {}), '001');
        assert.equal(JSON.stringify(cell), original);
    }
});

test('configured attributes, classes and affixes are escaped and target the right elements', () => {
    const column = boot().column('attributes');
    const html = column.render('"<script>&\'', 'display', [null, null, 42], {});
    const attrs = inputAttributes(html);
    assert.equal(attrs.placeholder, '"<b>&\'');
    assert.equal(attrs['data-context'], '"<script>&\'');
    assert.equal(attrs['data-flag'], 'false');
    assert.equal(attrs['data-field'], undefined);
    assert.equal(definitions.attributesHeaderData.field, 'notes');
    assert.equal(attrs['data-kind'], undefined);
    assert.equal(attrs['data-save-trigger'], undefined);
    assert.match(attrs.class, /custom configured/);
    assert.match(html, /^<div class="outer ib-suffix-container">/);
    assert.match(html, /&lt;pre&gt;/);
    assert.doesNotMatch(html, /<script>|<b>/);
    assert.equal(column.render('Value', 'export', [], {}), 'Value');
    const classes = ['datatable-class'];
    const cellAttrs = {};
    column.createdCell({ classList: { add(...items) { classes.push(...items); } },
        setAttribute(name, value) { cellAttrs[name] = value; } });
    assert.deepEqual(classes, ['datatable-class', 'marked', 'wide']);
    assert.deepEqual(cellAttrs, { 'data-kind': '"<&' });
});

test('permission-denied display is escaped text and all other modes retain the value', () => {
    const column = boot().column('denied');
    assert.equal(column.render('<script>', 'display', [], {}), '&lt;script&gt;');
    assert.equal(column.render('<script>', 'export', [], {}), '<script>');
});

function saveRuntime(initial, format)
{
    let data = initial;
    const td = {};
    const target = { 'data-ib-dt-cell-format': format, 'data-ib-dt-target-id': 'child' };
    const cell = {
        data(value) { if (!arguments.length) return data; data = value; return this; },
        draw() { return this; }, index() { return { row: 0, column: 1 }; },
    };
    const table = { cell() { return cell; } };
    const $ = element => ({
        length: 1,
        hasClass() { return false; },
        parents(selector) { return selector === 'td' ? td : { length: 1, DataTable() { return table; } }; },
        attr(name) { return element[name]; }, focus() {},
    });
    td.addClass = () => {};
    const context = { window: boot().window, $, __isEditorSaveTarget() { return false; } };
    context.window.jQuery = $;
    const start = ajaxSource.indexOf('    window.__checkResultGeneric = function');
    const end = ajaxSource.indexOf('    window.__getDataTableByClass', start);
    assert.ok(start >= 0 && end > start);
    vm.runInNewContext(ajaxSource.slice(start, end), context);
    return {
        save(result, submitted = 'submitted') {
            context.window.__checkResultGeneric(result, { target, data: { field: 'notes', value: submitted } });
            return JSON.parse(JSON.stringify(data));
        },
    };
}

test('AJAX response commits scalar values, including null, server-normalized values and empty responses', () => {
    for (const original of [null, '', 0, 'old'])
    {
        assert.equal(saveRuntime(original, 'scalar').save({ notes: 'server' }), 'server');
        assert.equal(saveRuntime(original, 'scalar').save({ value: null }), null);
        assert.equal(saveRuntime(original, 'scalar').save({}, '001'), '001');
    }
    assert.equal(saveRuntime('old', 'scalar').save({ success: false, notes: 'wrong' }), 'old');
});

test('AJAX response preserves structured metadata and only updates the selected child', () => {
    const source = { value: 'old', disabled: false, targetId: 'child', updateUrl: '/special/child' };
    assert.deepEqual(saveRuntime(source, 'value').save({ notes: 'new' }), { ...source, value: 'new' });
    assert.equal(source.value, 'old');
    const other = { value: 'other', targetId: 'other' };
    assert.deepEqual(saveRuntime([source, other], 'value').save({ notes: 'new' }), [{ ...source, value: 'new' }, other]);
});

test('legacy AJAX cells retain the positional update contract', () => {
    assert.deepEqual(saveRuntime([42, 'old'], undefined).save({ notes: 'new' }), [42, 'new']);
});
