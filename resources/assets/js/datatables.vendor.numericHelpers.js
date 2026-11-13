(function (window) {
    'use strict';

    const numbers = window.IlBronza.datatables.editors.helpers.numbers;

    function canonical(value) {
        if (value === null || value === undefined || value === '') return null;
        if (typeof value === 'number' && (!Number.isFinite(value) || (Number.isInteger(value) && !Number.isSafeInteger(value))))
            throw new Error('Il server deve fornire questo numero come stringa decimale.');
        if (typeof value !== 'number' && typeof value !== 'string') throw new Error('Inserisci un numero valido.');
        const source = String(value).trim();
        const match = /^([+-]?)(\d*)(?:\.(\d*))?(?:[eE]([+-]?\d+))?$/.exec(source);
        if (!match || (!match[2] && !match[3])) throw new Error('Inserisci un numero valido.');
        const exponent = match[4] ? Number(match[4]) : 0;
        if (!Number.isInteger(exponent) || Math.abs(exponent) > 10000) throw new Error('Esponente numerico troppo grande.');
        const digits = match[2] + (match[3] || '');
        const position = match[2].length + exponent;
        const expanded = position <= 0 ? '0.' + '0'.repeat(-position) + digits
            : position >= digits.length ? digits + '0'.repeat(position - digits.length)
                : digits.slice(0, position) + '.' + digits.slice(position);
        const [rawInteger, fraction = ''] = expanded.split('.');
        const integer = rawInteger.replace(/^0+/, '') || '0';
        const sign = match[1] === '-' && /[1-9]/.test(integer + fraction) ? '-' : '';
        return sign + integer + (fraction ? '.' + fraction : '');
    }

    function parts(value) {
        const raw = canonical(value);
        if (raw === null) return null;
        const [integer, fraction = ''] = raw.replace(/^-/, '').split('.');
        return { raw, negative: raw[0] === '-', integer, fraction };
    }

    function compare(left, right) {
        const a = parts(left), b = parts(right);
        if (!a || !b) return !a && !b ? 0 : !a ? 1 : -1; // null last
        if (a.negative !== b.negative) return a.negative ? -1 : 1;
        const length = Math.max(a.fraction.length, b.fraction.length);
        const af = a.fraction.padEnd(length, '0'), bf = b.fraction.padEnd(length, '0');
        const result = a.integer.length !== b.integer.length ? (a.integer.length < b.integer.length ? -1 : 1)
            : a.integer !== b.integer ? (a.integer < b.integer ? -1 : 1) : af === bf ? 0 : af < bf ? -1 : 1;
        return a.negative ? -result : result;
    }

    function equal(left, right) {
        try { return compare(left, right) === 0; }
        catch (_) { return String(left ?? '') === String(right ?? ''); }
    }

    // Editing uses ungrouped numbers. A single dot/comma is decimal; grouped
    // pastes must follow the declared separators and complete groups of three.
    function parse(value, options = {}, editing = true) {
        if (value === null || value === undefined || value === '') return null;
        if (typeof value !== 'string') return canonical(value);
        let source = value.trim();
        if (!source) return null;
        const decimal = options.decimalSeparator ?? ',';
        const grouping = options.thousandsSeparator ?? '.';
        let separator = decimal;
        const alternative = decimal === ',' ? '.' : ',';
        if (editing && !source.includes(decimal) && source.split(alternative).length === 2)
            separator = alternative;
        const sign = /^[+-]/.test(source) ? source[0] : '';
        if (sign) source = source.slice(1);
        const pieces = source.split(separator);
        if (pieces.length > 2) throw new Error('Separatore decimale ripetuto.');
        let integer = pieces[0], fraction = pieces[1];
        if (grouping && grouping !== separator && integer.includes(grouping)) {
            const groups = integer.split(grouping);
            if (!/^\d{1,3}$/.test(groups[0]) || groups.slice(1).some(group => !/^\d{3}$/.test(group)))
                throw new Error('Raggruppamento delle migliaia non valido.');
            integer = groups.join('');
        }
        if (!/^\d*$/.test(integer) || (fraction !== undefined && !/^\d*$/.test(fraction)) || (!integer && !fraction))
            throw new Error('Inserisci un numero valido con i separatori configurati.');
        return canonical(sign + (integer || '0') + (fraction ? '.' + fraction : ''));
    }

    function scaled(value, scale) {
        const p = parts(value);
        return BigInt((p.negative ? '-' : '') + p.integer + p.fraction.padEnd(scale, '0'));
    }

    function validate(value, options = {}) {
        const raw = canonical(value);
        if (raw === null) {
            if (options.nullable === false) throw new Error('Il campo è obbligatorio.');
            return null;
        }
        const fraction = parts(raw).fraction.replace(/0+$/, '');
        if (options.maxDecimals != null && fraction.length > options.maxDecimals)
            throw new Error('Sono ammessi al massimo ' + options.maxDecimals + ' decimali.');
        if (options.min != null && compare(raw, options.min) < 0) throw new Error('Il minimo ammesso è ' + options.min + '.');
        if (options.max != null && compare(raw, options.max) > 0) throw new Error('Il massimo ammesso è ' + options.max + '.');
        if (options.step != null) {
            const step = canonical(options.step);
            if (step === null || compare(step, '0') <= 0) throw new Error('Il passo deve essere maggiore di zero.');
            const base = options.min ?? '0';
            const scale = Math.max(...[raw, step, base].map(item => parts(item).fraction.length));
            if ((scaled(raw, scale) - scaled(base, scale)) % scaled(step, scale) !== BigInt(0))
                throw new Error('Usa incrementi di ' + step + ' a partire da ' + base + '.');
        }
        return raw;
    }

    function format(value, options = {}) {
        const p = parts(value);
        if (!p) return '';
        const decimals = options.decimals;
        let integer = p.integer, fraction = p.fraction;
        if (decimals != null) {
            let rounded = BigInt(integer + fraction.slice(0, decimals).padEnd(decimals, '0'));
            if (fraction.length > decimals && fraction[decimals] >= '5') rounded += BigInt(1);
            const digits = String(rounded).padStart(decimals + 1, '0');
            integer = decimals ? digits.slice(0, -decimals) : digits;
            fraction = decimals ? digits.slice(-decimals) : '';
        }
        if (options.fixedDecimals === false) fraction = fraction.replace(/0+$/, '');
        const sign = p.negative && /[1-9]/.test(integer + fraction) ? '-' : '';
        const grouping = options.thousandsSeparator ?? '.';
        if (grouping) integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, () => grouping);
        return sign + integer + (fraction ? (options.decimalSeparator ?? ',') + fraction : '');
    }

    function edit(value, options = {}) {
        return (canonical(value) ?? '').replace('.', options.decimalSeparator ?? ',');
    }

    Object.assign(numbers, { canonical, compare, equal, parse, validate, format, edit });
    Object.freeze(numbers);
})(window);
