<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mapster.me
 * @since      1.0.0
 *
 * @package    Mapster_Wordpress_Maps
 * @subpackage Mapster_Wordpress_Maps/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mapster_Wordpress_Maps
 * @subpackage Mapster_Wordpress_Maps/admin
 * @author     Mapster Technology Inc <hello@mapster.me>
 */
class Mapster_Wordpress_Maps_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

  	$current_screen = get_current_screen();
  	if( $current_screen->id === "mapster-wp-map" ) {
			// register & include CSS
    	global $post;
			$map_type = get_field('map_type', $post->ID);
			if($map_type && $map_type['map_provider'] == 'mapbox') {
				wp_enqueue_style('mapster_map_mapbox_css', plugin_dir_url( __FILE__ ) . "css/vendor/mapbox-gl-2.4.1.css", array(), $this->version);
			} else {
				wp_enqueue_style('mapster_map_maplibre_css', plugin_dir_url( __FILE__ ) . "css/vendor/maplibre-1.15.2.css", array(), $this->version);
			}
			wp_enqueue_style('mapster_map_directions_css', plugin_dir_url( __FILE__ ) . "css/vendor/directions.css", array(), $this->version);
			wp_enqueue_style('mapster_map_geocoder_css', plugin_dir_url( __FILE__ ) . "css/vendor/mapbox-gl-geocoder-4.7.2.css", array(), $this->version);
			wp_enqueue_style('mapster_map_geosearch_css', plugin_dir_url( __FILE__ ) . "css/vendor/leaflet-geosearch-3.0.5.css", array(), $this->version);
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mapster-wordpress-maps.css', array(), $this->version, 'all' );
		}

  	if( $current_screen->id === "mapster-wp-popup" ) {
			wp_enqueue_style('mapster_map_maplibre_css', plugin_dir_url( __FILE__ ) . "css/vendor/maplibre-1.15.2.css", array(), $this->version);
			wp_enqueue_style($this->plugin_name . '-popup', plugin_dir_url( __FILE__ ) . "css/mapster-wordpress-popup.css", array(), $this->version);
		}

		wp_enqueue_style( "mapster_general_admin", plugin_dir_url( __FILE__ ) . 'css/mapster-general-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ) {

  	$current_screen = get_current_screen();
  	if( $current_screen->id === "mapster-wp-map" ) {
			$settings_page_id = get_option('mapster_settings_page');
			$access_token = get_field('default_access_token', $settings_page_id);
			// register & include JS
    	global $post;
			$map_type = get_field('map_type', $post->ID);
			if($map_type && $map_type['map_provider'] == 'mapbox') {
				wp_enqueue_script('mapster_map_map_library', plugin_dir_url( __FILE__ ) . "js/vendor/mapbox-gl-2.4.1.js", array('jquery'), $this->version);
			} else {
				wp_enqueue_script('mapster_map_map_library', plugin_dir_url( __FILE__ ) . "js/vendor/maplibre-1.15.2.js", array('jquery'), $this->version);
			}
			wp_enqueue_script('mapster_map_turf_js', plugin_dir_url( __FILE__ ) . "js/vendor/turf-6.5.0.js", array('mapster_map_map_library'), $this->version);
			wp_enqueue_script('mapster_map_directions_js', plugin_dir_url( __FILE__ ) . "js/vendor/mapbox-gl-directions-4.1.0.js", array('mapster_map_turf_js'), $this->version);
			wp_enqueue_script('mapster_map_geocoder_js', plugin_dir_url( __FILE__ ) . "js/vendor/mapbox-gl-geocoder-4.7.2.js", array('mapster_map_directions_js'), $this->version);
			wp_enqueue_script('mapster_map_geosearch_js', plugin_dir_url( __FILE__ ) . "js/vendor/leaflet-geosearch-3.0.5.js", array('mapster_map_geocoder_js'), $this->version);
			wp_register_script($this->plugin_name, plugin_dir_url( __FILE__ ) . '/js/dist/mwp.js', array('mapster_map_geosearch_js'), $this->version, 'all' );
			wp_localize_script($this->plugin_name, 'params', array(
					'public' => false,
					'rest_url' => get_rest_url(),
					'mapbox_access_token' => $access_token
			));
			wp_enqueue_script( $this->plugin_name );
		}

  	if( $current_screen->id === "mapster-wp-map_page_wordpress-maps-settings" ) {
			wp_register_script('mapster_map_settings_js', plugin_dir_url( __FILE__ ) . '/js/mapster-wordpress-maps-settings.js', array('jquery'), $this->version, true );
			wp_localize_script('mapster_map_settings_js', 'params', array(
					'rest_url' => get_rest_url(),
					'nonce' => wp_create_nonce( 'wp_rest' )
			));
			wp_enqueue_script('mapster_map_settings_js');
		}

  	if( $current_screen->id === "mapster-wp-popup" ) {
			wp_enqueue_script('mapster_map_map_library', plugin_dir_url( __FILE__ ) . "js/vendor/maplibre-1.15.2.js", array('acf-input'), $this->version);
			wp_enqueue_script($this->plugin_name . '-popup', plugin_dir_url( __FILE__ ) . '/js/dist/mwp-popup.js', array('mapster_map_map_library'), $this->version);
		}

		if( $current_screen->id == "edit-mapster-wp-popup" ||  $current_screen->id == "edit-mapster-wp-map" || $current_screen->id == "edit-mapster-wp-location" || $current_screen->id == "edit-mapster-wp-line" || $current_screen->id == "edit-mapster-wp-polygon") {
			wp_register_script($this->plugin_name . '-general', plugin_dir_url( __FILE__ ) . '/js/mapster-wordpress-maps-general.js', array('jquery'), $this->version, true );
			wp_localize_script($this->plugin_name . '-general', 'params', array(
					'rest_url' => get_rest_url(),
					'nonce' => wp_create_nonce( 'wp_rest' )
			));
			wp_enqueue_script($this->plugin_name . '-general');
		}

	}

	/**
	 * Create Mapster Wordpress Maps post type
	 *
	 * @since    1.0.0
	 */
	public function create_mapster_wp_maps_post_types()
	{
			$settings_page_id = get_option('mapster_settings_page');
			if($settings_page_id) {
				$public_pages = get_field('public_pages', $settings_page_id);
			}

			register_post_type('mapster-wp-map',
					array(
							'labels' => array(
									'name' => 'Maps',
									'menu_name' => 'Maps',
									'singular_name' => 'Map',
									'add_new' => 'Add New',
									'add_new_item' => 'Add New Map',
									'edit' => 'Edit',
									'edit_item' => 'Edit Map',
									'new_item' => 'New Map',
									'view' => 'View',
									'view_item' => 'View Map',
									'search_items' => 'Search Map',
									'not_found' => 'No Map found',
									'not_found_in_trash' => 'No Map found in Trash',
									'parent' => 'Parent Map',
							),
							'public' => true,
        		  "publicly_queryable"  => $public_pages['maps'] ? true : false,
		   				'exclude_from_search' => false,
							'show_in_rest' => true,
							'menu_position' => 15,
							'supports' => array('title', 'editor', 'excerpt', 'custom-fields'),
							'taxonomies' => array('wp-map-category'),
							'has_archive' => true,
					)
			);
	    register_taxonomy(
	        'wp-map-category',
	        'mapster-wp-map',
	        array(
	            'hierarchical' => true,
							'show_in_rest' => true,
	            'label' => 'Map Categories', // display name
	            'query_var' => true
	        )
	    );

			register_post_type('mapster-wp-location',
					array(
							'labels' => array(
									'name' => 'Locations',
									'menu_name' => 'Locations',
									'singular_name' => 'Location',
									'add_new' => 'Add New',
									'add_new_item' => 'Add New Location',
									'edit' => 'Edit',
									'edit_item' => 'Edit Location',
									'new_item' => 'New Location',
									'view' => 'View',
									'view_item' => 'View Location',
									'search_items' => 'Search Location',
									'not_found' => 'No Location found',
									'not_found_in_trash' => 'No Locations found in Trash',
									'parent' => 'Parent Location',
							),
							'public' => true,
        		  "publicly_queryable"  => $public_pages['locations'] ? true : false,
		   				'exclude_from_search' => false,
							'show_in_rest' => true,
							'menu_position' => 15,
							'supports' => array('title', 'editor', 'excerpt', 'custom-fields'),
							'taxonomies' => array('wp-map-category'),
							'has_archive' => true,
    					'show_in_menu' => 'edit.php?post_type=mapster-wp-map'
					)
			);
			register_post_type('mapster-wp-line',
					array(
							'labels' => array(
									'name' => 'Lines',
									'menu_name' => 'Lines',
									'singular_name' => 'Line',
									'add_new' => 'Add New',
									'add_new_item' => 'Add New Line',
									'edit' => 'Edit',
									'edit_item' => 'Edit Line',
									'new_item' => 'New Line',
									'view' => 'View',
									'view_item' => 'View Line',
									'search_items' => 'Search Line',
									'not_found' => 'No Line found',
									'not_found_in_trash' => 'No Line found in Trash',
									'parent' => 'Parent Line',
							),
							'public' => true,
        		  "publicly_queryable"  => $public_pages['lines'] ? true : false,
		   				'exclude_from_search' => false,
							'show_in_rest' => true,
							'menu_position' => 15,
							'supports' => array('title', 'editor', 'excerpt', 'custom-fields'),
							'taxonomies' => array('wp-map-category'),
							'has_archive' => true,
    					'show_in_menu' => 'edit.php?post_type=mapster-wp-map'
					)
			);
			register_post_type('mapster-wp-polygon',
					array(
							'labels' => array(
									'name' => 'Polygons',
									'menu_name' => 'Polygons',
									'singular_name' => 'Polygon',
									'add_new' => 'Add New',
									'add_new_item' => 'Add New Polygon',
									'edit' => 'Edit',
									'edit_item' => 'Edit Polygon',
									'new_item' => 'New Polygon',
									'view' => 'View',
									'view_item' => 'View Polygon',
									'search_items' => 'Search Polygon',
									'not_found' => 'No Polygon found',
									'not_found_in_trash' => 'No Polygon found in Trash',
									'parent' => 'Parent Polygon',
							),
							'public' => true,
        		  "publicly_queryable"  => $public_pages['polygons'] ? true : false,
		   				'exclude_from_search' => false,
							'show_in_rest' => true,
							'menu_position' => 15,
							'supports' => array('title', 'editor', 'excerpt', 'custom-fields'),
							'taxonomies' => array('wp-map-category'),
							'has_archive' => true,
    					'show_in_menu' => 'edit.php?post_type=mapster-wp-map'
					)
			);
			register_post_type('mapster-wp-popup',
					array(
							'labels' => array(
									'name' => 'Popup Templates',
									'menu_name' => 'Popup Templates',
									'singular_name' => 'Popup Template',
									'add_new' => 'Add New',
									'add_new_item' => 'Add New Popup Template',
									'edit' => 'Edit',
									'edit_item' => 'Edit Popup Template',
									'new_item' => 'New Popup Template',
									'view' => 'View',
									'view_item' => 'View Popup Template',
									'search_items' => 'Search Popup Template',
									'not_found' => 'No Popup Template found',
									'not_found_in_trash' => 'No Popup Template found in Trash',
									'parent' => 'Parent Popup Template',
							),
							'public' => true,
        		  "publicly_queryable"  => false,
		   				'exclude_from_search' => false,
							'show_in_rest' => true,
							'menu_position' => 15,
							'supports' => array('title', 'custom-fields'),
							'taxonomies' => array(''),
							'has_archive' => true,
    					'show_in_menu' => 'edit.php?post_type=mapster-wp-map'
					)
			);
			register_post_type('mapster-wp-settings',
					array(
							'labels' => array(
									'name' => 'Mapster Settings',
									'menu_name' => 'Mapster Settings',
									'singular_name' => 'Mapster Settings',
									'add_new' => 'Add New',
									'add_new_item' => 'Add New Mapster Settings',
									'edit' => 'Edit',
									'edit_item' => 'Edit Mapster Settings',
									'new_item' => 'New Mapster Settings',
									'view' => 'View',
									'view_item' => 'View Mapster Settings',
									'search_items' => 'Search Mapster Settings',
									'not_found' => 'No Mapster Settings found',
									'not_found_in_trash' => 'No Mapster Settings found in Trash',
									'parent' => 'Parent Mapster Settings',
							),
							'public' => true,
        		  "publicly_queryable"  => false,
		   				'exclude_from_search' => true,
							'show_in_rest' => false,
							'supports' => array('custom-fields'),
							'taxonomies' => array(''),
							'has_archive' => false,
    					'show_in_menu' => false
					)
			);

			$set = get_option( 'post_type_rules_flushed_mapster_wp_maps' );
			if ( $set !== true ){
		    flush_rewrite_rules( false );
		    update_option( 'post_type_rules_flushed_mapster_wp_maps', true );
			}

	}

	/**
	 * Disable gutenberg for custom post types
	 *
	 * @since    1.0.0
	 */
	function mapster_maps_disable_gutenberg($current_status, $post_type) {

		$mapster_post_types = array('mapster-wp-map', 'mapster-wp-line', 'mapster-wp-polygon', 'mapster-wp-location');
		$settings_page_id = get_option('mapster_settings_page');
		$gutenberg_on = get_field('gutenberg_editor', $settings_page_id);
		if(!$gutenberg_on && in_array($post_type, $mapster_post_types)) {
			$current_status = false;
		}

    return $current_status;
	}

	/**
	 * Add shortcode column to Mapster Map type
	 *
	 * @since    1.0.0
	 */
  function set_custom_mapster_map_column($columns) {
			unset($columns['date']);
      $columns['shortcode'] = __( 'Shortcode', 'mapster-wp-maps' );
      $columns['date'] = __( 'Date', 'mapster-wp-maps' );
      return $columns;
  }

	/**
	 * Add shortcode output to Mapster map column
	 *
	 * @since    1.0.0
	 */
	function custom_mapster_map_shortcode_column( $column, $post_id ) {
      switch ( $column ) {
        case 'shortcode' :
          echo '[mapster_wp_map id="' . $post_id . '"]';
          break;
      }
    }

	/**
	 * Add Mapster Map Metabox for main map editing
	 *
	 * @since    1.0.0
	 */
	public function add_mapster_wp_map_metabox() {
		add_meta_box( 'mapster-wp-map-preview', 'Map Preview', 'my_meta_box_callback',
				'mapster-wp-map', 'normal', 'core',
				array(
						'__block_editor_compatible_meta_box' => true,
				)
		);
		function my_meta_box_callback() {
			echo '<div id="map" style="width: 100%; height: 400px;"></div>';
		}
	}

	/**
	 * Register default options
	 *
	 * @since    1.0.0
	 */
	public function add_mapster_wp_maps_default_options( ) {
			// Create default option
			if(!get_option( 'mapster_settings_page' )) {
				$settings_page_id = wp_insert_post(array(
					'post_type' => "mapster-wp-settings"
				));
				update_field('public_pages', array(
					'maps' => true,
					'locations' => true,
					'lines' => true,
					'polygons' => true
				), $settings_page_id);
				update_option( 'mapster_settings_page', $settings_page_id );
			}
	}

	/**
	 * Add default popup types
	 *
	 * @since    1.0.0
	 */
	function mapster_add_default_popups() {
		$default_popup = get_option('mapster_default_popup');
		if(!$default_popup || !get_post($default_popup)) {
			$simple_mapbox = wp_insert_post(array(
				'post_title' => "Default",
				'post_type' => "mapster-wp-popup",
				'post_status' => "publish"
			));
			mapster_setDefaults(acf_get_fields('group_6169ff23a6e6d'), $simple_mapbox);

			update_field('enable_header', false, $simple_mapbox);
			update_field('enable_image', false, $simple_mapbox);
			update_field('enable_footer', false, $simple_mapbox);
			update_field('close_button', true, $simple_mapbox);

			update_option( 'mapster_default_popup', $simple_mapbox );
		}
		$default_image = get_option('mapster_default_image_text');
		if(!$default_image || !get_post($default_image)) {
			$default_image_post = wp_insert_post(array(
				'post_title' => "Default Thumbnail",
				'post_type' => "mapster-wp-popup",
				'post_status' => "publish"
			));
			mapster_setDefaults(acf_get_fields('group_6169ff23a6e6d'), $default_image_post);

			update_field('enable_header', false, $default_image_post);
			update_field('image_height', 100, $default_image_post);
			update_field('max_width', 150, $default_image_post);
			update_field('enable_footer', false, $default_image_post);

			update_option( 'mapster_default_image_text', $default_image_post );
		}
		$default_header = get_option('mapster_default_header');
		if(!$default_header || !get_post($default_header)) {
			$default_header_post = wp_insert_post(array(
				'post_title' => "Default Header",
				'post_type' => "mapster-wp-popup",
				'post_status' => "publish"
			));
			mapster_setDefaults(acf_get_fields('group_6169ff23a6e6d'), $default_header_post);

			update_field('enable_image', false, $default_header_post);
			update_field('enable_body', false, $default_header_post);
			update_field('enable_footer', false, $default_header_post);

			update_option( 'mapster_default_header', $default_header_post );
		}
	}

	/**
	 * Add row action link
	 *
	 * @since    1.0.0
	 */
	public function mapster_wp_maps_row_action_menu($actions, $post)
	{
			if($post->post_type == 'mapster-wp-popup' || $post->post_type == 'mapster-wp-polygon' || $post->post_type == 'mapster-wp-location' || $post->post_type == 'mapster-wp-line' || $post->post_type == 'mapster-wp-map') {
				$actions['mapster-wp-maps-duplicate'] = '<a id="mapster-' . $post->ID . '" class="mapster-duplicate" href="#">Duplicate</a>';
			}
			return $actions;
	}

	/**
	 * Create backend menu
	 *
	 * @since    1.0.0
	 */
	public function mapster_wp_maps_settings_menu()
	{
			add_submenu_page('edit.php?post_type=mapster-wp-map', 'Categories', 'Categories', 'manage_options', 'edit-tags.php?taxonomy=wp-map-category&post_type=mapster-wp-map');
			add_submenu_page('edit.php?post_type=mapster-wp-map', 'Settings', 'Settings', 'manage_options', 'wordpress-maps-settings', function () {
					include 'partials/mapster-wordpress-maps-settings.php';
			});
	}

	/**
	 * Set ACF Form Head before headers in admin screen
	 *
	 * @since    1.0.0
	 */
	public function mapster_wp_maps_settings_form_init()
	{
	  	$current_get_vars = $_GET;
			if(isset($current_get_vars['post_type']) && isset($current_get_vars['page']) && $current_get_vars['post_type'] == 'mapster-wp-map' && $current_get_vars['page'] == 'wordpress-maps-settings') {
				acf_form_head();
			}
	}

	/**
	 * Welcome message
	 *
	 * @since    1.0.0
	 */
	function mapster_wp_maps_admin_notice() {
			if(!get_option( 'mapster_welcome_message' )) {
				update_option( 'mapster_welcome_message', true );
				?>
		    <div class="notice notice-success is-dismissible">
		      <img style="width: 50px;margin-top:10px;float:left;margin-right: 10px;" src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/logo-Mapster.png" />
					<h2>Thanks for installing Mapster Wordpress Maps!</h2>
					<p>You don't need to use a Mapbox Access Token to use the plugin, but more features are enabled when you do. It's up to you! If you want to, go to <a href="<?php echo get_admin_url(); ?>/edit.php?post_type=mapster-wp-map&page=wordpress-maps-settings">the settings page</a> to enter yours.</p>
					<p>If you're not sure what an Access Token is, we have lots of help available.</p>
					<p><a href="https://wpmaps.mapster.me/documentation" target="_blank">Plugin Documentation</a> | <a href="https://www.mapbox.com/account/access-tokens" target="_blank">Your Mapbox Account</a></p>
		    </div>
	    <?php }
	}

}
