<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://mapster.me
 * @since      1.0.0
 *
 * @package    Mapster_Wordpress_Maps
 * @subpackage Mapster_Wordpress_Maps/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mapster_Wordpress_Maps
 * @subpackage Mapster_Wordpress_Maps/public
 * @author     Mapster Technology Inc <hello@mapster.me>
 */
class Mapster_Wordpress_Maps_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
			wp_register_style('mapster_map_mapbox_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/mapbox-gl-2.4.1.css", array(), $this->version);
			wp_register_style('mapster_map_maplibre_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/maplibre-1.15.2.css", array(), $this->version);
			wp_register_style('mapster_map_directions_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/directions.css", array(), $this->version);
			wp_register_style('mapster_map_geocoder_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/mapbox-gl-geocoder-4.7.2.css", array(), $this->version);
			wp_register_style('mapster_map_geosearch_css', plugin_dir_url( __FILE__ ) . "../admin/css/vendor/leaflet-geosearch-3.0.5.css", array(), $this->version);
			wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/css/mapster-wordpress-maps.css', array(), $this->version, 'all' );
			wp_register_style( 'mapster_map_public_css', plugin_dir_url( __FILE__ ) . 'css/mapster-wordpress-maps-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$settings_page_id = get_option('mapster_settings_page');
		$access_token = get_field('default_access_token', $settings_page_id);
		$injectedParams = array(
				'public' => true,
				'rest_url' => get_rest_url(),
				'mapbox_access_token' => $access_token
		);

		wp_register_script('mapster_map_mapbox', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-2.4.1.js", array('jquery'), $this->version);
		wp_register_script('mapster_map_mapbox_turf', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/turf-6.5.0.js", array('mapster_map_mapbox'), $this->version);
		wp_register_script('mapster_map_mapbox_directions_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-directions-4.1.0.js", array('mapster_map_mapbox_turf'), $this->version);
		wp_register_script('mapster_map_mapbox_geocoder_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-geocoder-4.7.2.js", array('mapster_map_mapbox_directions_js'), $this->version);
		wp_register_script( $this->plugin_name . "_mapbox", plugin_dir_url( __FILE__ ) . '../admin/js/dist/mwp.js', array('mapster_map_mapbox_geocoder_js'), $this->version, 'all' );
		wp_localize_script( $this->plugin_name . "_mapbox", 'params', $injectedParams);

		wp_register_script('mapster_map_maplibre', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/maplibre-1.15.2.js", array('jquery'), $this->version);
		wp_register_script('mapster_map_maplibre_turf', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/turf-6.5.0.js", array('mapster_map_maplibre'), $this->version);
		wp_register_script('mapster_map_maplibre_directions_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-directions-4.1.0.js", array('mapster_map_maplibre_turf'), $this->version);
		wp_register_script('mapster_map_maplibre_geocoder_js', plugin_dir_url( __FILE__ ) . "../admin/js/vendor/mapbox-gl-geocoder-4.7.2.js", array('mapster_map_maplibre_directions_js'), $this->version);
		wp_register_script( $this->plugin_name . "_maplibre", plugin_dir_url( __FILE__ ) . '../admin/js/dist/mwp.js', array('mapster_map_maplibre_geocoder_js'), $this->version, 'all' );
		wp_localize_script( $this->plugin_name . "_maplibre", 'params', $injectedParams);

	}

	/**
	 * Register shortcode
	 *
	 * @since    1.0.0
	 */
	public function mapster_wordpress_maps_register_shortcodes() {
		add_shortcode( 'mapster_wp_map', array( $this, 'mapster_wordpress_maps_shortcode_display') );
	}

	/**
	 * Add shortcode to Map type content
	 *
	 * @since    1.0.0
	 */
	public function mapster_wordpress_maps_output_shortcode( $content ) {
		if( is_singular('mapster-wp-map') ) {
			$output_shortcode = do_shortcode( '[mapster_wp_map id="' . get_the_ID() . '"]' );
			$output_shortcode .= $content;
			return $output_shortcode;
		} else {
			return $content;
		}
	}

	/**
	 * Map shortcode logic
	 *
	 * @since    1.0.0
	 */
	public function mapster_wordpress_maps_shortcode_display( $atts ){

		$map_provider = get_field('map_type', $atts['id'])['map_provider'];

		if($map_provider === 'maplibre') {
			wp_enqueue_style( "mapster_map_maplibre_css" );
			wp_enqueue_script( "mapster_map_maplibre" );
			wp_enqueue_script( "mapster_map_maplibre_directions_js" );
			wp_enqueue_script( "mapster_map_maplibre_geocoder_js" );
			wp_enqueue_script( $this->plugin_name . "_maplibre");
		}
		if($map_provider === 'mapbox') {
			wp_enqueue_style( "mapster_map_mapbox_css" );
			wp_enqueue_script( "mapster_map_mapbox" );
			wp_enqueue_script( "mapster_map_mapbox_directions_js" );
			wp_enqueue_script( "mapster_map_mapbox_geocoder_js" );
			wp_enqueue_script( $this->plugin_name . "_mapbox");
		}
		wp_enqueue_style( "mapster_map_directions_css" );
		wp_enqueue_style( "mapster_map_geocoder_css" );
		wp_enqueue_style( "mapster_map_geosearch_css" );
		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_style( "mapster_map_public_css" );



		return "<div><div class='mapster-wp-maps' data-id='" . $atts['id'] . "' id='mapster-wp-maps-" . $atts['id'] . "'></div></div>";
	}

}
