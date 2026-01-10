	<div class="colvis-settings-form" id="colvis-settings-form-template">

		<div class="uk-grid-small" uk-grid>
				<div class="uk-card uk-card-body uk-padding-small">

					<h4 class="uk-heading-divider">Style</h4>

					{{-- Font style --}}
					<div class="uk-margin-small">
						<label class="uk-form-label">Font style</label>
						<select class="uk-select ib-style" data-style="fontStyle">
							<option value="normal">Normal</option>
							<option value="italic">Italic</option>
						</select>
					</div>

					{{-- Font size --}}
					<div class="uk-margin-small">
						<label class="uk-form-label">Font size (px)</label>
						<input
								type="range"
								class="uk-range ib-style"
								data-style="fontSize"
								min="10"
								max="32"
								step="1"
								value="14"
						>
					</div>

					{{-- Letter spacing --}}
					<div class="uk-margin-small">
						<label class="uk-form-label">Letter spacing (px)</label>
						<input
								type="range"
								class="uk-range ib-style"
								data-style="letterSpacing"
								min="-1"
								max="6"
								step="0.5"
								value="0"
						>
					</div>

					{{-- Font weight --}}
					<div class="uk-margin-small">
						<label class="uk-form-label">Font weight</label>
						<select class="uk-select ib-style" data-style="fontWeight">
							<option value="400">Normal</option>
							<option value="600">Semi-bold</option>
							<option value="700">Bold</option>
						</select>
					</div>

					{{-- Text decoration --}}
					<div class="uk-margin-small">
						<label class="uk-form-label">Text decoration</label>
						<select class="uk-select ib-style" data-style="textDecoration">
							<option value="none">None</option>
							<option value="underline">Underline</option>
							<option value="line-through">Line-through</option>
						</select>
					</div>

					{{-- Text align --}}
					<div class="uk-margin-small">
						<label class="uk-form-label">Text align</label>
						<select class="uk-select ib-style" data-style="textAlign">
							<option value="left">Left</option>
							<option value="center">Center</option>
							<option value="right">Right</option>
						</select>
					</div>

					<h4 class="uk-heading-divider uk-margin-top">Colors</h4>

					{{-- Text color --}}
					<div class="uk-margin-small">
						<label class="uk-form-label">Text color</label>
						<input type="color" class="uk-input ib-style" data-style="color" value="#111111">
					</div>

					{{-- Background color --}}
					<div class="uk-margin-small">
						<label class="uk-form-label">Background color</label>
						<input type="color" class="uk-input ib-style" data-style="backgroundColor" value="#ffffff">
					</div>

			</div>
		</div>
	</div>
