/**
 * Inizializza select2 sui select .ib-editor-select2 emessi da DatatableFieldSelect (select2 = true).
 * Richiede select2 caricato sulla stessa istanza jQuery del pacchetto.
 *
 * Le opzioni vanno caricate prima dell'init: select2 nasconde il select nativo e il
 * popolamento lazy su click di datatables.vendor.ajaxButton.min.js non arriverebbe mai.
 */
(function ($) {
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

	function initEditorSelect2sInRoot(rootEl)
	{
		$(rootEl).find('select.ib-editor-select2').each(function ()
		{
			const $select = $(this);

			if ($select.data('select2'))
				return;

			window.ibDtPopulateSelectOptions($select, getPossibleValues(this));

			//la cella e' stretta: la tendina si allarga sulle etichette
			$select.select2({
				width: '100%',
				dropdownAutoWidth: true
			});
		});
	}

	function onTableDraw()
	{
		initEditorSelect2sInRoot(this);
	}

	window.ibDtInitEditorSelect2sInRoot = initEditorSelect2sInRoot;

	$(document).on('draw.dt', 'table.dataTable', onTableDraw);
})($);
