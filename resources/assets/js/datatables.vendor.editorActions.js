(function(window)
{
    function isTrue(value)
    {
        return value === true || value === 1 || value === '1' || value === 'true';
    }

    function getAjaxExtraData(fieldExtraData)
    {
        if (! fieldExtraData || typeof fieldExtraData !== 'object')
            return null;

        return fieldExtraData.ajaxextradata
            || fieldExtraData.ajaxExtraData
            || fieldExtraData;
    }

    window.ibDtEditorFieldRequestsTableReload = function(fieldExtraData)
    {
        const ajaxExtraData = getAjaxExtraData(fieldExtraData);

        return !! ajaxExtraData && isTrue(ajaxExtraData.reloadTable);
    };

    window.ibDtEditorResponseRequestsTableReload = function(response)
    {
        if (! response || typeof response !== 'object')
            return false;

        return response.action === 'reloadTable'
            || response.ibaction === 'reloadTable';
    };

    /**
     * An editor control has already applied its new value locally when its
     * request can fail validation. Reloading its table is currently the
     * authoritative way to discard that optimistic value.
     */
    window.ibDtEditorTargetRequiresErrorReload = function(target)
    {
        if (! target || typeof target.matches !== 'function' || typeof target.closest !== 'function')
            return false;

        if (! target.matches(
            '.ib-editor-text, .ib-editor-select, .ib-editor-color, '
            + '.ib-editor-custom-value, .ib-editor-file-upload'
        ))
            return false;

        return !! target.closest('table.datatable');
    };
})(window);
