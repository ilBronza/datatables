(function ($) {
    'use strict';

    const floatingSelector = '.ib-elements-select-rows-floating';

    function notifyDanger(message) {
        if (typeof window.addDangerNotification === 'function') {
            window.addDangerNotification(message);
            return;
        }

        window.alert(message);
    }

    function getSelectedIds(node, dt) {
        if (typeof window.ibDtCollectSelectedRowIds === 'function')
            return window.ibDtCollectSelectedRowIds(dt);

        if (! dt || typeof dt.rows !== 'function' || typeof window.__getIdColumnIndex !== 'function')
            return [];

        return dt.rows({ selected: true }).data()
            .pluck(window.__getIdColumnIndex(node, dt))
            .toArray();
    }

    function createContainer(button) {
        const offset = button.offset();

        return $('<div class="ib-elements-select-rows-floating"></div>').css({
            position: 'absolute',
            top: offset.top + button.outerHeight() + 4,
            left: offset.left,
            zIndex: 10090,
            minWidth: '320px',
            background: '#fff',
            padding: '8px',
            boxShadow: '0 5px 15px rgba(0,0,0,.15)'
        });
    }

    function refreshSubmittedRows(dt, rowIds) {
        const tableNode = dt && typeof dt.table === 'function' ? dt.table().node() : null;
        const tableSelector = tableNode && tableNode.id ? '#' + tableNode.id : null;

        if (tableSelector && typeof window.reloadTableRows === 'function') {
            window.reloadTableRows(tableSelector, rowIds);
            return;
        }

        if (dt && typeof dt.rows === 'function')
            dt.rows().deselect();

        if (dt && dt.ajax && typeof dt.ajax.reload === 'function')
            dt.ajax.reload(null, false);
    }

    window.ibDtMountElementsSelectRows = function (node, dt, configuration) {
        $(floatingSelector).remove();

        const selectedIds = getSelectedIds(node, dt);

        if (! selectedIds.length) {
            notifyDanger(configuration.noSelectionMessage);
            return;
        }

        const $container = createContainer($(node));
        const $select = $('<select style="width: 100%;"></select>');

        $select.append(new Option('', '', true, true));

        $.each(configuration.elements, function (id, name) {
            $select.append(new Option(name, id, false, false));
        });

        $container.append($select);
        $('body').append($container);

        const removeContainer = function () {
            if ($select.data('select2'))
                $select.select2('destroy');

            $container.remove();
        };

        $select.select2({
            dropdownParent: $container,
            placeholder: configuration.placeholder,
            width: '100%'
        });

        $select.on('select2:select', function (event) {
            const elementId = event.params.data.id;

            if (! elementId)
                return;

            const data = {};
            data[configuration.elementIdField] = elementId;
            data[configuration.selectedIdsField] = selectedIds;

            $.ajax({
                url: configuration.url,
                type: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).done(function (response) {
                if (response && response.success === true)
                    refreshSubmittedRows(dt, selectedIds);
            }).always(removeContainer);
        });

        $select.on('select2:close', function () {
            setTimeout(function () {
                if (! $select.val())
                    removeContainer();
            }, 150);
        });

        $select.select2('open');
    };
})(jQuery);
