/**
 * Select2 per i campi editor.select (e derivati: selectOrFlat, selectOrInput)
 * con la proprieta' select2 attiva.
 *
 * Select2 nasconde il select nativo, quindi il popolamento lazy su click di
 * datatables.vendor.ajaxButton.min.js non arriverebbe mai: qui le opzioni
 * vengono caricate prima dell'init, alla prima interazione con la cella.
 */
$(document).ready(function()
{
    function getPossibleValues(select)
    {
        const $select = $(select);
        const possibleValues = $select.data('possible-values');

        if (possibleValues)
            return possibleValues;

        const tableId = window.__getTableByCell(select).attr('id');
        const fieldName = window.__getTH(select).data('name');

        return window.ibDtGetSelectPossibleValues(tableId, fieldName);
    }

    window.ibDtInitEditorSelect2 = function(select)
    {
        const $select = $(select);

        window.ibDtPopulateSelectOptions($select, getPossibleValues(select));

        // la cella e' stretta: la tendina si allarga sulle etichette
        $select.select2({
            width: '100%',
            dropdownAutoWidth: true
        });

        return $select;
    };

    // init al mousedown, prima che si apra la tendina nativa del browser
    $(document).on('mousedown', 'table.datatable tbody select.ib-editor-select2', function(event)
    {
        if ($(this).data('select2'))
            return;

        event.preventDefault();

        window.ibDtInitEditorSelect2(this).select2('open');
    });

    // arrivo da tastiera: il focus va spostato sul container, il select sparisce
    $(document).on('focusin', 'table.datatable tbody select.ib-editor-select2', function()
    {
        if ($(this).data('select2'))
            return;

        window.ibDtInitEditorSelect2(this)
            .next('.select2-container')
            .find('.select2-selection')
            .trigger('focus');
    });
});
