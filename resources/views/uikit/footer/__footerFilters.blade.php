<tr class="columns">
    @foreach($table->getFields() as $field)
        @include('datatables::uikit.footer.__footerFilter')
    @endforeach
</tr>
