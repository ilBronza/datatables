const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
let JSDOM;
try { ({ JSDOM } = require('jsdom')); require.resolve('jquery'); require.resolve('datatables.net'); }
catch (_) { JSDOM = null; }
const domTest = (name, fn) => test(name, { skip: !JSDOM && 'Requires the optional DOM test dependencies' }, fn);

async function boot(t, values, { type = 'numeric', filterType = 'text', render } = {}) {
    const filter = (prefix) => '<tr class="columns"><th data-column="0" data-filter-type="' + filterType
        + '" data-range-filter="1" data-filter-events="change"><input id="' + prefix + 'start"><input id="' + prefix + 'end"></th></tr>';
    const dom = new JSDOM('<!doctype html><table id="listings" data-realid="listings">'
        + '<thead class="sectionheader"><tr><th>Price</th></tr>' + filter('price') + '</thead>'
        + '<tbody></tbody><tfoot class="sectionfooter">' + filter('footerprice') + '</tfoot></table>',
    { runScripts: 'outside-only', url: 'https://example.test' });
    const { window } = dom;
    t.after(() => window.close());
    window.eval(fs.readFileSync(require.resolve('jquery'), 'utf8'));
    window.eval(fs.readFileSync(require.resolve('datatables.net'), 'utf8'));
    for (const file of ['filtering', 'sorting']) {
        window.eval(fs.readFileSync(path.join(__dirname, '../resources/assets/js/datatables.vendor.' + file + '.min.js'), 'utf8'));
    }
    const $ = window.jQuery;
    await new Promise(resolve => $(resolve));
    const table = $('#listings').DataTable({
        data: values.map(value => [value]), columns: [{ type, render }], order: [[0, 'asc']],
        paging: false, info: false,
    });

    return {
        table,
        prices: () => Array.from(table.rows({ search: 'applied', order: 'applied' }).data(), row => row[0]),
        range(min = '', max = '', footerMin = '', footerMax = '') {
            for (const [id, value] of Object.entries({ pricestart: min, priceend: max, footerpricestart: footerMin, footerpriceend: footerMax })) {
                $('#' + id).val(value);
            }
            $('#priceend').trigger('change');
        },
    };
}

domTest('legacy numeric columns order decimal strings by value in both directions', async t => {
    const app = await boot(t, ['10000.00', '9000.00', '23000.00', '3000.00', '9999.99', '0.00']);

    assert.deepEqual(app.prices(), ['0.00', '3000.00', '9000.00', '9999.99', '10000.00', '23000.00']);
    app.table.order([[0, 'desc']]).draw();
    assert.deepEqual(app.prices(), ['23000.00', '10000.00', '9999.99', '9000.00', '3000.00', '0.00']);
});

domTest('a maximum price of 23000 includes cheaper prices and the boundary but excludes unknown prices', async t => {
    const app = await boot(t, ['3000.00', '9000.00', '10000.00', '23000.00', '23000.01', null, '']);

    app.range('', '23000');

    assert.deepEqual(app.prices(), ['3000.00', '9000.00', '10000.00', '23000.00']);
});

domTest('minimum prices and exclusion intervals use inclusive numeric boundaries', async t => {
    const app = await boot(t, ['9000.00', '10000.00', '12000.00', '15000.00', '23000.00', null]);

    app.range('10000', '', '12000', '15000');

    assert.deepEqual(app.prices(), ['10000.00', '23000.00']);
});

domTest('blank bounds do not constrain results and zero remains a valid bound', async t => {
    const app = await boot(t, [null, '', '0.00', '1.00', '-1.00']);

    app.range('', '0');
    assert.deepEqual(app.prices(), ['-1.00', '0.00']);
    app.range('0', '0');
    assert.deepEqual(app.prices(), ['0.00']);
    app.range(' ', ' ');
    assert.equal(app.prices().length, 5);
});

domTest('exclusion-only ranges preserve unknown values and clear without leaving a filter', async t => {
    const app = await boot(t, [null, '', '0.00', '9000.00', '23000.00']);

    app.range('', '', '', '9000');
    assert.deepEqual(app.prices().filter(value => value !== null && value !== ''), ['23000.00']);
    assert.equal(app.prices().length, 3);
    app.range();
    assert.equal(app.prices().length, 5);
});

domTest('editor ranges compare the rendered numeric value rather than the row id', async t => {
    const app = await boot(t, [[42, '3000.00'], [43, '9000.00'], [44, '25000.00']], {
        render: item => item[1],
    });

    app.range('', '23000');

    assert.deepEqual(app.prices(), [[42, '3000.00'], [43, '9000.00']]);
});

domTest('date ranges include the whole final day', async t => {
    const values = ['2026-10-17T23:59:59', '2026-10-18T00:00:00', '2026-10-18T23:59:59', '2026-10-19T00:00:00'];
    const app = await boot(t, values, {
        type: 'num', filterType: 'date', render: value => new Date(value).getTime() / 1000,
    });

    app.range('2026-10-18', '2026-10-18');

    assert.deepEqual(app.prices(), ['2026-10-18T00:00:00', '2026-10-18T23:59:59']);
});
