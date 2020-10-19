@if($table->showCounters)
<div class="uk-width-auto">
    
    <div class="tablecounter uk-flex uk-width-small uk-child-width-1-3" id="tablecounter-{{ $table->id }}">
        <div class="checked">
            <i></i>
            <span class="uk-badge"></span>
        </div>
        <div class="visible">
            <i></i>
            <span class="uk-badge"></span>
        </div>
        <div class="total">
            <i></i>
            <span class="uk-badge"></span>
        </div>
    </div>

</div>
@endif