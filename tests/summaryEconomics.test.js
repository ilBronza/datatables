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
                callback.call({
                    cell() {
                        return {
                            data() { return value; },
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
