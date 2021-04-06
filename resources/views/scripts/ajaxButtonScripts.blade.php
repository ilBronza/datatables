<style type="text/css">
.ib-toggle,
.ib-ajax
{
    cursor: pointer;
}

.ib-spin
{
    position: absolute;
    left: 4px;
    top: 3px;
    background-color: rgba(2, 122, 32, 0.7)!important;
    color: #fff;
}
</style>

<script type="text/javascript">

window.editorFieldHasChanged = function(target)
{
    let val = $(target).val();
    let oldVal = $(target).data('originalvalue');

    return oldVal != val;
}



$(document).ready(function($)
{


    $('body').on('click', '.ib-table-action-button', function(e)
    {
        e.preventDefault();

        var target = this;

        if(window.__mustOpenIframe(target))
            return window.__openIframe(target);

        var modal = $('#deliveries-modal');
        if(modal.length)
            UIkit.modal(modal).hide();

        var tableid = $(this).data('tableid');
        var table = $('#' + tableid).DataTable();

        let idColumnIndex = window.__getIdColumnIndex(this, table);

        let selectedRows = table.rows( { selected: true } );
        var ids = selectedRows.data().pluck(idColumnIndex).toArray();

        var url = $(this).data('route');

        // window.open(url + '?iframed=true&callertablename=' + tableVarName, 'window', 'width=960, height=600, toolbar=no, menubar=no, resizable=yes');

        // openWindowWithPost(url, {
        //     ids: ids,
        // });


        // return false;

        $.ajax({
            url : url,
            type : 'POST',
            data : {
                ids : ids
            },
            success : function (response)
            {
                if(response.success == false)
                    return this.error(response);

                // if(typeof response.notReload === 'undefined')
                //     table.ajax.reload();

                if(response.success == true)
                    window.addSuccessNotification(response.message);

                if((typeof response.action !== 'undefined'))
                {
                    if(response.action == 'reloadTable')
                    {
                        table.rows().deselect();
                        table.ajax.reload();
                    }

                    else if(response.action == 'reload')
                        location.reload();

                    else if(response.action == 'redirect')
                        window.location.href = response.route;

                    if(response.action == 'remove')
                    {
                        response.ids.forEach(function(value)
                        {
                            table.row("#" + value).remove();
                        });
                        
                        table.draw();
                    }
                }

                window.__checkResultPopup(response, {
                    target: target
                });

                window.__displayResponseErrors(response);
            },
            error: function (response)
            {
                table.ajax.reload();
                table.draw();

                window.__displayResponseErrors(response);
            }
        });
    });


    $('body').on('click', '.ib-toggle', function(e)
    {
        var params = {
            target : this,
            e : e,
            type : 'POST',
            data : {
                "ib-editor" : true,
                toggle : true,
                field : $(this).data('field'),
                _method : 'PUT',
            }
        };

        window.ibCallAjax(params);
    });

    $('body').on('click', '.ib-ajax', function(e)
    {
        let data = $(this).data();

        data['ib-editor'] = true;
        data._method = 'PUT';

        var params = {
            target : this,
            e : e,
            type : 'POST',
            data : data
        };

        window.ibCallAjax(params);
    });

    $('body').on('blur', '.ib-editor-color', function(e)
    {
        if(! window.editorFieldHasChanged(this))
            return false;

        var params = {
            target : this,
            e : e,
            type : 'POST',
            data : {
                "ib-editor" : true,
                field : $(this).data('field'),
                value : $(this).val(),
                _method : 'PUT',
            }
        };

        window.ibCallAjax(params);
    });

    $('body').on('blur', '.ib-editor-text', function(e)
    {
        if(! window.editorFieldHasChanged(this))
            return false;

        var params = {
            target : this,
            e : e,
            type : 'POST',
            data : {
                "ib-editor" : true,
                field : $(this).data('field'),
                value : $(this).val(),
                _method : 'PUT',
            }
        };

        window.ibCallAjax(params);
    });

    $('body').on('click', '.ib-editor-select', function(e)
    {
        if($(this).data('populated'))
            return ;

        if($(this).data('populating'))
            return ;


        $(this).data('populating', true);

        let currentValue = $(this).val();

        let th = window.__getTH(this);
        let possibleValues = th.data('possiblevalues');

        for (var key in possibleValues)
        {
            if(key == currentValue)
                continue;

            $(this).append('<option value="' + key + '">' + possibleValues[key] + '</option>');
        }

        $(this).data('populated', true);
        $(this).data('populating', false);
    });

    $('body').on('blur', '.ib-editor-select', function(e)
    {
        if(! window.editorFieldHasChanged(this))
            return false;

        if($(this).val() == $(this).data('originalvalue'))
            return false;

        var params = {
            target : this,
            e : e,
            type : 'POST',
            data : {
                "ib-editor" : true,
                field : $(this).data('field'),
                value : $(this).val(),
                _method : 'PUT',
            }
        };

        window.ibCallAjax(params);
    });

    $('body').on('click', '.ib-cell-ajax-button', function(e)
    {
        e.preventDefault();

        let sendingData = {};

        // var data = $(this).data('datas');

        // if($(this).data('dataattributes'))
        //     data = collectDataAttributes(this);

        // else if($(this).data('elements'))
        //     data = collectDatas(this);

        // if($(this).data('dataattributes'))
        //     var sendingData = data;
        // else
        //     var sendingData = {data : data};

        if($(this).data('method'))
            sendingData._method = $(this).data('method');

        var params = {
            target : this,
            e : e,
            data : sendingData
        };

        window.ibCallAjax(params);
    });
});
    
</script>