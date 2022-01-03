<?php
  class Mapster_Wordpress_Maps_Admin_API {

    public function mapster_wp_maps_duplicate_post() {
        register_rest_route('mapster-wp-maps', 'duplicate', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_duplication',
            'permission_callback' => function(){
                return current_user_can( 'edit_posts' );
            },
        ));

        function mapster_wp_maps_duplication($request) {
          $body = $request->get_body();
          $decoded_body = json_decode($body);

          $post_id = $decoded_body->id;

          $old_post = get_post($post_id);
          $new_post = array(
        		'post_author'           => $old_post->post_author,
        		'post_content'          => $old_post->post_content,
        		'post_title'            => $old_post->post_title,
        		'post_excerpt'          => $old_post->post_excerpt,
        		'post_status'           => $old_post->post_status,
        		'comment_status'        => $old_post->comment_status,
        		'ping_status'           => $old_post->ping_status,
        		'post_password'         => $old_post->post_password,
        		'to_ping'               => $old_post->to_ping,
        		'pinged'                => $old_post->pinged,
        		'post_content_filtered' => $old_post->post_content_filtered,
        		'post_parent'           => $old_post->post_parent,
        		'menu_order'            => $old_post->menu_order,
        		'post_type'             => $old_post->post_type,
        		'post_mime_type'        => $old_post->post_mime_type
          );

          $new_post_id = wp_insert_post($new_post);
        	if ($new_post_id) {
        		$meta_data = get_post_meta($post_id);
        		foreach($meta_data as $meta_key => $meta_value) {
        			update_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value[0]));
        		}
        		return $new_post_id;
        	}

          return array(
            "new_post_id" => $new_post_id
          );
        }
    }

    public function mapster_wp_maps_import_features() {
        register_rest_route('mapster-wp-maps', 'import-geojson', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_import_geojson',
            'permission_callback' => function(){
                return current_user_can( 'edit_posts' );
            },
        ));

        function mapster_wp_maps_import_geojson($request) {

            $body = $request->get_body();
            $decoded_body = json_decode($body);

            $geojson = $decoded_body->file;
            $category_id = $decoded_body->category;

            $marker_count = 0;
            $poly_count = 0;
            $line_count = 0;

            foreach($geojson->features as $feature) {
              $feature_copy = clone $feature;
              $feature_geojson = array(
                "type" => "FeatureCollection",
                "features" => array($feature_copy)
              );

              if($feature->geometry->type == 'Point') {
                $marker_count = $marker_count + 1;
                $new_shape = wp_insert_post(array(
                  'post_type' => 'mapster-wp-location',
                  'post_status' => 'publish',
                  'post_title' => $feature->properties->name ? $feature->properties->name : $feature->geometry->type . ' ' . $marker_count
                ));
                if($category_id !== "") {
                  wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                }
                mapster_setDefaults(acf_get_fields('group_6163732e0426e'), $new_shape);
                mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);

                update_field('location', json_encode($feature_geojson), $new_shape);
              }
              if($feature->geometry->type == 'Polygon' || $feature->geometry->type == 'MultiPolygon' ) {
                $poly_count = $poly_count + 1;
                $new_shape = wp_insert_post(array(
                  'post_type' => 'mapster-wp-polygon',
                  'post_status' => 'publish',
                  'post_title' => $feature->properties->name ? $feature->properties->name : $feature->geometry->type . ' ' . $poly_count
                ));
                if($category_id !== "") {
                  wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                }
                mapster_setDefaults(acf_get_fields('group_616379566202f'), $new_shape);
                mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);

                update_field('polygon', json_encode($feature_geojson), $new_shape);
              }
              if($feature->geometry->type == 'LineString' || $feature->geometry->type == 'MultiLineString' ) {
                $line_count = $line_count + 1;
                $new_shape = wp_insert_post(array(
                  'post_type' => 'mapster-wp-line',
                  'post_status' => 'publish',
                  'post_title' => $feature->properties->name ? $feature->properties->name : $feature->geometry->type . ' ' . $line_count
                ));
                if($category_id !== "") {
                  wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                }
                mapster_setDefaults(acf_get_fields('group_616377d62836b'), $new_shape);
                mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);

                update_field('line', json_encode($feature_geojson), $new_shape);
              }

            }

            ob_get_clean();

            return array(
              "count" => $new_shape
            );
        }

        register_rest_route('mapster-wp-maps', 'import-gl-js', array(
            'methods'   => 'POST',
            'callback'  => 'mapster_wp_maps_import_gl_js',
            'permission_callback' => function(){
                return current_user_can( 'edit_posts' );
            },
        ));

        function mapster_wp_maps_import_gl_js($request) {

            $body = $request->get_body();
            $decoded_body = json_decode($body);

            $geojson = $decoded_body->file;
            $category_id = $decoded_body->category;

            $marker_count = 0;
            $poly_count = 0;
            $line_count = 0;

            $uploaded_images = array();
            $uploaded_images_new = array();

            foreach($geojson->features as $feature) {
              $feature_copy = clone $feature;
              $feature_copy->properties = new stdClass();
              $geojson = array(
                "type" => "FeatureCollection",
                "features" => array($feature_copy)
              );

              if($feature->geometry->type == 'Point') {
                $marker_count = $marker_count + 1;
                $new_shape = wp_insert_post(array(
                  'post_type' => 'mapster-wp-location',
                  'post_status' => 'publish',
                  'post_title' => $feature->properties->name !== '' ? $feature->properties->name : $feature->properties->marker_title . ' ' . $marker_count
                ));
                if($category_id !== "") {
                  wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                }
                mapster_setDefaults(acf_get_fields('group_6163732e0426e'), $new_shape);
                mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);

                update_field('location_style', 'label', $new_shape);
                update_field('icon_icon_on', true, $new_shape);
                update_field('icon_icon_properties_icon-anchor', $feature->properties->marker_icon_anchor, $new_shape);
                update_field('icon_icon_properties_icon-size', 30, $new_shape);
                update_field('enable_popup', true, $new_shape);
                update_field('popup_style', get_option('mapster_default_popup'), $new_shape);
                update_field('popup_body_text', $feature->properties->description, $new_shape);

                // Upload marker image
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $filename = explode('-wp_mapbox_gl_js_sizing', $feature->properties->marker_icon_url)[0];
                if(!in_array($filename, $uploaded_images)) {
                  $attachment_id = media_sideload_image( $filename, 0, null, 'id' );
                  array_push($uploaded_images, $filename);
                  array_push($uploaded_images_new, $attachment_id);
                  update_field('icon_icon_properties_icon-image', $attachment_id, $new_shape);
                } else {
                  $index = array_search($filename, $uploaded_images);
                  update_field('icon_icon_properties_icon-image', $uploaded_images_new[$index], $new_shape);
                }

                update_field('location', json_encode($geojson), $new_shape);
              }
              if($feature->geometry->type == 'Polygon' || $feature->geometry->type == 'MultiPolygon' ) {
                $poly_count = $poly_count + 1;
                $new_shape = wp_insert_post(array(
                  'post_type' => 'mapster-wp-polygon',
                  'post_status' => 'publish',
                  'post_title' => $feature->properties->name !== '' ? $feature->properties->name : $feature->properties->marker_title . ' ' . $poly_count
                ));
                if($category_id !== "") {
                  wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                }
                mapster_setDefaults(acf_get_fields('group_616379566202f'), $new_shape);
                mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);

                update_field('color', $feature->properties->color, $new_shape);
                update_field('opacity', $feature->properties->opacity * 100, $new_shape);

                update_field('polygon', json_encode($geojson), $new_shape);
              }
              if($feature->geometry->type == 'LineString' || $feature->geometry->type == 'MultiLineString' ) {
                $line_count = $line_count + 1;
                $new_shape = wp_insert_post(array(
                  'post_type' => 'mapster-wp-line',
                  'post_status' => 'publish',
                  'post_title' => $feature->properties->name !== '' ? $feature->properties->name : $feature->properties->marker_title . ' ' . $line_count
                ));
                if($category_id !== "") {
                  wp_set_post_terms($new_shape, array($category_id), 'wp-map-category');
                }
                mapster_setDefaults(acf_get_fields('group_616377d62836b'), $new_shape);
                mapster_setDefaults(acf_get_fields('group_6163d357655f4'), $new_shape);

                update_field('color', $feature->properties->color, $new_shape);
                update_field('opacity', $feature->properties->opacity * 100, $new_shape);

                update_field('line', json_encode($geojson), $new_shape);
              }

            }

            ob_get_clean();

            return array(
              "count" => $line_count + $poly_count + $marker_count
            );
        }
    }

    public function mapster_wp_maps_get_category_features() {
        register_rest_route('mapster-wp-maps', 'category', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_get_category',
            'permission_callback' => function(){
                return true; // open to public
            },
        ));

        function mapster_wp_maps_get_category($params) {

            $response = array();
            $id = json_decode($params['id']);
            $args = array(
              'tax_query' => array(
                array(
                  "taxonomy" => "wp-map-category",
                  "field" => "term_id",
                  "terms" => $id,
                  "include_children" => false
                )
              ),
              'posts_per_page' => -1
            );
            $the_query = new WP_Query( $args );
            if ( $the_query->have_posts() ) :
              while ( $the_query->have_posts() ) : $the_query->the_post();
                $thisResponse = mapster_getOnlyValues(get_the_ID());
                array_push($response, $thisResponse);
              endwhile;
            endif;

            ob_get_clean();

            return $response;
        }
    }

    public function mapster_wp_maps_get_all_features() {
        register_rest_route('mapster-wp-maps', 'features', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_get_features',
            'permission_callback' => function(){
                return true; // open to public
            },
        ));

        function mapster_wp_maps_get_features($params) {

            $response = array();
            $idsArray = json_decode($params['ids']);
            $catsArray = json_decode($params['categories']);
            foreach($idsArray as $id) {
              $thisResponse = mapster_getOnlyValues($id);
              array_push($response, $thisResponse);
            }
            // Check for category additions
            if(count($catsArray) > 0) {
              $args = array(
                'tax_query' => array(
                  array(
                    "taxonomy" => "wp-map-category",
                    "field" => "term_id",
                    "terms" => $catsArray,
                    "include_children" => false
                  )
                ),
                'posts_per_page' => -1
              );
              $the_query = new WP_Query( $args );
              if ( $the_query->have_posts() ) :
                while ( $the_query->have_posts() ) : $the_query->the_post();
                  $thisResponse = mapster_getOnlyValues(get_the_ID());
                  array_push($response, $thisResponse);
                endwhile;
              endif;
            }

            ob_get_clean();

            return $response;
        }
    }

    public function mapster_wp_maps_get_single_feature() {
        register_rest_route('mapster-wp-maps', 'feature', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_get_feature',
            'permission_callback' => function(){
                return true; // open to public
            },
        ));

        function mapster_wp_maps_get_feature($params) {

            $post_id = intval($params['id']);
            $thisResponse = mapster_getOnlyValues($post_id);

            ob_get_clean();

            return $thisResponse;
        }
    }

    public function mapster_wp_maps_get_map() {
        register_rest_route('mapster-wp-maps', 'map', array(
            'methods'   => 'GET',
            'callback'  => 'mapster_wp_maps_get_single_map',
            'permission_callback' => function(){
                return true; // open to public
            },
        ));

        function mapster_wp_maps_get_single_map($params) {

            $post_id = intval($params['id']);
            $acf_data = get_field_objects($post_id);
            $minimized_data = array();
            foreach($acf_data as $key=>$data) {
              $minimized_data[$key] = $data['value'];
            }
            // Normal feature additions
            $minimized_location_data = array();
            foreach($minimized_data['locations'] as $location) {
              array_push($minimized_location_data, mapster_getOnlyValues($location->ID));
            }
            $minimized_line_data = array();
            foreach($minimized_data['lines'] as $line) {
              array_push($minimized_line_data, mapster_getOnlyValues($line->ID));
            }

            $minimized_polygon_data = array();
            foreach($minimized_data['polygons'] as $polygon) {
              array_push($minimized_polygon_data, mapster_getOnlyValues($polygon->ID));
            }
            // Check for category additions
            $categories = get_field('add_by_category', $post_id);
            if(count($categories) > 0) {
              $args = array(
                'tax_query' => array(
                  array(
                    "taxonomy" => "wp-map-category",
                    "field" => "term_id",
                    "terms" => $categories,
                    "include_children" => false
                  )
                ),
                'posts_per_page' => -1
              );
              $the_query = new WP_Query( $args );
              if ( $the_query->have_posts() ) :
                while ( $the_query->have_posts() ) : $the_query->the_post();
                  if(get_post_type() == 'mapster-wp-location') {
                    array_push($minimized_location_data, mapster_getOnlyValues(get_the_ID()));
                  }
                  if(get_post_type() == 'mapster-wp-line') {
                    array_push($minimized_line_data, mapster_getOnlyValues(get_the_ID()));
                  }
                  if(get_post_type() == 'mapster-wp-polygon') {
                    array_push($minimized_polygon_data, mapster_getOnlyValues(get_the_ID()));
                  }
                endwhile;
              endif;
            }

            unset($minimized_data['locations']);
            unset($minimized_data['lines']);
            unset($minimized_data['polygons']);

            ob_get_clean();

            return array(
              'id' => $post_id,
              'cats' => $categories,
              'map' => mapster_remakeUsingTemplate($minimized_data, 'map'),
              'locations' => mapster_remakeUsingTemplate($minimized_location_data, 'location'),
              'lines' => mapster_remakeUsingTemplate($minimized_line_data, 'line'),
              'polygons' => mapster_remakeUsingTemplate($minimized_polygon_data, 'polygon')
            );
        }
    }
}

function mapster_setGroup($field, $sub_fields, $post_id) {
  $array_to_add = array();
  foreach($sub_fields as $sub_field) {
    if(isset($sub_field['default_value'])) {
      $array_to_add[$sub_field['name']] = $sub_field['default_value'];
      update_field($field['name'], $array_to_add, $post_id);
    }
    if($sub_field['type'] == 'group') {
      mapster_setGroup($sub_field, $sub_field['sub_fields'], $post_id);
    }
  }
}

function mapster_setDefaults($all_fields, $post_id) {
  $field_names = array();
  foreach($all_fields as $field) {
    array_push($field_names, $field);
    if(isset($field['default_value'])) {
      update_field($field['name'], $field['default_value'], $post_id);
    }
    if($field['type'] == 'group') {
      mapster_setGroup($field, $field['sub_fields'], $post_id);
    }
  }
  return $field_names;
}
// Using default ACF values to make the object
// Therefore not worrying about undefined values that are newly added
function mapster_remakeUsingTemplate($data, $type) {
  $toReturn = array();
  $template = mapster_getTemplate($type);
  if($type == 'map') {
    $toReturn = mapster_replaceValueOrNot($template, $data);
  } else {
    foreach($data as $feature) {
      array_push($toReturn, mapster_replaceValueOrNot($template, $feature));
    }
  }
  return $toReturn;
}

function mapster_replaceValueOrNot($template, $data) {
  $toReturn = array();
  foreach($template as $key=>$field) {
    if(!isset($data[$key])) {
      $toReturn[$key] = $field;
    } else {
      if(is_array($field)) {
        $toReturn[$key] = mapster_replaceValueOrNot($field, $data[$key]);
      } else {
        $toReturn[$key] = $data[$key];
      }
    }
  }
  foreach($data as $key=>$dataPiece) {
    if(!isset($toReturn[$key])) {
      $toReturn['custom_fields'][$key] = $dataPiece;
    }
  }
  return $toReturn;
}

function mapster_getTemplate($type) {
  if($type == 'map') {
    return mapster_arrange_fields(acf_get_fields('group_61636c62b003e'), false);
  } elseif($type == 'line') {
    return mapster_arrange_fields(acf_get_fields('group_616377d62836b'), true);
  } elseif($type == 'location') {
    return mapster_arrange_fields(acf_get_fields('group_6163732e0426e'), true);
  } elseif($type == 'polygon') {
    return mapster_arrange_fields(acf_get_fields('group_616379566202f'), true);
  }
}

// Organizing responses to have minimal output
function mapster_returnPopupData($popup_id) {
  $single_popup_style_data = array();
  $popup_style_data = get_field_objects($popup_id);
  foreach($popup_style_data as $key=>$data) {
    $single_popup_style_data[$key] = $data['value'];
  }
  $single_popup_style_data['id'] = $popup_id;
  return $single_popup_style_data;
}

function mapster_getTermList($object_id) {
  $terms = get_the_terms($object_id, 'wp-map-category');
  $termsToReturn = array();
  foreach($terms as $term) {
    $thisTerm = array(
      "id" => $term->term_id,
      "name" => $term->name,
      "color" => get_field("color", 'wp-map-category_' . $term->term_id),
      "icon" => get_field("icon", 'wp-map-category_' . $term->term_id),
      "parent" => $term->parent
    );
    array_push($termsToReturn, $thisTerm);
  }
  return $termsToReturn;
}

function mapster_getOnlyValues($object_id) {
  $field_object_data = get_field_objects($object_id);
  $single_feature_data = array(
    "id" => $object_id,
    "permalink" => get_permalink($object_id),
    "title" => get_the_title($object_id),
    "content" => get_the_content($object_id),
    "categories" => mapster_getTermList($object_id),
    "data" => $field_object_data
  );

  foreach($field_object_data as $key=>$data) {
    $single_feature_data['data'][$key] = $data['value'];

    if($key == 'popup_style') {
      if($single_feature_data['data'][$key]->ID) {
        $single_feature_data['data'][$key] = mapster_returnPopupData($single_feature_data['data'][$key]->ID);
      }
    }

    if($key == 'popup') {
      $single_feature_data['data'][$key]['permalink'] = get_permalink($object_id);
      if($single_feature_data['data'][$key]['featured_image']) {
        $newImageData = array();
        $newImageData['id'] = $single_feature_data['data'][$key]['featured_image']['id'];
        $newImageData['url'] = $single_feature_data['data'][$key]['featured_image']['url'];
        $single_feature_data['data'][$key]['featured_image'] = $newImageData;
      }
    }

    if($key === 'icon') {
      $newImageData = array();
      $newImageData['id'] = $single_feature_data['data'][$key]['icon_properties']['icon-image']['id'];
      $newImageData['url'] = $single_feature_data['data'][$key]['icon_properties']['icon-image']['url'];
      $single_feature_data['data'][$key]['icon_properties']['icon-image'] = $newImageData;
    }
  }
  return $single_feature_data;
}

// Organizing template fields
function mapster_arrange_fields($field_group, $isFeature) {
  $toReturn = array();
  if($isFeature) {
    $toReturn['permalink'] = false;
    $toReturn['title'] = false;
    $toReturn['content'] = false;
    $toReturn['categories'] = false;
    $toReturn['id'] = false;
    $toReturn['data'] = false;
    foreach($field_group as $field) {
      if($field['name'] !== "") {
        $toReturn['data'][$field['name']] = mapster_arrange_sub_fields($field);
      }
    }
    // Get popup stuff too
    $popup_fields = acf_get_fields('group_6163d357655f4');
    foreach($popup_fields as $field) {
      $toReturn['data'][$field['name']] = mapster_arrange_sub_fields($field);
    }
    $toReturn['data']['popup']['permalink'] = false;
  } else {
    foreach($field_group as $field) {
      if($field['name'] !== "") {
        $toReturn[$field['name']] = mapster_arrange_sub_fields($field);
      }
    }
  }
  return $toReturn;
}

function mapster_arrange_sub_fields($field) {
  $toReturn = array();
  if(isset($field['sub_fields'])) {
    foreach($field['sub_fields'] as $sub_field) {
      $toReturn[$sub_field['name']] = mapster_arrange_sub_fields($sub_field);
    }
    return $toReturn;
  } else {
    // Handler for true/false
    if($field['type'] == 'true_false') {
      return $field['default_value'] == 0 ? false : true;
    } else {
      if(!isset($field['default_value'])) {
        return null;
      } else {
        return $field['default_value'];
      }
    }
  }
}

?>
