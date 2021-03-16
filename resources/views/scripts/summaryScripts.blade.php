<script type="text/javascript">
jQuery(document).ready(function()
{
    window.transformDataBySummaryExistence = function (tableId, summary, json)
    {
        if(summary)
        {
            let data = json.data;

            let summaryValues = json.data.pop();
            let summaryRow = $('#' + tableId).find('thead tr.summary');

            summaryValues.forEach(function(element, index)
            {
                let th = $(summaryRow).find('.summary' + index);
                $(th).html(element);
            });
        }

        return json.data;
    }

    window.populateFilteredColumnValues = function (api, tableId)
    {
        // let filteredRows = api.rows({filter:'applied'}).data();
        let filterRowsOptions = {};

        if(window[ tableId + 'FilteredSelected'])
            filterRowsOptions = {selected: true};

        let filteredRows = api.rows(filterRowsOptions);

        $('#' + tableId).find('thead tr.columns th').each(function(columnIndex, element)
        {
            let summary = $(element).find('input').data('summary');

            if(typeof summary === 'undefined')
                return;

            let realColumnNumber = $(element).data('column');
            let th = $('#' + tableId + ' .inlinesearchsummary').find('.summary' + realColumnNumber);

            if(summary == 'sumMinutes')
            {
                // api.rows({selected: true}).every(function (rowIdx, tableLoop, rowLoop)
                // {
                //     console.log('uno');
                //     console.log(rowIdx);
                //     console.log(tableLoop);
                //     console.log(rowLoop);

                //     var data = this.cells(rowIdx, 4).data();

                //     console.log(data);
                // });
            }

            if((summary == 'average')||(summary == 'sum')||(summary == 'sumMinutes'))
            {
                let result = 0;
                let totalRowsFilteredCount = 0;

                var selection = {};

                filteredRows.every(function (rowIdx, tableLoop, rowLoop)
                {
                    var value = this.cell(rowIdx, realColumnNumber, { search:'applied' }).data();

                    if(! isNaN(float = parseFloat(value)))
                        result = result + float;

                    totalRowsFilteredCount = totalRowsFilteredCount + 1;
                });

                // api.column(columnIndex, { search:'applied' } ).data().each(function(value, rowIndex) {

                //     // console.log('rowIndex');
                //     // console.log(rowIndex);
                //     // console.log(value);

                //     // if(! isNaN(float = parseFloat(value)))
                //     //     result = result + float;

                //     // totalRowsFilteredCount = rowIndex;
                // });

                if(summary == 'average')
                    result = Math.round(((result / (totalRowsFilteredCount + 1)) + Number.EPSILON) * 100) / 100

                if(summary == 'sumMinutes')
                {
                    let hours = Math.floor((result / 60)) + "h";
                    let minutes = Math.floor((result % 60)) + "'";

                    result = hours + ' ' + minutes;
                }

                $(th).html(result);
            }
            else if((summary == 'distinct'))
            {
                let result = new Array();

                filteredRows.every(function (rowIdx, tableLoop, rowLoop)
                {
                    var value = this.cell(rowIdx, realColumnNumber, { search:'applied' }).data();

                    if(typeof result[value] === 'undefined')
                        result[value] = 0;

                    result[value] ++;
                });


                // api.column(columnIndex, { search:'applied' } ).data().each(function(value, rowIndex) {
                //     if(typeof result[value] === 'undefined')
                //         result[value] = 0;

                //     result[value] ++;
                // });

                let string = new Array;

                Object.keys(result).map(function(key, index)
                {
                    string.push(key + ': ' + result[key]);
                });

                $(th).html(string.join('<br />'));
            }

        });

    }

});
</script>