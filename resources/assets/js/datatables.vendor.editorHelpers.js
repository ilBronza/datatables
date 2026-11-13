(function (window) {
    'use strict';

    const datatables = (window.IlBronza = window.IlBronza || {}).datatables ||= {};
    const editors = datatables.editors ||= {};
    const entities = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };

    editors.helpers = Object.freeze({
        numbers: {},
        escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => entities[character]);
        },
        clone(value) { return value === undefined ? null : JSON.parse(JSON.stringify(value)); },
        same(a, b) { return JSON.stringify(a) === JSON.stringify(b); },
        displayedSame(a, b) { return String(a ?? '') === String(b ?? ''); },
        hasId(value) { return value !== null && value !== undefined && value !== ''; },
        valueOf(raw, format) { return this.clone(format === 'value' ? raw?.value : raw); },

        updateCell(cell, value, format, targetId) {
            if (format === 'scalar') return value;
            if (format !== 'value') throw new Error('Unknown editor cell format: ' + format);
            if (Array.isArray(cell))
                return cell.map(entry => entry && entry.targetId != null && String(entry.targetId) === String(targetId)
                    ? { ...entry, value } : entry);
            return { ...cell, value };
        },

        resolveUrl(route, targetId, updateUrl) {
            if (typeof route?.storeUrl === 'string' && route.storeUrl.length) return route.storeUrl;
            if (typeof updateUrl === 'string' && updateUrl.trim().length) return updateUrl.trim();
            if (!route || typeof route.urlTemplate !== 'string' || !route.urlTemplate.length
                || typeof route.idPlaceholder !== 'string' || !route.idPlaceholder.length)
                throw new Error('Editor URL requires a template and an ID placeholder.');
            if (!this.hasId(targetId)) throw new Error('Editor URL requires a target ID.');
            return route.urlTemplate.split(route.idPlaceholder).join(encodeURIComponent(String(targetId)));
        },

        table(table) {
            if (table && typeof table.settings === 'function') return table;
            const $ = window.jQuery;
            const node = $(table)[0];
            return node && $.fn.dataTable.isDataTable(node) ? $(node).DataTable() : null;
        },
        columnName(settings, index) {
            for (const row of settings.aoHeader || []) {
                const name = row[index]?.cell?.getAttribute('data-field-name');
                if (name) return name;
            }
            return null;
        },
        columnIndex(settings, name) {
            return settings.aoColumns.findIndex((column, index) => this.columnName(settings, index) === name);
        },
    });
})(window);
