(function (window) {
    'use strict';

    const editors = window.IlBronza.datatables.editors;
    const numbers = editors.helpers.numbers;
    const states = new WeakMap();

    class NumericEditorContext extends editors.EditorContext {
        constructor($field) { super($field); this.numeric = true; }
        numberOptions() {
            const options = this.data('ib-dt-number-options');
            if (!options) throw new Error('Missing numeric editor options.');
            return typeof options === 'string' ? JSON.parse(options) : options;
        }
        read() {
            const state = states.get(this.element);
            const value = this.$field.val();
            if (state && value === state.display) return state.raw;
            if (!state) {
                const original = numbers.canonical(this.$field.attr('data-originalvalue') ?? '');
                if (value === numbers.format(original, this.numberOptions()) || value === (original ?? '')) return original;
            }
            return numbers.parse(value, this.numberOptions());
        }
        getValue() {
            try { return this.read(); }
            catch (error) { this.invalid(error.message); return this.$field.val(); }
        }
        invalid(message = '', report = false) {
            this.element.setCustomValidity(message);
            if (message && report) this.element.reportValidity();
            return !message;
        }
        validateValue(value, report = false) {
            try { numbers.validate(value, this.numberOptions()); return this.invalid(); }
            catch (error) { return this.invalid(error.message, report); }
        }
        validate(report = false) {
            try { return this.validateValue(this.read(), report); }
            catch (error) { return this.invalid(error.message, report); }
        }
        submit(value, options = {}) {
            if (!this.validateValue(value, true)) return false;
            return super.submit(numbers.canonical(value), options);
        }
        setValue(value, editing = window.document?.activeElement === this.element) {
            const raw = numbers.canonical(value);
            const display = editing ? numbers.edit(raw, this.numberOptions()) : numbers.format(raw, this.numberOptions());
            this.$field.val(display);
            states.set(this.element, { raw, display });
            this.invalid();
        }
        enter() {
            const message = this.element.validity.customError ? this.element.validationMessage : '';
            try {
                this.setValue(this.read(), true);
                if (message) this.invalid(message);
            }
            catch (_) {} // Preserve an incomplete or invalid draft.
        }
        leave() {
            if (this.validate()) this.setValue(this.read(), false);
        }
        draft() { states.set(this.element, { display: null }); this.invalid(); }
    }

    editors.NumericEditorContext = NumericEditorContext;
    editors.register('ib-dt-numeric-editor', NumericEditorContext);
    editors.initialize = function (root) {
        const $ = window.jQuery;
        $(root).find('.ib-dt-numeric-editor').add($(root).filter('.ib-dt-numeric-editor')).each(function () {
            if (!states.has(this) && window.document.activeElement !== this)
                editors.forInput(this).setValue(this.getAttribute('data-originalvalue'), false);
        });
    };

    if (window.document && window.jQuery) {
        // Capture input changes before the shared delegated dirty/save handlers.
        for (const name of ['focusin', 'focusout', 'input', 'change']) {
            window.document.addEventListener(name, event => {
                if (!event.target.matches?.('.ib-dt-numeric-editor.ib-dt-editor-v2')) return;
                const editor = editors.forInput(event.target);
                if (name === 'focusin') editor.enter();
                else if (name === 'focusout') editor.leave();
                else editor.draft();
            }, true);
        }
        const $ = window.jQuery;
        $(window.document).on('draw.dt', 'table.dataTable', function () { editors.initialize(this); });
        const order = $.fn.dataTable.ext.type.order;
        order['ib-decimal-asc'] = (a, b) => numbers.compare(a, b);
        order['ib-decimal-desc'] = (a, b) => numbers.canonical(a) === null || numbers.canonical(b) === null
            ? numbers.compare(a, b) : -numbers.compare(a, b);
    }
})(window);
