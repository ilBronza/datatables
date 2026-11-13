(function (window) {
    'use strict';

    const namespace = window.IlBronza.datatables;
    const helpers = namespace.editors.helpers;
    const controllers = new WeakMap();

    function fieldFor(target) {
        const $field = target && typeof target.attr === 'function' ? target : window.jQuery(target);
        if (!$field.hasClass('ib-datatable-inline-edit-save')) return $field;
        let $row = $field.closest('tr');
        if (!$row.hasClass('ib-datatable-inline-edit-child')) $row = $row.next('tr.ib-datatable-inline-edit-child');
        return $row.find('.ib-datatable-inline-edit-field').first();
    }

    class LegacyEditorContext {
        constructor($field) {
            this.$field = $field;
            this.element = $field[0];
            this.managed = false;
        }
        data(name) {
            return name === 'field' ? this.$field.data('field') : this.$field.attr('data-' + name);
        }
        getTable() { return helpers.table(this.$field.closest('table')[0]); }
        resolveUrl() { return this.$field.data('url') || this.$field.data('route') || this.$field.attr('href') || null; }
        getValue() {
            return typeof window.__ibGetEditorSubmitValue === 'function'
                ? window.__ibGetEditorSubmitValue(this.element) : this.$field.val();
        }
        save(value = this.getValue(), options = {}) {
            return this.submit(value, options);
        }
        submit(value, options = {}) {
            return window.ibCallAjax({ ...options, target: this.element, type: options.type || 'POST',
                data: { 'ib-editor': true, _method: 'PUT', ...options.data, field: this.data('field'), value } });
        }
        validate() { return true; }
        validateValue() { return true; }
    }

    // Contexts live for one operation. Never retain inputs across DataTables draws.
    class EditorContext extends LegacyEditorContext {
        constructor($field) { super($field); this.managed = true; }
        getController() { return namespace.editors.forTable(this.getTable()); }
        columnIndex(table = this.getTable()) {
            if (!table) return undefined;
            if (this.$field.hasClass('ib-datatable-inline-edit-field'))
                return helpers.columnIndex(table.settings()[0], this.$field.attr('data-inline-source-field'));
            return table.cell(this.$field.closest('td, [data-dt-row][data-dt-column]')[0]).index()?.column;
        }
        data(name) {
            const inlineValue = this.$field.closest('[data-ib-dt-inline-control]').attr('data-' + name);
            if (inlineValue !== undefined) return inlineValue;
            const table = this.getTable();
            const index = this.columnIndex(table);
            if (index === undefined || index < 0) return undefined;
            for (const row of table.settings()[0].aoHeader || []) {
                const value = window.jQuery(row[index]?.cell).attr('data-' + name);
                if (value !== undefined) return value;
            }
            return undefined;
        }
        resolveUrl() {
            const inlineRoute = this.$field.closest('[data-ib-dt-editor-route]').attr('data-ib-dt-editor-route');
            if (inlineRoute !== undefined) {
                const route = JSON.parse(inlineRoute);
                return helpers.resolveUrl(route, route.targetId, route.updateUrlFromCell ? route.updateUrl : null);
            }
            const table = this.getTable();
            if (!table) throw new Error('Editor URL requires a DataTable or an inline route.');
            const cell = table.cell(this.$field.closest('td, [data-dt-row][data-dt-column]')[0]);
            const index = cell.index();
            const settings = table.settings()[0];
            const route = index && settings.aoColumns[index.column].ibDtEditor;
            if (!route) throw new Error('Editor URL requires column route metadata.');
            const targetId = route.target === 'element' ? this.$field.attr('data-ib-dt-target-id')
                : settings.rowIdFn(table.row(index.row).data());
            return helpers.resolveUrl(route, targetId, route.updateUrlFromCell ? cell.data()?.updateUrl : null);
        }
        save(value = this.getValue(), options = {}) {
            const controller = this.getController();
            return controller ? controller.save(this, value, options) : super.save(value, options);
        }
        describe() {
            if (this.$field.hasClass('ib-datatable-inline-create-field')) return null;
            const table = this.getTable();
            if (!table) return null;
            const settings = table.settings()[0];
            let $row = this.$field.closest('tr');
            if ($row.hasClass('ib-datatable-inline-edit-child') || $row.hasClass('child')) $row = $row.prev('tr');
            const row = table.row($row);
            if (!row.any()) return null;
            const rowId = settings.rowIdFn(row.data());
            const index = this.columnIndex(table);
            if (!helpers.hasId(rowId) || index === undefined || index < 0) return null;
            const column = helpers.columnName(settings, index);
            const field = this.data('field');
            if (!column || !field) return null;
            const route = settings.aoColumns[index].ibDtEditor;
            if (route?.history === false) return null;
            const format = this.data('ib-dt-cell-format');
            const raw = table.cell(row.index(), index).data();
            const targetId = route?.target === 'element' ? raw?.targetId : rowId;
            if (!helpers.hasId(targetId)) return null;
            return { table, element: this.element, rowId: String(rowId), column, field, targetId: String(targetId), format,
                valueType: this.data('ib-dt-value-type'),
                before: route ? helpers.valueOf(raw, format) : (this.$field.attr('data-originalvalue') ?? ''),
                hasColumnRoute: !!route, original: this.$field.attr('data-originalvalue'), url: this.resolveUrl() };
        }
    }

    class TableEditorController {
        constructor(table) { this.table = table; this._history = null; }
        get history() {
            if (!this._history && namespace.editors.History) this._history = new namespace.editors.History(this);
            return this._history;
        }
        editorFor(input) {
            const editor = namespace.editors.forInput(input);
            if (editor.getTable()?.settings()[0] !== this.table.settings()[0])
                throw new Error('Editor belongs to a different DataTable.');
            return editor;
        }
        save(editor, value, options = {}) { return editor.submit(value, options); }
        locate(change, rowData) {
            const table = this.table;
            const settings = table.settings()[0];
            const index = helpers.columnIndex(settings, change.column);
            const rowIndex = table.rows().indexes().toArray().find(index =>
                String(settings.rowIdFn(table.row(index).data())) === change.rowId);
            if (index < 0 || rowIndex === undefined) throw new Error('La riga o la colonna della modifica non è più disponibile.');
            const data = rowData ?? table.row(rowIndex).data();
            return { settings, index, rowIndex, data, raw: settings.aoColumns[index].fnGetData(data) };
        }
        async readRow(rowId) {
            const table = this.table;
            const url = table.ajax && typeof table.ajax.url === 'function' ? table.ajax.url() : null;
            if (!url) return null;
            const response = await window.jQuery.ajax({ url, type: 'GET', dataType: 'json', data: { ibeditor: true, rowId, refreshRow: true } });
            if (!response || !Array.isArray(response.data) || response.data.length !== 1)
                throw new Error('Impossibile verificare il valore attuale della riga.');
            const data = response.data[0];
            if (String(table.settings()[0].rowIdFn(data)) !== String(rowId))
                throw new Error('La risposta contiene una riga diversa da quella richiesta.');
            return data;
        }
    }

    class EditorRegistry {
        constructor() { this.helpers = helpers; this.contextTypes = []; }
        register(className, Context) { this.contextTypes.unshift({ className, Context }); }
        forTable(table) {
            table = helpers.table(table);
            if (!table) return null;
            const settings = table.settings()[0];
            if (!controllers.has(settings)) controllers.set(settings, new TableEditorController(table));
            return controllers.get(settings);
        }
        forInput(target) {
            const $field = fieldFor(target);
            if (!$field.hasClass('ib-dt-editor-v2')) return new LegacyEditorContext($field);
            const Type = this.contextTypes.find(type => $field.hasClass(type.className))?.Context || EditorContext;
            return new Type($field);
        }
        validateRequest(params) {
            const fields = params.inlineEditSubmittedFields || [{ element: params.target, value: params.data?.value }];
            return fields.every(item => {
                const editor = this.forInput(item.element);
                if (!editor.numeric) return true;
                const value = params.data?.fields ? params.data.fields[editor.data('field')] : item.value;
                return editor.validateValue(value, true);
            });
        }
        data(target, name) { return this.forInput(target).data(name); }
        resolveUrl(target) { return this.forInput(target).resolveUrl(); }
        saveTrigger(target) {
            const editor = this.forInput(target);
            return editor.managed ? editor.data('save-trigger') : null;
        }
    }

    namespace.editors = Object.assign(new EditorRegistry(), { EditorContext, LegacyEditorContext, TableEditorController });

    // Stable bridges for existing shared modules and previously generated markup.
    window.ibDtGetEditorData = (target, name) => namespace.editors.data(target, name);
    window.ibDtEscapeHtml = value => helpers.escapeHtml(value);
})(window);
