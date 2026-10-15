(function (window) {
    'use strict';

    const entities = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };

    // Only escaping is shared at render time. PHP compiles each column's markup.
    window.ibDtEscapeHtml = function (value) {
        return String(value ?? '').replace(/[&<>"']/g, character => entities[character]);
    };

    // The format is emitted by the PHP column, not inferred from its value.
    window.ibDtUpdateEditorV2Cell = function (cell, value, format, targetId) {
        if (format === 'scalar')
            return value;

        if (format !== 'value')
            throw new Error('Unknown editor cell format: ' + format);

        // An existing each container can embed the V2 display body. Its child
        // target ID identifies which entry receives the successful update.
        if (Array.isArray(cell))
            return cell.map(entry => entry && entry.targetId != null && String(entry.targetId) === String(targetId)
                ? { ...entry, value } : entry);

        return { ...cell, value };
    };
})(window);
