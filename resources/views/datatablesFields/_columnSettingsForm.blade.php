<div class="colvis-settings-form" id="colvis-settings-form-template" style="display:none;">

		<div>
			<div class="">

					{{-- Width --}}

					<div class="uk-margin-small uk-flex">
						<label class="uk-form-label">Width (px)</label>
						<input
							type="range"
							class="uk-range ib-style uk-width-expand uk-margin-left"
							data-style="width"
							min="40"
							max="800"
							step="10"
							value=""
						>
					</div>

				<div class="uk-margin-small uk-padding-small uk-background-secondary uk-border-rounded">

					<div class="uk-flex uk-flex-between uk-margin-small">

						<div class="uk-width-1-3">
							<label class="uk-form-label uk-display-block">Text</label>
							<input type="color" class="uk-input ib-style" data-style="color" value="#111111">
						</div>

						<div class="uk-width-1-3">
							<label class="uk-form-label uk-display-block">Background</label>
							<input type="color" class="uk-input ib-style" data-style="background-color" value="#ffffff">
						</div>

					</div>
				</div>


				<div class="uk-margin-small uk-padding-small uk-background-secondary uk-border-rounded">

						<div class="uk-flex uk-flex-between">

							{{-- Font size --}}
							<div>
								<label class="uk-form-label uk-display-block">Font size</label>
								<input
										type="range"
										class="uk-range ib-style"
										data-style="font-size"
										min="10"
										max="32"
										step="1"
										value="14"
								>
							</div>

							{{-- Letter spacing --}}
							<div>
								<label class="uk-form-label uk-display-block">Letter spacing</label>
								<input
										type="range"
										class="uk-range ib-style"
										data-style="letter-spacing"
										min="-1"
										max="6"
										step="0.5"
										value="0"
								>
							</div>
						</div>


						<div class="uk-flex uk-flex-between uk-margin-small">

							{{-- Font style --}}
							<div>

								<div class="uk-button-group ib-style-buttons" data-style="font-style">
									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="normal"
											uk-tooltip="Normal"
									>
										<i class="fa-solid fa-font"></i>
									</button>

									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="italic"
											uk-tooltip="Italic"
									>
										<i class="fa-solid fa-italic"></i>
									</button>
								</div>
							</div>



							{{-- Font weight --}}
							<div>

								<div class="uk-button-group ib-style-buttons" data-style="font-weight">
									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="300"
											uk-tooltip="Thin"
									>
										<span style="font-weight: 300;">T</span>
									</button>

									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="400"
											uk-tooltip="Normal"
									>
										<span style="font-weight: 400;">N</span>
									</button>

									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="600"
											uk-tooltip="Semi-bold"
									>
										<span style="font-weight: 600;">B</span>
									</button>

									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="700"
											uk-tooltip="Bold"
									>
										<span style="font-weight: 700;">B</span>
									</button>
								</div>
							</div>

						</div>

					{{-- Text transform --}}
					<div class="uk-margin-small">

						<div class="uk-button-group ib-style-buttons" data-style="text-transform">
							<button
									style="text-transform: none;"
									type="button"
									class="uk-button uk-button-default uk-button-small ib-style-button"
									data-value="none"
									uk-tooltip="None"
							>
								-
							</button>

							<button
									style="text-transform: none;"
									type="button"
									class="uk-button uk-button-default uk-button-small ib-style-button"
									data-value="uppercase"
									uk-tooltip="UPPERCASE"
							>
								AA
							</button>

							<button
									style="text-transform: none;"
									type="button"
									class="uk-button uk-button-default uk-button-small ib-style-button"
									data-value="lowercase"
									uk-tooltip="lowercase"
							>
								aa
							</button>

							<button
									style="text-transform: none;"
									type="button"
									class="uk-button uk-button-default uk-button-small ib-style-button"
									data-value="capitalize"
									uk-tooltip="Capitalize"
							>
								Ab
							</button>
						</div>
					</div>

					<div class="uk-flex uk-flex-between">

								<div>
									<div class="uk-button-group ib-style-buttons" data-style="text-decoration">
										<button
												type="button"
												class="uk-button uk-button-default uk-button-small ib-style-button"
												data-value="none"
												uk-tooltip="None"
										>
											<i class="fa-solid fa-ban"></i>
										</button>

										<button
												type="button"
												class="uk-button uk-button-default uk-button-small ib-style-button"
												data-value="underline"
												uk-tooltip="Underline"
										>
											<i class="fa-solid fa-underline"></i>
										</button>

										<button
												type="button"
												class="uk-button uk-button-default uk-button-small ib-style-button"
												data-value="line-through"
												uk-tooltip="Line-through"
										>
											<i class="fa-solid fa-strikethrough"></i>
										</button>
									</div>
								</div>


							{{-- Letter spacing --}}
							<div>
								<div class="uk-button-group ib-style-buttons" data-style="text-align">
									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="left"
											uk-tooltip="Left"
									>
										<i class="fa-solid fa-align-left"></i>
									</button>

									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="center"
											uk-tooltip="Center"
									>
										<i class="fa-solid fa-align-center"></i>
									</button>

									<button
											type="button"
											class="uk-button uk-button-default uk-button-small ib-style-button"
											data-value="right"
											uk-tooltip="Right"
									>
										<i class="fa-solid fa-align-right"></i>
									</button>
								</div>
							</div>
						</div>



					<div class="uk-margin-small">
						<label class="uk-form-label">Font family</label>

						<div class="uk-button-group ib-style-buttons" data-style="font-family">
							<select class="uk-select ib-style" data-style="font-family">
								<option value="inherit" style="font-family: inherit;">Inherit</option>

@foreach(config('datatables.fonts') as $label => $stack)
    <option value="{{ $stack }}" style="font-family: {{ $stack }};">
        {{ $label }}
    </option>
@endforeach
							</select>
						</div>
					</div>

					{{-- Text overflow --}}
					<div class="uk-margin-small">
						<label class="uk-form-label uk-display-block">Text overflow</label>

						<div class="uk-button-group ib-style-buttons" data-style="text-overflow-mode">
							<button
								type="button"
								class="uk-button uk-button-default uk-button-small ib-style-button"
								data-value="wrap"
								uk-tooltip="Wrap"
							>
								<i class="fa-solid fa-align-left"></i> <small>Wrap</small>
							</button>

							<button
								type="button"
								class="uk-button uk-button-default uk-button-small ib-style-button"
								data-value="clip"
								uk-tooltip="No wrap"
							>
								<i class="fa-solid fa-grip-lines-vertical"></i> <small>No wrap</small>
							</button>

							<button
								type="button"
								class="uk-button uk-button-default uk-button-small ib-style-button"
								data-value="ellipsis"
								uk-tooltip="Ellipsis"
							>
								<i class="fa-solid fa-ellipsis"></i> <small>…</small>
							</button>

							<button
								type="button"
								class="uk-button uk-button-default uk-button-small ib-style-button"
								data-value="tooltip"
								uk-tooltip="Tooltip"
							>
								<i class="fa-solid fa-comment-dots"></i> <small>Tip</small>
							</button>
						</div>
					</div>
				</div>

				<div class="uk-margin-small uk-padding-small uk-background-secondary uk-border-rounded">

					<div class="uk-button-group ib-style-buttons">
						<button
								type="button"
								class="uk-button uk-button-default uk-button-small ib-reset-settings-button uk-margin-small-right"
								data-value="reset"
								uk-tooltip="Reset"
						>
							Reset <i class="fa-solid fa-rotate-left"></i>
						</button>

						<button
								type="button"
								class="uk-button uk-button-default uk-button-small ib-cancel-settings-button uk-margin-small-right"
								data-value="cancel"
								uk-tooltip="Cancel"
						>
							Cancel <i class="fa-solid fa-cancel"></i>
						</button>



						<button
								type="button"
								class="uk-button uk-button-default uk-button-small ib-colvis-save"
								data-value="save"
								uk-tooltip="Save"
						>
							Save <i class="fa-solid fa-save"></i>
						</button>

					</div>
				</div>

			</div>
		</div>
	</div>
