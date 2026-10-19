/* Column styles: no cell inspection or editor DOM until the gear is opened. */
(function (root, factory) {
    const api = factory();
    if (typeof module === 'object' && module.exports) module.exports = api;
    if (root && root.document && root.jQuery) {
        root.ibDtColumnStyles = api;
        api.bind(root, root.document, root.jQuery);
    }
})(typeof window === 'undefined' ? null : window, function () {
    'use strict';

    const token = '(?:[a-z][a-z0-9-]*(?:\\.[a-zA-Z_][a-zA-Z0-9_-]*)*(?::nth-of-type\\([1-9][0-9]*\\))?|\\[data-style-target="[a-zA-Z0-9_-]+"\\])';
    const pathPattern = new RegExp('^(?:>\\s*)?' + token + '(?:(?:\\s+>\\s*|\\s+)' + token + ')*$');
    const enums = {
        'font-style': ['normal', 'italic'], 'font-weight': ['300', '400', '500', '600', '700', 'bold', 'normal'],
        'text-align': ['left', 'center', 'right', 'start', 'end'],
        'text-transform': ['none', 'uppercase', 'lowercase', 'capitalize'],
        'text-decoration': ['none', 'underline', 'line-through'],
        'text-overflow-mode': ['wrap', 'clip', 'ellipsis', 'tooltip']
    };
    const lengths = ['width', 'min-width', 'max-width', 'font-size', 'letter-spacing'];
    const clone = value => JSON.parse(JSON.stringify(value || {}));

    // Old signatures used " + " to join independent branches, not CSS siblings.
    function paths(signature) {
        if (typeof signature !== 'string' || signature.length > 1000) return [];
        const result = signature.split(' + ').map(part => part.trim().replace(/^td(?:\s+|$)/, '').trim());
        return result.every(part => !part || pathPattern.test(part)) ? result : [];
    }

    function normalize(selectors) {
        const result = Object.create(null);
        if (!selectors || typeof selectors !== 'object') return result;
        Object.entries(selectors).slice(0, 100).forEach(([selector, rules]) => {
            if (!paths(selector).length || !rules || typeof rules !== 'object') return;
            const out = {};
            Object.entries(rules).forEach(([key, raw]) => {
                if (key === '__tooltip') { if (raw === true) out[key] = true; return; }
                if (typeof raw !== 'string' && typeof raw !== 'number') return;
                let value = String(raw).trim();
                if (lengths.includes(key)) {
                    if (/^-?\d+(?:\.\d+)?$/.test(value)) value += 'px';
                    if (/^-?\d+(?:\.\d+)?(?:px|em|rem|%)$/.test(value) || (key.includes('width') && value === 'auto')) out[key] = value;
                } else if (enums[key] && enums[key].includes(value)) out[key] = value;
                else if (['color', 'background-color'].includes(key) && /^(?:#[\da-f]{3,8}|[a-z]+|(?:rgb|rgba|hsl|hsla)\([\d.,%\s-]+\))$/i.test(value)) out[key] = value;
                else if (key === 'font-family' && /^[a-zA-Z0-9\s,'"_-]+$/.test(value)) out[key] = value;
            });
            if (out.width) { out['min-width'] = out.width; out['max-width'] = out.width; }
            if (Object.keys(out).length) result[selector] = out;
        });
        return result;
    }

    function quote(value) {
        return '"' + String(value).replace(/[\\"<>\r\n\f]/g, char => '\\' + char.charCodeAt(0).toString(16) + ' ') + '"';
    }

    function identifier(value) {
        return Array.from(String(value)).map(char => '\\' + char.codePointAt(0).toString(16) + ' ').join('');
    }

    function declarations(rules) {
        const out = [];
        Object.entries(rules).forEach(([key, value]) => {
            if (key === '__tooltip') return;
            if (key === 'text-overflow-mode') {
                out.push('white-space:' + (value === 'wrap' ? 'normal' : 'nowrap') + '!important',
                    'overflow:' + (value === 'wrap' ? 'visible' : 'hidden') + '!important',
                    'text-overflow:' + (['ellipsis', 'tooltip'].includes(value) ? 'ellipsis' : 'clip') + '!important');
            } else out.push(key + ':' + value + '!important');
        });
        return out.join(';');
    }

    function css(tableId, column, selectors) {
        const base = '#' + identifier(tableId);
        const cell = 'td[class~=' + quote(column) + ']';
        return Object.entries(normalize(selectors)).map(([signature, rules]) => {
            const targets = paths(signature).map(path => base + ' ' + cell + (path ? ' ' + path : ''));
            const width = rules.width;
            let output = targets.join(',') + '{' + declarations(rules) + '}';
            if (signature === 'td' && width) {
                const header = 'th[class~=' + quote(column) + ']';
                output += base + ' ' + header + ',[id=' + quote(tableId + '_wrapper') + '] ' + header
                    + ',table[data-realid=' + quote(tableId) + '] ' + header
                    + '{' + declarations({width, 'min-width': width, 'max-width': width}) + '}';
            }
            return output;
        }).join('\n');
    }

    function matches(cell, signature) {
        const result = new Set();
        paths(signature).forEach(path => {
            if (!path) result.add(cell);
            else {
                try { cell.querySelectorAll(path.startsWith('>') ? ':scope ' + path : path).forEach(node => result.add(node)); }
                catch (_) { /* Unsupported legacy selector: leave it visible as an unavailable target. */ }
            }
        });
        return Array.from(result);
    }

    function kind(node) {
        const tag = node.tagName.toLowerCase();
        if (tag === 'svg' || tag === 'i' || /(?:^|\s)(?:fa[srlbd]?|fa-solid|uk-icon)(?:\s|$)/.test(node.className || '')) return 'icon';
        if (tag === 'img') return 'image';
        if (tag === 'a') return 'link';
        if (tag === 'button') return 'button';
        if (['input', 'select', 'textarea'].includes(tag)) return 'input';
        if (/(?:badge|label|status)/i.test(node.className || '')) return 'badge';
        return 'text';
    }

    function structuralPath(node, cell) {
        const parts = [];
        while (node && node !== cell) {
            const role = node.getAttribute('data-style-target');
            if (role && /^[a-zA-Z0-9_-]+$/.test(role)) {
                parts.unshift('[data-style-target="' + role + '"]');
                return 'td ' + parts.join(' > ');
            }
            let tag = node.tagName.toLowerCase();
            const parent = node.parentElement;
            // Repeated list items share one target across the entire collection.
            if (parent && tag !== 'li' && !node.hasAttribute('data-cellindex')) {
                const siblings = Array.from(parent.children).filter(child => child.tagName === node.tagName);
                if (siblings.length > 1) tag += ':nth-of-type(' + (siblings.indexOf(node) + 1) + ')';
            }
            parts.unshift(tag);
            node = parent;
        }
        return 'td > ' + parts.join(' > ');
    }

    function createDiscovery(cells, saved, labels) {
        cells = Array.from(cells);
        const targets = new Map([['td', {selector: 'td', label: labels.cell, kind: 'cell', sample: '', node: cells[0] || null}]]);
        const occurrences = new Map();
        const savedSelectors = normalize(saved);

        function* inspect() {
            for (const cell of cells) {
                const walker = cell.ownerDocument.createTreeWalker(cell, 1);
                let node;
                // Yield between descendants as well as cells: one large iterator must not monopolize a frame.
                while ((node = walker.nextNode())) {
                    const type = kind(node);
                    const explicit = node.getAttribute('data-style-label');
                    const role = node.getAttribute('data-style-target');
                    const excluded = (node.closest('svg') !== null && node.tagName.toLowerCase() !== 'svg')
                        || (node.closest('[hidden], [aria-hidden="true"]') && type !== 'icon')
                        || node.matches('script, style, template, input[type="hidden"]');
                    const directText = !excluded && Array.from(node.childNodes).some(child => child.nodeType === 3 && child.textContent.trim());
                    if (!excluded && (explicit || role || type !== 'text' || directText)) {
                        const selector = structuralPath(node, cell);
                        if (paths(selector).length) {
                            occurrences.set(selector, (occurrences.get(selector) || 0) + 1);
                            if (!targets.has(selector)) {
                                const label = explicit || node.getAttribute('aria-label') || node.getAttribute('title') || labels[type];
                                const sample = (node.getAttribute('alt') || node.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 55);
                                targets.set(selector, {selector, label: String(label || labels.text).slice(0, 80), kind: type, sample, node});
                            }
                        }
                    }
                    yield;
                }
                yield;
            }
            // Reconcile legacy selectors incrementally too, reusing discovered groups instead of rescanning each target.
            for (const selector of Object.keys(savedSelectors)) {
                if (targets.has(selector)) continue;
                let first = null, count = 0, equivalent;
                for (const cell of cells) {
                    for (const node of matches(cell, selector)) {
                        first = first || node;
                        const key = structuralPath(node, cell);
                        if (count === 0) equivalent = key;
                        else if (equivalent !== key) equivalent = null;
                        count++;
                        yield;
                    }
                    yield;
                }
                const target = targets.get(equivalent);
                if (target && !Object.prototype.hasOwnProperty.call(savedSelectors, equivalent) && occurrences.get(equivalent) === count) {
                    targets.delete(equivalent);
                    targets.set(selector, Object.assign({}, target, {selector}));
                } else {
                    targets.set(selector, {selector, label: first ? (first.getAttribute('data-style-label') || labels[kind(first)]) : labels.savedElement,
                        kind: first ? kind(first) : 'text', sample: first ? (first.textContent || '').trim().slice(0, 55) : '', node: first});
                }
                yield;
            }
        }

        function snapshot() {
            const counts = new Map(), indexes = new Map();
            targets.forEach(target => counts.set(target.label, (counts.get(target.label) || 0) + 1));
            return Array.from(targets.values(), target => {
                const index = (indexes.get(target.label) || 0) + 1;
                indexes.set(target.label, index);
                return Object.assign({}, target, {label: target.label + (counts.get(target.label) > 1 ? ' ' + index : '')});
            });
        }

        return {iterator: inspect(), snapshot};
    }

    function discover(cells, saved, labels) {
        const scan = createDiscovery(cells, saved, labels);
        while (!scan.iterator.next().done) { /* Synchronous entry point for small, non-UI callers. */ }
        return scan.snapshot();
    }

    function discoverInChunks(cells, saved, labels, update, scheduler) {
        const scan = createDiscovery(cells, saved, labels);
        let cancelled = false, pending = null;
        function next() {
            if (cancelled) return;
            pending = null;
            const start = scheduler.now();
            let done = false;
            // These are per-frame budgets, never limits on how much of the column is inspected.
            for (let steps = 0; steps < 100 && (steps === 0 || scheduler.now() - start < 6); steps++) {
                if (scan.iterator.next().done) { done = true; break; }
            }
            if (cancelled) return;
            update(scan.snapshot(), done);
            if (!cancelled && !done) pending = scheduler.schedule(next);
        }
        next();
        return function cancel() {
            cancelled = true;
            if (pending !== null) scheduler.cancel(pending);
            scan.iterator.return();
        };
    }

    function bind(window, document, $) {
        if (window.__ibColumnStyleInspectorBound) return;
        window.__ibColumnStyleInspectorBound = true;
        let active = null;
        const tooltipOriginals = new Map();

        function restoreTooltips() {
            tooltipOriginals.forEach((title, node) => {
                if (title === null) node.removeAttribute('title'); else node.setAttribute('title', title);
            });
            tooltipOriginals.clear();
        }


        function styleNode(tableId, column) {
            let node = Array.from(document.querySelectorAll('style[data-ib-column-style]')).find(node =>
                node.dataset.tableId === tableId && node.dataset.columnName === column);
            if (!node) {
                node = document.createElement('style');
                node.dataset.ibColumnStyle = '';
                node.dataset.tableId = tableId;
                node.dataset.columnName = column;
                document.head.appendChild(node);
            }
            return node;
        }

        function close() {
            if (!active) return;
            const editor = active;
            restoreTooltips();
            active = null;
            window.cancelAnimationFrame(editor.frame);
            window.cancelAnimationFrame(editor.discoveryFrame);
            if (editor.cancelDiscovery) editor.cancelDiscovery();
            if (editor.observer) editor.observer.disconnect();
            editor.style.textContent = css(editor.table.id, editor.column, editor.saved);
            editor.highlight.remove();
            editor.panel.remove();
            editor.list.hidden = false;
            editor.bar.classList.remove('ib-column-style-open');
            $(editor.table).off('.ibColumnStyleInspector');
            editor.gear.setAttribute('aria-expanded', 'false');
            editor.dt.columns.adjust();
            editor.gear.focus();
        }

        function open(gear) {
            const list = gear.closest('.toggle-vis-container');
            const table = list && document.getElementById(list.dataset.tableid);
            const toggle = gear.closest('.ib-colvis-item').querySelector('.toggle-vis');
            if (!table || !toggle || !$.fn.dataTable.isDataTable(table)) return;
            if (active && active.saving) return;
            close();
            const dt = $(table).DataTable();
            const column = toggle.dataset.styleColumn;
            if (!column) return;
            const savedByTable = window.__ibColumnStyleDraftByTable || {};
            const saved = normalize(((savedByTable[table.id] || {})[column] || {}).selectors);
            const labels = JSON.parse(list.dataset.styleLabels || '{}');
            const fonts = JSON.parse(list.dataset.styleFonts || '{}');
            const bar = list.closest('.uk-offcanvas-bar');
            const panel = document.createElement('section');
            panel.className = 'ib-column-style-editor';
            panel.setAttribute('aria-label', labels.title);
            // Static scaffold only; all column names, samples and labels use textContent.
            panel.innerHTML = '<header><button type="button" class="ib-style-back"></button><h3></h3><p class="ib-style-scope"></p></header>'
                + '<div class="ib-style-body"><label class="ib-style-target-label"><span></span><select class="uk-select ib-style-target"></select></label>'
                + '<p class="ib-style-sample"></p><p class="ib-style-empty ib-style-discovery" role="status" hidden></p>'
                + '<p class="ib-style-empty ib-style-unavailable" role="status"></p><div class="ib-style-controls"></div>'
                + '<button type="button" class="ib-style-reset"></button></div>'
                + '<footer><p role="status" aria-live="polite"></p><div><button type="button" class="uk-button uk-button-default ib-style-cancel"></button>'
                + '<button type="button" class="uk-button uk-button-primary ib-style-save"></button></div></footer>';
            const highlight = document.createElement('style');
            document.head.appendChild(highlight);
            const editor = active = {table, column, dt, list, panel, bar, gear, labels, fonts, saved: clone(saved), draft: clone(saved),
                index: dt.column(function (_index, _data, header) { return header && header.classList.contains(column); }).index(), style: styleNode(table.id, column), highlight, target: 'td', frame: 0, saving: false};
            const find = selector => panel.querySelector(selector);
            find('.ib-style-back').textContent = '‹ ' + labels.columns;
            find('h3').textContent = toggle.textContent.trim();
            find('.ib-style-scope').textContent = labels.allRows;
            find('.ib-style-target-label span').textContent = labels.target;
            find('.ib-style-reset').textContent = labels.resetElement;
            find('.ib-style-cancel').textContent = labels.cancel;
            find('.ib-style-save').textContent = labels.save;
            bar.appendChild(panel);
            bar.classList.add('ib-column-style-open');
            list.hidden = true;
            gear.setAttribute('aria-expanded', 'true');

            function cells() {
                editor.index = dt.column(function (_index, _data, header) { return header && header.classList.contains(column); }).index();
                return Array.from(dt.column(editor.index, {page: 'current'}).nodes());
            }
            function status(message) { find('footer p').textContent = message; }
            function dirty() { return JSON.stringify(normalize(editor.draft)) !== JSON.stringify(normalize(editor.saved)); }
            function paint() {
                restoreTooltips();
                editor.style.textContent = css(table.id, column, editor.draft);
                if (editor.frame) window.cancelAnimationFrame(editor.frame);
                editor.frame = window.requestAnimationFrame(() => { editor.frame = 0; if (active === editor) dt.columns.adjust(); });
            }
            function setRule(property, value) {
                const rules = editor.draft[editor.target] || {};
                if (value === '') delete rules[property]; else rules[property] = value;
                if (property === 'width') { delete rules['min-width']; delete rules['max-width']; }
                if (property === 'text-overflow-mode') delete rules.__tooltip;
                if (Object.keys(rules).length) editor.draft[editor.target] = rules; else delete editor.draft[editor.target];
                paint(); status(dirty() ? labels.unsaved : labels.noChanges);
            }
            function controls() {
                const target = editor.targets.find(item => item.selector === editor.target);
                const container = find('.ib-style-controls');
                container.replaceChildren();
                const rules = editor.draft[editor.target] || {};
                const sample = target.node && table.contains(target.node) ? target.node : null;
                find('.ib-style-sample').textContent = target.sample || '';
                find('.ib-style-unavailable').textContent = sample || editor.discovering ? '' : labels.notOnPage;
                const outline = '#' + identifier(table.id) + ' td[class~=' + quote(column) + ']';
                highlight.textContent = paths(editor.target).map(path => outline + (path ? ' ' + path : '')).join(',')
                    + '{outline:2px solid #1e87f0;outline-offset:-2px}';
                const definitions = [];
                if (target.kind === 'cell') definitions.push(['width', 'number', labels.width, 40, 1600, 10]);
                definitions.push(['color', 'color', labels.color], ['background-color', 'color', labels.background]);
                if (!['image', 'icon'].includes(target.kind)) {
                    definitions.push(['font-size', 'number', labels.fontSize, 8, 72, 1], ['font-weight', 'select', labels.weight, ['300','400','500','600','700']],
                        ['font-style', 'select', labels.fontStyle, ['normal','italic']], ['text-align', 'select', labels.align, ['left','center','right']],
                        ['text-overflow-mode', 'select', labels.overflow, ['wrap','clip','ellipsis','tooltip']]);
                }
                if (target.kind === 'icon' && sample && sample.tagName.toLowerCase() !== 'svg') definitions.push(['font-size', 'number', labels.iconSize, 8, 72, 1]);
                definitions.forEach(definition => addControl(container, definition, rules, sample));
                if (!['image', 'icon'].includes(target.kind)) {
                    const advanced = document.createElement('details');
                    const summary = document.createElement('summary'); summary.textContent = labels.more;
                    advanced.appendChild(summary); container.appendChild(advanced);
                    [['font-family','select',labels.fontFamily,Object.keys(fonts)], ['letter-spacing','number',labels.spacing,-1,6,0.5],
                        ['text-transform','select',labels.transform,['none','uppercase','lowercase','capitalize']],
                        ['text-decoration','select',labels.decoration,['none','underline','line-through']]]
                        .forEach(definition => addControl(advanced, definition, rules, sample));
                }
            }
            function addControl(container, definition, rules, sample) {
                const [property, type, title, min, max, step] = definition;
                const row = document.createElement('div'); row.className = 'ib-style-control';
                const label = document.createElement('label');
                const id = 'ib-style-' + property + '-' + table.id;
                label.htmlFor = id; label.textContent = title;
                const field = document.createElement(type === 'select' ? 'select' : 'input');
                field.id = id; field.dataset.property = property;
                field.className = type === 'select' ? 'uk-select' : 'uk-input';
                if (type === 'select') {
                    field.add(new Option(labels.original, ''));
                    min.forEach(option => field.add(new Option(labels[option] || option, property === 'font-family' ? fonts[option] : option)));
                    const selected = property === 'text-overflow-mode' && rules.__tooltip ? 'tooltip' : rules[property];
                    if (selected && !Array.from(field.options).some(option => option.value === selected)) field.add(new Option(selected, selected));
                    field.value = selected || '';
                } else {
                    field.type = type;
                    if (type === 'number') {
                        field.min = min; field.max = max; field.step = step;
                        const explicit = rules[property];
                        // Keep non-pixel legacy units editable without silently turning them into pixels.
                        if (explicit && !/^-?\d+(?:\.\d+)?(?:px)?$/.test(String(explicit))) {
                            field.type = 'text'; field.value = explicit;
                        } else field.value = explicit ? parseFloat(explicit) : '';
                        const computed = sample && window.getComputedStyle(sample).getPropertyValue(property);
                        field.placeholder = computed ? labels.original + ' (' + computed + ')' : labels.original;
                    } else {
                        const value = rules[property] || (sample && window.getComputedStyle(sample).getPropertyValue(property)) || '#000000';
                        const rgb = value.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/);
                        field.value = rgb ? '#' + rgb.slice(1).map(n => Number(n).toString(16).padStart(2,'0')).join('') : /^#[\da-f]{6}$/i.test(value) ? value : '#000000';
                    }
                }
                const reset = document.createElement('button'); reset.type = 'button'; reset.className = 'ib-style-property-reset';
                reset.textContent = rules[property] ? labels.custom : labels.original;
                reset.setAttribute('aria-label', labels.resetProperty + ': ' + title);
                reset.disabled = !rules[property];
                reset.addEventListener('click', () => { setRule(property, ''); controls(); });
                field.addEventListener(type === 'select' ? 'change' : 'input', () => {
                    if (field.type === 'number' && !field.validity.valid) return;
                    setRule(property, field.value);
                    reset.textContent = field.value ? labels.custom : labels.original;
                    reset.disabled = !field.value;
                });
                row.append(label, field, reset); container.appendChild(row);
            }
            function refresh() {
                if (editor.cancelDiscovery) editor.cancelDiscovery();
                const revision = editor.discoveryRevision = (editor.discoveryRevision || 0) + 1;
                editor.discovering = true;
                const progress = find('.ib-style-discovery');
                progress.textContent = labels.discovering;
                progress.hidden = false;
                const select = find('.ib-style-target');
                select.setAttribute('aria-busy', 'true');
                editor.cancelDiscovery = discoverInChunks(cells(), editor.draft, labels, (targets, done) => {
                    if (active !== editor || revision !== editor.discoveryRevision) return;
                    const current = editor.target;
                    const previous = (editor.targets || []).find(target => target.selector === current);
                    // Keep a user's current selection while its row is still waiting to be inspected.
                    if (!targets.some(target => target.selector === current) && previous
                        && (!done || Object.prototype.hasOwnProperty.call(editor.draft, current))) targets.push(previous);
                    editor.targets = targets;
                    editor.discovering = !done;
                    const options = new Map(Array.from(select.options, option => [option.value, option]));
                    const available = new Set();
                    targets.forEach(target => {
                        available.add(target.selector);
                        const option = options.get(target.selector);
                        if (!option) select.add(new Option(target.label, target.selector));
                        else if (option.text !== target.label) option.text = target.label;
                    });
                    options.forEach((option, key) => { if (!available.has(key)) option.remove(); });
                    editor.target = available.has(current) ? current : 'td';
                    select.value = editor.target;
                    const selected = targets.find(target => target.selector === editor.target);
                    if (!editor.saving && (!previous || current !== editor.target || previous.node !== selected.node || done)
                        && (current !== editor.target || !find('.ib-style-controls').contains(document.activeElement))) controls();
                    progress.hidden = done;
                    select.setAttribute('aria-busy', String(!done));
                }, {
                    now: () => window.performance.now(),
                    schedule: callback => window.requestAnimationFrame(callback),
                    cancel: handle => window.cancelAnimationFrame(handle)
                });
            }
            find('.ib-style-target').addEventListener('change', event => { editor.target = event.target.value; controls(); });
            find('.ib-style-back').addEventListener('click', () => { if (!editor.saving) close(); });
            find('.ib-style-cancel').addEventListener('click', () => { if (!editor.saving) close(); });
            find('.ib-style-reset').addEventListener('click', () => { delete editor.draft[editor.target]; paint(); controls(); status(dirty() ? labels.unsaved : labels.noChanges); });
            find('.ib-style-save').addEventListener('click', () => {
                if (editor.saving) return;
                const snapshot = normalize(editor.draft);
                const payload = {columnIndex: editor.index, columnName: column, selectors: snapshot};
                editor.saving = true;
                panel.querySelectorAll('button,input,select').forEach(node => { node.disabled = true; });
                status(labels.saving);
                const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
                $.ajax({url:list.dataset.settingsStoreUrl, type:'POST', dataType:'json', headers:token ? {'X-CSRF-TOKEN':token} : {},
                    data:{columnIndex:payload.columnIndex, columnName:column, settings_json:JSON.stringify(payload)}})
                    .done(response => {
                        if (!response || !response.success) { status(labels.failed); return; }
                        editor.saved = clone(response.selectors || snapshot);
                        editor.draft = clone(editor.saved);
                        window.__ibColumnStyleDraftByTable = window.__ibColumnStyleDraftByTable || {};
                        window.__ibColumnStyleDraftByTable[table.id] = window.__ibColumnStyleDraftByTable[table.id] || {};
                        window.__ibColumnStyleDraftByTable[table.id][column] = {index:editor.index, selectors:clone(editor.saved)};
                        editor.style.textContent = css(table.id, column, editor.saved);
                        status(labels.saved);
                    }).fail(() => status(labels.failed)).always(() => {
                        editor.saving = false;
                        if (active !== editor) return;
                        panel.querySelectorAll('button,input,select').forEach(node => { node.disabled = false; });
                        controls();
                    });
            });
            $(table).on('draw.dt.ibColumnStyleInspector column-reorder.dt.ibColumnStyleInspector', refresh)
                .on('destroy.dt.ibColumnStyleInspector', close);
            if (window.MutationObserver && table.tBodies[0]) {
                editor.observer = new window.MutationObserver(records => {
                    const relevant = records.some(record => {
                        const cell = record.target.closest && record.target.closest('td');
                        return !cell || cell.classList.contains(column);
                    });
                    if (!relevant || editor.discoveryFrame) return;
                    editor.discoveryFrame = window.requestAnimationFrame(() => {
                        editor.discoveryFrame = 0;
                        if (active === editor) refresh();
                    });
                });
                editor.observer.observe(table.tBodies[0], {childList:true, subtree:true});
            }
            refresh(); status(labels.noChanges); find('.ib-style-target').focus();
        }

        // Tooltip contents are read only when a cell is hovered/focused, including AJAX rows.
        $(document).on('mouseover.ibColumnStyleTooltip focusin.ibColumnStyleTooltip', 'table.dataTable td', function () {
            restoreTooltips();
            const table = this.closest('table');
            const settings = Object.assign({}, (window.__ibColumnStyleDraftByTable || {})[table.id] || {});
            if (active && active.table === table) settings[active.column] = {selectors: active.draft};
            Object.entries(settings).forEach(([column, data]) => {
                if (!this.classList.contains(column)) return;
                Object.entries(data.selectors || {}).forEach(([selector, rules]) => {
                    if (!rules.__tooltip && rules['text-overflow-mode'] !== 'tooltip') return;
                    matches(this, selector).forEach(node => {
                        const text = (node.value || node.textContent || '').trim();
                        if (!text) return;
                        if (!tooltipOriginals.has(node)) tooltipOriginals.set(node, node.getAttribute('title'));
                        node.setAttribute('title', text);
                    });
                });
            });
        }).on('mouseleave.ibColumnStyleTooltip focusout.ibColumnStyleTooltip', 'table.dataTable td', restoreTooltips);

        $(document).on('click.ibColumnStyleInspector' , '.ib-colvis-gear', function (event) {
            event.preventDefault(); event.stopPropagation(); open(this);
        }).on('beforehide.ibColumnStyleInspector', '[uk-offcanvas]', function (event) {
            if (active && this.contains(active.panel)) {
                if (active.saving) event.preventDefault(); else close();
            }
        });
    }

    return {paths, normalize, css, matches, discover, discoverInChunks, bind};
});
