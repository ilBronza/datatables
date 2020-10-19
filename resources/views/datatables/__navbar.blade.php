@if(count($table->buttons))
<!-- START \views\datatables\__navbar.blade.php -->

<div class="subnavbar">
	<nav class="uk-navbar-container" uk-navbar>

	    <div class="uk-navbar-left">

	        <ul class="uk-navbar-nav">
	            @foreach($table->buttons as $button)
					{!! $button->renderLi() !!}
				@endforeach
	        </ul>

	    </div>

	    <div class="uk-navbar-right">

	        <ul class="uk-navbar-nav">
	        	<li><a href="#" class="icon bg-alert"><span class="icon-alert"></span></a></li>
	            <li><a href="#" class="icon"><span class="icon-vehicle"></span></a></li>
	            <li><a href="#" class="icon"><span class="icon-driver"></span></a></li>
	            <li><a href="#" class="icon"><span class="icon-link"></span></a></li>
	            <li><a href="#" class="icon"><span class="icon-create"></span></a></li>
	        </ul>

	    </div>

	</nav>
</div>

<!-- END \views\datatables\__navbar.blade.php -->
@endif