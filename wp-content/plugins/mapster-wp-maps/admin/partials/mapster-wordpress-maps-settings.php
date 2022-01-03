<?php

	$settings_page_id = get_option('mapster_settings_page');

?>
	<style>
	.acf-form-submit {
		margin: 10px;
	}
	</style>
	<div class="wrap">

		<h1>Mapster Maps Settings</h1>
		<p>See <a href="https://wpmaps.mapster.me/documentation" target="_blank">our website</a> for documentation and tutorials, and get in touch with us anytime.</p>
		<div style="display: flex;">
			<div style="width: 50%;">
				<?php
					acf_form(array(
						"post_id" => $settings_page_id,
						'submit_value' => __("Save Settings", 'acf'),
						'updated_message' => __("Settings saved!", 'acf'),
					));
				?>
			</div>
			<div style="width: 50%; padding-left: 20px;">
				<h3>Import from WP GL JS Maps</h3>
				<p>This makes it easy (hopefully) to switch from our old Mapbox plugin to this new one. Export from your old map and import here. All your features will be added with their old styling included, then you will just need to go and create a new Map, and add your old features to it again.</p>
				<p><a href="https://wpmaps.mapster.me/documentation" target="_blank">See our documentation for step-by-step instructions</a>.</p>
				<div style="margin-bottom: 10px;">
					<div>
						<input id="gl-js-import-file" type="file" />
					</div>
					<div>
						<p>You can assign these features to a specific category (this makes it faster to import them to a map later). If you need to add a category, click Categories on the left menu.</p>
						<select id="gl-js-import-category">
							<option value="">(none)</option>
							<?php
							 	$terms = get_terms(array( 'taxonomy' => 'wp-map-category', 'hide_empty' => false ));
								foreach($terms as $term) { ?>
									<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
								<?php }
							?>
						</select>
					</div>
				</div>
				<button id="gl-js-import-button" class="button button-primary button-large">Import</button>
				<div id="gl-js-import-result" style="display: none; margin-top: 10px;">
					<span></span> features imported!
				</div>
				<hr style="margin-top: 20px;" />
				<h3>Import GeoJSON</h3>
				<p>You can import a large, multi-featured geoJSON here if you want to get everything into the plugin at once. You will have to go through and style your features individually, though.</p>
				<div style="margin-bottom: 10px;">
					<div>
						<input id="geojson-import-file" type="file" />
					</div>
					<div>
						<p>You can assign these features to a specific category (this makes it faster to import them to a map later). If you need to add a category, click Categories on the left menu.</p>
						<select id="geojson-import-category">
							<option value="">(none)</option>
							<?php
							 	$terms = get_terms(array( 'taxonomy' => 'wp-map-category', 'hide_empty' => false ));
								foreach($terms as $term) { ?>
									<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
								<?php }
							?>
						</select>
					</div>
				</div>
				<button id="geojson-import-button" class="button button-primary button-large">Import</button>
				<div id="geojson-import-result" style="display: none; margin-top: 10px;">
					<span></span> features imported!
				</div>
			</div>
		</div>

	</div>
