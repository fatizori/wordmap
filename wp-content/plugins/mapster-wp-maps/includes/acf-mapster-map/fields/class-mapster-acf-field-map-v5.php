<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('mapster_acf_field_map') ) :


class mapster_acf_field_map extends acf_field {


	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct( $settings ) {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'mapster-map';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('Mapster Map', 'mapster_acf_plugin_map');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'jquery';


		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/

		// $this->default_value = 'default';

		$this->defaults = array(
			'default_value'	=> '{ "type" : "FeatureCollection", "features" : [] }',
		);


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('FIELD_NAME', 'error');
		*/

		// $this->l10n = array(
		// 	'error'	=> __('Error! Please enter a higher value', 'mapster_acf_plugin_map'),
		// );


		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/

		$this->settings = $settings;


		// do not delete!
    	parent::__construct();

	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {

		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/

		acf_render_field_setting( $field, array(
			'label'			=> __('Points','mapster_acf_plugin_map'),
			'instructions'	=> __('Allow Point creation.','mapster_acf_plugin_map'),
			'default_value' => 1,
		  'ui' => 1,
		  'ui_on_text' => 'On',
		  'ui_off_text' => 'Off',
  		'type' => 'true_false',
			'name'			=> 'mapster-draw-type-point'
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('LineStrings','mapster_acf_plugin_map'),
			'instructions'	=> __('Allow LineString creation.','mapster_acf_plugin_map'),
			'default_value' => 1,
		  'ui' => 1,
		  'ui_on_text' => 'On',
		  'ui_off_text' => 'Off',
  		'type' => 'true_false',
			'name'			=> 'mapster-draw-type-linestring'
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Polygons','mapster_acf_plugin_map'),
			'instructions'	=> __('Allow Polygon creation.','mapster_acf_plugin_map'),
			'default_value' => 1,
		  'ui' => 1,
		  'ui_on_text' => 'On',
		  'ui_off_text' => 'Off',
  		'type' => 'true_false',
			'name'			=> 'mapster-draw-type-polygon'
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Multiple Features','mapster_acf_plugin_map'),
			'instructions'	=> __('Allow user to add multiple features in a single map field.','mapster_acf_plugin_map'),
			'default_value' => 1,
		  'ui' => 1,
		  'ui_on_text' => 'On',
		  'ui_off_text' => 'Off',
  		'type' => 'true_false',
			'name'			=> 'mapster-draw-type-multiple'
		));

	}



	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {


		/*
		*  Review the data of $field.
		*  This will show what data is available
		*/

		// echo '<pre>';
		// 	print_r( $field );
		// echo '</pre>';


		/*
		*  Create a simple text input using the 'font_size' setting.
		*/

		?>
		<input
			type="text"
			id="mapster-map-geojson-<?php echo $field['ID']; ?>"
			name="<?php echo esc_attr($field['name']) ?>"
			value="<?php echo esc_attr($field['value']) ?>"
			style="display:none;"
		/>

		<div class="mapster-map-container">
			<div>
				<div
					class="mapster-map"
					id="mapster-map-<?php echo $field['ID']; ?>"
					style="height: 400px; width: 100%;"
					data-point="<?php echo esc_attr($field['mapster-draw-type-point']); ?>"
					data-linestring="<?php echo esc_attr($field['mapster-draw-type-linestring']); ?>"
					data-polygon="<?php echo esc_attr($field['mapster-draw-type-polygon']); ?>"
					data-multiple="<?php echo esc_attr($field['mapster-draw-type-multiple']); ?>"
				></div>
			</div>
			<div>
				<div class="mapster-map-input-container">
					<?php if(esc_attr($field['mapster-draw-type-point']) == 1) { ?>
						<div class="acf-label">
							<label>Search for address</label>
							<p class="description">Enter address to place a location on the map.</p>
						</div>
						<div>
							<input id="mapster-map-geosearch" type="text" placeholder="Enter address or location" />
							<ul id="mapster-geocoder-results"></ul>
						</div>
						<div class="button-container">
							<div id="mapster-get-results" class="button button-primary">Search</div>
						</div>
						<div class="mapster-map-line">
							<div>OR</div> <hr />
						</div>
						<div class="acf-label">
							<label>Select a point manually</label>
							<p class="description">Click below and then press on the map to create a location.</p>
						</div>
						<div id="draw-point" class="button button-primary"><?php echo $field['value'] ? 'Replace' : 'Start'; ?> Drawing</div>
						<div id="draw-delete" class="button button-secondary">Delete</div>
					<?php } ?>
					<?php if(esc_attr($field['mapster-draw-type-linestring']) == 1) { ?>
						<div class="acf-label">
							<label>Search for address</label>
							<p class="description">Enter address to move the map closer to your desired area.</p>
						</div>
						<div>
							<input id="mapster-map-geosearch" type="text" placeholder="Enter address or location" />
							<ul id="mapster-geocoder-results"></ul>
						</div>
						<div class="button-container">
							<div id="mapster-get-results" class="button button-primary">Search</div>
						</div>
						<div class="acf-label">
							<label>Draw a line manually</label>
							<p class="description">Click below and then click multiple times on the map to create a line. Click twice on the last point to complete the line.</p>
						</div>
						<div id="draw-linestring" class="button button-primary"><?php echo $field['value'] ? 'Replace' : 'Start'; ?> Drawing</div>
						<div id="edit-linestring" class="button button-secondary">Edit Drawing</div>
						<div id="draw-delete" class="button button-tertiary">Delete</div>
						<div id="finish-drawing">
							<div class="button button-primary">Done Drawing</div>
						</div>
						<div class="mapster-map-line">
							<div>OR</div> <hr />
						</div>
						<div class="acf-label">
							<label>Upload a GeoJSON</label>
							<p class="description">Upload a GeoJSON with a single LineString feature. See an example.</p>
						</div>
						<div>
							<input id="mapster-map-upload" data-type="LineString" type="file" />'
						</div>
					<?php } ?>
					<?php if(esc_attr($field['mapster-draw-type-polygon']) == 1) { ?>
						<div class="acf-label">
							<label>Search for address</label>
							<p class="description">Enter address to move the map closer to your desired area.</p>
						</div>
						<div>
							<input id="mapster-map-geosearch" type="text" placeholder="Enter address or location" />
							<ul id="mapster-geocoder-results"></ul>
						</div>
						<div class="button-container">
							<div id="mapster-get-results" class="button button-primary">Search</div>
						</div>
						<div class="acf-label">
							<label>Draw a polygon manually</label>
							<p class="description">Click below and then click multiple times on the map to create a polygon. Connect to the beginning of the polygon to complete the shape.</p>
						</div>
						<div id="draw-polygon" class="button button-primary"><?php echo $field['value'] ? 'Replace' : 'Start'; ?> Drawing</div>
						<div id="edit-polygon" class="button button-secondary">Edit Drawing</div>
						<div id="draw-delete" class="button button-tertiary">Delete</div>
						<div id="finish-drawing">
							<div class="button button-primary">Done Drawing</div>
						</div>
						<div class="mapster-map-line">
							<div>OR</div> <hr />
						</div>
						<div class="acf-label">
							<label>Upload a GeoJSON</label>
							<p class="description">Upload a GeoJSON with a single Polygon feature. See an example.</p>
						</div>
						<div>
							<input id="mapster-map-upload" data-type="Polygon" type="file" />'
						</div>
					<?php } ?>
				</div>
				<div class="mapster-map-input-container">
					<?php if(esc_attr($field['mapster-draw-type-point']) == 1) { ?>
						<div class="acf-label" style="margin-top: 50px;">
							<label>Current Location Coordinates</label>
							<p class="description"><span id="current-coordinates"></span></p>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>

		<?php
	}


	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function input_admin_enqueue_scripts() {

		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];

  	$current_screen = get_current_screen();
  	if( $current_screen->id !== "mapster-wp-map" ) {

			// register & include JS
			wp_enqueue_script('mapster_map_field_turf_js', "{$url}assets/js/turf.js", array('acf-input'), $version);
			wp_enqueue_script('mapster_map_field_maplibre_js', "{$url}assets/js/maplibre-1.15.2.js", array('mapster_map_field_turf_js'), $version);
			wp_enqueue_script('mapster_map_field_mapbox_draw_js', "{$url}assets/js/mapbox-gl-draw.js", array('mapster_map_field_maplibre_js'), $version);
			wp_enqueue_script('mapster_map_field_geosearch_js', "{$url}assets/js/leaflet-geosearch-3.0.5.js", array('mapster_map_field_mapbox_draw_js'), $version);
			wp_enqueue_script('mapster_map_field_geojsonhint_js', "{$url}assets/js/geojsonhint.js", array('mapster_map_field_mapbox_draw_js'), $version);
			wp_enqueue_script('mapster_mapbox_field_js', "{$url}assets/js/input.js", array('mapster_map_field_geojsonhint_js'), $version);

			// register & include CSS
			wp_enqueue_style('mapster_map_field_maplibre_css', "{$url}assets/css/maplibre-1.15.2.css", array('acf-input'), $version);
			wp_enqueue_style('mapster_map_field_geosearch_css', "{$url}assets/css/leaflet-geosearch-3.0.5.css", array('mapster_map_field_maplibre_css'), $version);
			wp_enqueue_style('mapster_map_field_maplibre_draw_css', "{$url}assets/css/mapbox-gl-draw.css", array('mapster_map_field_geosearch_css'), $version);
			wp_enqueue_style('mapster_map_field_css', "{$url}assets/css/input.css", array('mapster_map_field_maplibre_draw_css'), $version);

		}

	}


	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_head() {



	}

	*/


	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/

   	/*

   	function input_form_data( $args ) {



   	}

   	*/


	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_footer() {



	}

	*/


	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_enqueue_scripts() {

	}

	*/


	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_head() {

	}

	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	/*

	function load_value( $value, $post_id, $field ) {

		return $value;

	}

	*/


	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	/*

	function update_value( $value, $post_id, $field ) {

		return $value;

	}

	*/


	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/

	/*

	function format_value( $value, $post_id, $field ) {

		// bail early if no value
		if( empty($value) ) {

			return $value;

		}


		// apply setting
		if( $field['font_size'] > 12 ) {

			// format the value
			// $value = 'something';

		}


		// return
		return $value;
	}

	*/


	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/

	/*

	function validate_value( $valid, $value, $field, $input ){

		// Basic usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = false;
		}


		// Advanced usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = __('The value is too little!','TEXTDOMAIN'),
		}


		// return
		return $valid;

	}

	*/


	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/

	/*

	function delete_value( $post_id, $key ) {



	}

	*/


	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function load_field( $field ) {

		return $field;

	}

	*/


	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function update_field( $field ) {

		return $field;

	}

	*/


	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/

	/*

	function delete_field( $field ) {



	}

	*/


}


// initialize
new mapster_acf_field_map( $this->settings );


// class_exists check
endif;

?>
