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
		if (typeof window.ibDtLoadSelectPossibleValuesRow === 'function')
		{
			const rowRouteRequest = window.ibDtLoadSelectPossibleValuesRow(select);

			if (rowRouteRequest)
				return rowRouteRequest;
		}

		const $select = $(select);
		const possibleValues = $select.data('possible-values');

		if (possibleValues)
			return possibleValues;

		const tableId = window.__getTableByCell(select).attr('id');
		const fieldName = window.__getTH(select).data('name');

		return window.ibDtGetSelectPossibleValues(tableId, fieldName);
	}

	function populateAndInitialize($select, possibleValues)
	{
		window.ibDtPopulateSelectOptions($select, possibleValues);

		//la cella e' stretta: la tendina si allarga sulle etichette
		$select.select2({
			width: '100%',
			dropdownAutoWidth: true
		});

		return $select;
	}

	function initEditorSelect2(select)
	{
		const $select = $(select);

		if ($select.data('select2'))
			return $select;

		const possibleValues = getPossibleValues(select);

		if (possibleValues && typeof possibleValues.then === 'function')
		{
			$select.data('select2Loading', true);

			return possibleValues.then(function (list)
			{
				$select.removeData('select2Loading');
				$select.data('possibleValuesRowRouteReady', true);

				return populateAndInitialize($select, list);
			}, function (error)
			{
				$select.removeData('select2Loading');
				throw error;
			});
		}

		return populateAndInitialize($select, possibleValues);
	}

	window.ibDtInitEditorSelect2 = initEditorSelect2;

	//Init al primo click, prima che il browser apra il select nativo.
	$(document).on('mousedown', 'table.datatable tbody select.ib-editor-select2', function (event)
	{
		if ($(this).data('select2'))
			return;

		if ($(this).data('select2Loading'))
			return;

		event.preventDefault();

		const initialized = initEditorSelect2(this);

		if (initialized && typeof initialized.then === 'function')
		{
			initialized.then(function ($select)
			{
				$select.select2('open');
			}, console.error);

			return;
		}

		initialized.select2('open');
	});

	//Arrivo da tastiera: dopo l'init il focus deve passare al container Select2.
	$(document).on('focusin', 'table.datatable tbody select.ib-editor-select2', function ()
	{
		if ($(this).data('select2'))
			return;

		if ($(this).data('select2Loading'))
			return;

		const focusSelection = function ($select)
		{
			$select
				.next('.select2-container')
				.find('.select2-selection')
				.trigger('focus');
		};

		const initialized = initEditorSelect2(this);

		if (initialized && typeof initialized.then === 'function')
		{
			initialized.then(focusSelection, console.error);

			return;
		}

		focusSelection(initialized);
	});

	// Ogni apertura successiva ricarica le opzioni della riga. Il flag ready
	// permette l'apertura che segue il caricamento senza innescare una seconda GET.
	$(document).on('select2:opening', 'table.datatable tbody select.ib-editor-select2[data-possible-values-row-route]', function (event)
	{
		const $select = $(this);

		if ($select.data('possibleValuesRowRouteReady'))
		{
			$select.removeData('possibleValuesRowRouteReady');

			return;
		}

		if ($select.data('select2Loading'))
		{
			event.preventDefault();

			return;
		}

		event.preventDefault();
		$select.data('select2Loading', true);

		window.ibDtLoadSelectPossibleValuesRow(this).then(function (possibleValues)
		{
			window.ibDtPopulateSelectOptions($select, possibleValues);
			$select.data('possibleValuesRowRouteReady', true);
			$select.removeData('select2Loading');
			$select.select2('open');
		}, function (error)
		{
			$select.removeData('select2Loading');
			console.error(error);
		});
	});
})($);
