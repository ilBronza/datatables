<script>
    window.tabledata{{ $table->id }} = [

@foreach($table->elements as $element)
[@foreach($table->fieldsGroups as $group)
@foreach($group->fields as $field) @if(in_array($field->view, ['belongsToMany', 'belongsTo'])) @php 
	$content = view('datatables::datatables.fields._' . $field->view, [
						'tableName' => $table->name,
						'table' => $table,
						'value' => $table->resolveElement($field->name, $element),
						'element' => $element,
						'field' => $field
						])->render();

						echo trim(preg_replace('/\s+/', ' ', $content)); @endphp
	 @else '@php 
	$content = view('datatables::datatables.fields._' . $field->view, [
						'tableName' => $table->name,
						'table' => $table,
						'value' => $table->resolveElement($field->name, $element),
						'element' => $element,
						'field' => $field
						])->render();

						echo trim(preg_replace('/\s+/', ' ', $content)); @endphp '@endif @if(! $loop->last),@endif
@endforeach
@if(! $loop->last),@endif
@endforeach],
@endforeach];

</script>

