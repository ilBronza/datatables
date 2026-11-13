const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');
const context = { window: {} };
for (const name of ['editorHelpers', 'numericHelpers'])
    vm.runInNewContext(fs.readFileSync('resources/assets/js/datatables.vendor.' + name + '.js', 'utf8'), context);
const numbers = context.window.IlBronza.datatables.editors.helpers.numbers;
const italian = { decimalSeparator: ',', thousandsSeparator: '.', decimals: 2, fixedDecimals: true };

test('decimal canonicalization preserves large integers, fractional digits and trailing zeroes', () => {
    for (const [raw, expected] of [
        ['9007199254740993.123456789012345678900', '9007199254740993.123456789012345678900'],
        ['+00012.3400', '12.3400'], ['-.00001', '-0.00001'], ['-0.00', '0.00'],
        ['1.234500e-7', '0.0000001234500'], ['12e+5', '1200000'], ['1e-30', '0.' + '0'.repeat(29) + '1'],
        [0, '0'], [null, null], ['', null],
    ]) assert.equal(numbers.canonical(raw), expected);
    for (const raw of [true, 'NaN', 'Infinity', '12abc', '1.2.3', 9007199254740992, Infinity, '1e10001'])
        assert.throws(() => numbers.canonical(raw));
});

test('localized input accepts either decimal mark while grouping is explicit and validated', () => {
    for (const [input, expected] of [
        ['12,202', '12.202'], ['12.202', '12.202'], ['-1.234.567,890123', '-1234567.890123'],
        ['1.234.567', '1234567'], [',25', '0.25'], ['12,', '12'], ['0', '0'], ['', null],
    ]) assert.equal(numbers.parse(input, italian), expected);
    assert.equal(numbers.parse('1.234', italian, false), '1234');
    assert.equal(numbers.parse('1 234,567', { ...italian, thousandsSeparator: ' ' }), '1234.567');
    assert.equal(numbers.parse('1,234.567', { decimalSeparator: '.', thousandsSeparator: ',' }), '1234.567');
    for (const input of ['12 euro', '12,3,4', '12.34,56', '--2', '1,234.56', '1e3', '-'])
        assert.throws(() => numbers.parse(input, italian), input);
});

test('display rounding never changes the canonical value and works beyond floating-point precision', () => {
    for (const [raw, expected] of [
        ['12.3456', '12,35'], ['999.999', '1.000,00'], ['-999.995', '-1.000,00'],
        ['9007199254740993.129999999999', '9.007.199.254.740.993,13'], ['-0.00001', '0,00'],
        [null, ''], ['0', '0,00'],
    ]) {
        assert.equal(numbers.format(raw, italian), expected);
        assert.equal(numbers.edit(raw, italian), raw === null ? '' : raw.replace('.', ','));
    }
    assert.equal(numbers.format('12.500', { ...italian, fixedDecimals: false }), '12,5');
    assert.equal(numbers.format('1234.12345678901234567890', { decimals: null, thousandsSeparator: '' }), '1234,12345678901234567890');
    assert.equal(numbers.format('999999999999999999.5', { decimals: 0 }), '1.000.000.000.000.000.000');
});

test('comparison, min/max, precision and step use exact decimals and distinguish zero from null', () => {
    assert.equal(numbers.equal('1.000', '1'), true);
    assert.equal(numbers.equal('0', null), false);
    assert.equal(numbers.equal('9007199254740993.00000001', '9007199254740993.00000002'), false);
    assert.equal(numbers.compare('-9007199254740993', '-9007199254740992'), -1);
    const options = { min: '9007199254740993.03', max: '9007199254740993.13', step: '0.05', maxDecimals: 2 };
    assert.equal(numbers.validate('9007199254740993.0800', options), '9007199254740993.0800');
    for (const value of ['9007199254740993.02', '9007199254740993.14', '9007199254740993.07', '9007199254740993.031'])
        assert.throws(() => numbers.validate(value, options), value);
    assert.equal(numbers.validate('0.00000000000000000003', { step: '0.00000000000000000001' }), '0.00000000000000000003');
    assert.throws(() => numbers.validate(null, { nullable: false }));
    assert.throws(() => numbers.validate('1.01', { maxDecimals: 0 }));
    assert.equal(numbers.validate('0', { min: '0', max: '0', nullable: false }), '0');
    assert.equal(numbers.validate('12.3456789', { decimals: 2 }), '12.3456789');
});
