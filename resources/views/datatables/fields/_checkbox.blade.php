<label>
	<input class="uk-checkbox" type="checkbox" name={{ isset($name)? $name: "check"}} value={{ $value }}> 
	@isset($label)
		{{ $label }}
	@endisset
</label> 