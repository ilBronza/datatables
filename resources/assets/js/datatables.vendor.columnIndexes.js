/* Translate immutable backend indexes to current DataTables data indexes. */
(function registerIlBronzaDatatablesColumnIndexes($) {
    'use strict';

    if (!$ || !$.fn.dataTable || !$.fn.dataTable.Api) return;

    function parseIndex(value) {
        if (typeof value === 'string' && /^\d+$/.test(value.trim())) value = Number(value.trim());
        return typeof value === 'number' && Number.isSafeInteger(value) && value >= 0 ? value : null;
    }

    $.fn.dataTable.Api.register('columnIndexFromOriginal()', function (originalIndex) {
        const original = parseIndex(originalIndex);
        const settings = this.settings();
        if (original === null || settings.length !== 1) return null;

        let current = null;
        // DataTables keeps this matrix in current column order, including hidden/detached
        // headers. Reading DOM positions or visible indexes would shift hidden fields.
        // Search every header row: the primary header can be a grouped colspan cell.
        for (const row of settings[0].aoHeader || []) {
            for (let index = 0; index < row.length; index++) {
                const cell = row[index] && row[index].cell;
                if (!cell || parseIndex(cell.getAttribute('data-original-column-index')) !== original) continue;
                // Ambiguous metadata must not silently select a different field.
                if (current !== null && current !== index) return null;
                current = index;
            }
        }
        return current;
    });
})(typeof window === 'undefined' ? null : window.jQuery);
