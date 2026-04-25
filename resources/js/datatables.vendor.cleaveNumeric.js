/**
 * Inizializza Cleave.js sugli input .ib-cleave-numeric emessi da DatatableFieldNumeric (cleave = true).
 * Richiede window.Cleave caricato prima di questo script.
 */
(function ($) {
	function initCleaveNumericsInRoot(rootEl) {
		if (typeof window.Cleave === 'undefined') {
			return;
		}

		$(rootEl)
			.find('.ib-cleave-numeric')
			.each(function () {
				var el = this;
				var raw = el.getAttribute('data-cleave-options');
				if (!raw) {
					return;
				}

				var opts;
				try {
					opts = JSON.parse(raw);
				} catch (e) {
					return;
				}

				var $el = $(el);
				var prev = $el.data('cleaveInstance');
				if (prev && typeof prev.destroy === 'function') {
					try {
						prev.destroy();
					} catch (_e) {}
				}
				$el.removeData('cleaveInstance');

				var instance = new window.Cleave(el, opts);
				$el.data('cleaveInstance', instance);
			});
	}

	function onTableDraw() {
		initCleaveNumericsInRoot(this);
	}

	$(document).on('draw.dt', 'table.dataTable', onTableDraw);
})(window.jQuery);
