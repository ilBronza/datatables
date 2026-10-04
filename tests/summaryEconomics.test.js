const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

const moduleSource = fs.readFileSync(
    'resources/assets/js/datatables.vendor.summary.min.js',
    'utf8'
);

function boot()
{
    const hooksMarker = '    /* ===============================\n     *  Recalculate summary on row selection changes';
    const hooks = [
        '    window.__ibDatatablesSummaryTestHooks = {',
        '        calculateSummaryValues,',
        '        formatLocalizedNumber,',
        '        resolveSummaryNumberFormat,',
        '        summaryHandlers',
        '    };',
        '',
    ].join('\n');

    assert.notEqual(moduleSource.indexOf(hooksMarker), -1);

    const window = {
        __ibDatatablesSummaryFormats: {
            economics: {
                decimalSep: ',',
                thousandsSep: '.',
                fractionDigits: 2,
            },
        },
    };

    function $(node)
    {
        return {
            on() { return this; },
        };
    }

    $.fn = {
        dataTable: {
            Api: {
                register() {},
            },
        },
    };

    vm.runInNewContext(moduleSource.replace(hooksMarker, hooks + hooksMarker), {
        $,
        document: {},
        setTimeout() {},
        window,
    });

    return window.__ibDatatablesSummaryTestHooks;
}

function rows(values)
{
    return {
        every(callback) {
            values.forEach(function(value, index) {
                const hasRaw = value !== null && typeof value === 'object' && 'raw' in value;
                const raw = hasRaw ? value.raw : value;
                const display = hasRaw ? value.display ?? raw : raw;

                callback.call({
                    cell() {
                        return {
                            data() { return raw; },
                            render() { return display; },
                        };
                    },
                }, index);
            });
        },
    };
}

test('sumEconomics parses and renders Italian-formatted economic values', () => {
    const summary = boot();
    const sourceRows = rows(['1.234,56', '2.000,00']);
    const numberFormat = summary.resolveSummaryNumberFormat(
        summary.summaryHandlers.sumEconomics,
        { every() { throw new Error('sumEconomics must not detect the column format'); } },
        0,
        { every() { throw new Error('sumEconomics must not detect the column format'); } }
    );

    const total = summary.calculateSummaryValues(
        sourceRows,
        0,
        summary.summaryHandlers.sumEconomics,
        false,
        numberFormat
    );

    assert.equal(total, 3234.56);
    assert.equal(summary.formatLocalizedNumber(total, numberFormat), '3.234,56');
});

test('sumEconomics supports numeric raw values and always renders economics format', () => {
    const summary = boot();
    const sourceRows = rows([1234.56, 2000]);
    const numberFormat = summary.resolveSummaryNumberFormat(
        summary.summaryHandlers.sumEconomics,
        sourceRows,
        0,
        sourceRows
    );
    const total = summary.calculateSummaryValues(
        sourceRows,
        0,
        summary.summaryHandlers.sumEconomics,
        false,
        numberFormat
    );

    assert.equal(total, 3234.56);
    assert.equal(summary.formatLocalizedNumber(total, numberFormat), '3.234,56');
});

test('sumEconomics reads decimal-point raw strings alongside Italian display values', () => {
    const summary = boot();
    const sourceRows = rows([
        { raw: '1000.000', display: '1.000,00' },
        { raw: '53851.30', display: '53.851,30' },
        { raw: '1,25', display: '1,25' },
    ]);
    const numberFormat = summary.resolveSummaryNumberFormat(
        summary.summaryHandlers.sumEconomics, sourceRows, 0, sourceRows
    );
    const total = summary.calculateSummaryValues(
        sourceRows, 0, summary.summaryHandlers.sumEconomics, false, numberFormat
    );

    assert.equal(total, 54852.55);
    assert.equal(summary.formatLocalizedNumber(total, numberFormat), '54.852,55');
});

test('sumEconomics disambiguates a single dot using the displayed cell', () => {
    const summary = boot();
    const sourceRows = rows([
        { raw: '1.234', display: '1,23' },
        { raw: '1.234', display: '1.234,00' },
        { raw: '1.234', display: '1.234 €' },
    ]);
    const numberFormat = summary.resolveSummaryNumberFormat(
        summary.summaryHandlers.sumEconomics, sourceRows, 0, sourceRows
    );
    const total = summary.calculateSummaryValues(
        sourceRows, 0, summary.summaryHandlers.sumEconomics, false, numberFormat
    );

    assert.equal(total, 2469.234);
    assert.equal(summary.formatLocalizedNumber(total, numberFormat), '2.469,23');
});

test('sumEconomics accepts strings formatted with either decimal separator', () => {
    const summary = boot();
    const sourceRows = rows(['1.234,56', '1,234.56']);
    const numberFormat = summary.resolveSummaryNumberFormat(
        summary.summaryHandlers.sumEconomics, sourceRows, 0, sourceRows
    );
    const total = summary.calculateSummaryValues(
        sourceRows, 0, summary.summaryHandlers.sumEconomics, false, numberFormat
    );

    assert.equal(total, 2469.12);
});

test('sum keeps detecting the format from its column values', () => {
    const summary = boot();
    const sourceRows = rows(['1,234.56', '2,000.00']);
    const numberFormat = summary.resolveSummaryNumberFormat(
        summary.summaryHandlers.sum,
        sourceRows,
        0,
        sourceRows
    );
    const total = summary.calculateSummaryValues(
        sourceRows,
        0,
        summary.summaryHandlers.sum,
        false,
        numberFormat
    );

    assert.equal(total, 3234.56);
    assert.equal(summary.formatLocalizedNumber(total, numberFormat), '3,234.56');
});
