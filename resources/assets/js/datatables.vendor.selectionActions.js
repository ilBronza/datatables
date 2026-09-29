/**
 * Azioni che richiedono almeno una riga selezionata (per esempio la
 * cancellazione multipla) restano fuori dalla toolbar finché non sono
 * utilizzabili. Il controller deve aggiungere la classe
 * `ib-dt-selection-action` al bottone interessato.
 */
(function registerIlBronzaDatatablesSelectionActions()
{
    if (! window.jQuery || ! $.fn.dataTable || ! $.fn.dataTable.Api)
    {
        setTimeout(registerIlBronzaDatatablesSelectionActions, 100);
        return;
    }

    function asDataTableApi(table)
    {
        if (table && typeof table.rows === 'function')
            return table;

        return new $.fn.dataTable.Api(table);
    }

    window.ibDtSyncSelectionActionButtons = function (table)
    {
        const dt = asDataTableApi(table);

        if (! dt || typeof dt.rows !== 'function' || typeof dt.table !== 'function')
            return;

        const container = dt.table().container();
        const $actions = $(container).find('.ib-dt-selection-action');

        if (! $actions.length)
            return;

        const hasSelection = dt.rows({ selected: true }).count() > 0;

        $actions
            .toggleClass('uk-hidden', ! hasSelection)
            .prop('disabled', ! hasSelection)
            .attr('aria-hidden', hasSelection ? 'false' : 'true');
    };

    $(document).on('init.dt.ibSelectionActions draw.dt.ibSelectionActions', function (e, settings)
    {
        window.ibDtSyncSelectionActionButtons(settings);
    });

    $(document).on('select.dt.ibSelectionActions deselect.dt.ibSelectionActions', function (e, dt)
    {
        window.ibDtSyncSelectionActionButtons(dt);
    });

})();
