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
})(window);
