const assert = require('node:assert/strict');
const test = require('node:test');
const styles = require('../resources/assets/js/datatables.vendor.columnStyles.js');
const fixtures = require('./fixtures/columnStyles.json');

for (const fixture of fixtures) {
    test(fixture.name, () => {
        assert.deepEqual(JSON.parse(JSON.stringify(styles.normalize(fixture.selectors))), fixture.normalized);
    });
}

test('legacy branches become separate scoped CSS selectors', () => {
    const css = styles.css('orders', 'customer', {'td a i + td a span': {color:'#123456'}});
    assert.equal(css.split(',').length, 2);
    assert.equal((css.match(/td\[class~="customer"\]/g) || []).length, 2);
    assert.ok(css.includes(' a i,') && css.includes(' a span{'));
    assert.ok(!css.includes(' + '));
});

test('overflow pseudo-properties compile into real CSS declarations', () => {
    const css = styles.css('orders', 'customer', {'td a': {'text-overflow-mode':'tooltip', __tooltip:true}});
    assert.ok(css.includes('white-space:nowrap!important'));
    assert.ok(css.includes('text-overflow:ellipsis!important'));
    assert.ok(!css.includes('__tooltip') && !css.includes('text-overflow-mode'));
});

test('column widths also target headers and fixed-header clones', () => {
    const css = styles.css('orders', 'customer', {td:{width:'240px'}});
    assert.ok(css.includes('th[class~="customer"]'));
    assert.ok(css.includes('[id="orders_wrapper"]'));
    assert.ok(css.includes('table[data-realid="orders"]'));
});

test('hostile identifiers cannot terminate a style element', () => {
    const css = styles.css('orders</style>', 'customer"><script>', {td:{color:'red'}});
    assert.ok(!css.includes('<') && !css.includes('>'));
    assert.ok(css.includes('color:red!important'));
});

test('resetting all rules produces an empty stylesheet', () => {
    assert.equal(styles.css('orders','customer',{}), '');
    assert.equal(styles.css('orders','customer',{td:{}}), '');
});
