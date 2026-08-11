/**
 * Select2 per i campi editor.select (e derivati: selectOrFlat, selectOrInput)
 * con la proprieta' select2 attiva.
 *
 * Le opzioni vanno caricate prima dell'init: select2 nasconde il select nativo e il
 * popolamento lazy su click di datatables.vendor.ajaxButton.min.js non arriverebbe mai.
 * L'init resta volutamente lazy: farlo durante draw.dt mette Select2 nel ciclo di
 * rendering di DataTables e puo' interferire con il redraw della riga.
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

	function initEditorSelect2(select)
	{
		const $select = $(select);

		if ($select.data('select2'))
			return $select;

		window.ibDtPopulateSelectOptions($select, getPossibleValues(select));

		//la cella e' stretta: la tendina si allarga sulle etichette
		$select.select2({
			width: '100%',
			dropdownAutoWidth: true
		});

		return $select;
	}

	window.ibDtInitEditorSelect2 = initEditorSelect2;

	//Init al primo click, prima che il browser apra il select nativo.
	$(document).on('mousedown', 'table.datatable tbody select.ib-editor-select2', function (event)
	{
		if ($(this).data('select2'))
			return;

		event.preventDefault();
		initEditorSelect2(this).select2('open');
	});

	//Arrivo da tastiera: dopo l'init il focus deve passare al container Select2.
	$(document).on('focusin', 'table.datatable tbody select.ib-editor-select2', function ()
	{
		if ($(this).data('select2'))
			return;

		initEditorSelect2(this)
			.next('.select2-container')
			.find('.select2-selection')
			.trigger('focus');
	});
})($);
