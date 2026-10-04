jQuery(document).ready(function($)
{
    window.ibDtInitFieldOperations = function(container, item, operations)
    {
        if (! item || item[0] == null)
            return;

        const placeholder = window.replace_model_id_string;
        if (placeholder)
            container.find('a[href]').each(function()
            {
                $(this).attr('href', $(this).attr('href').split(placeholder).join(item[0]));
            });

        Object.entries(operations).forEach(function(entry)
        {
            const name = entry[0];
            const operation = entry[1];
            const control = container.children().filter(function() { return $(this).hasClass(name); });

            control.addClass('ib-field-operation-editor').data('editorOperation', operation);

            if (operation.type === 'editor.toggle')
            {
                const value = item[2] && item[2][name];
                const active = value === true || value === 1 || value === '1' || value === 'true';
                control.data('value', active).removeAttr('uk-icon').empty()
                    .append($('<i/>', {class: 'fa-solid fa-' + (active ? (operation.trueIcon || 'check') : (operation.falseIcon || 'close'))}));
            }

            if (operation.fetcher && operation.fetcher.url)
                control.addClass('clickfetchermodal').data('fetchurl',
                    operation.fetcher.url.split(placeholder).join(item[0]));
        });
    };

    $('body').on('click', '.ib-field-operation-editor', function(event)
    {
        event.preventDefault();
        event.stopPropagation();

        const control = $(this);
        // The owning cell survives when the hover toolbar disappears during the request.
        const owner = control.closest('[data-cellindex], td');

        if (owner.data('editorOperationPending'))
            return;

        const operation = control.data('editorOperation');
        const data = Object.assign({}, operation.data, {
            'ib-editor': true,
            field: operation.editorProperty,
            _method: 'PUT'
        });

        if (operation.type === 'editor.toggle')
            data.value = ! control.data('value');

        owner.data('editorOperationPending', true).data('url', control.attr('href')).addClass('spin');

        const release = function() { owner.removeData('editorOperationPending'); };

        try
        {
            if (window.ibCallAjax({
                target: owner[0],
                e: event,
                type: 'POST',
                data: data,
                refreshRowOnSuccess: true,
                forceRefreshRowOnSuccess: true,
                deferRowRefresh: true,
                onComplete: release
            }) === false)
                release();
        }
        catch (error)
        {
            release();
            throw error;
        }
    });
});
