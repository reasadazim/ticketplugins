<?php
/**
 * Google Maps
 * @version 4.6
 */

// since 4.5
$is_google_map_good = true;

if( !EVO()->cal->get_prop('evo_gmap_api_key','evcal_1')) $is_google_map_good = false;

$location_type = isset( $EventData['location_type'] ) ? $EventData['location_type'] : '';

if( $location_type == 'virtual' ) $is_google_map_good = false;


if( empty( $EventData['location_address'] ) && empty( $EventData['location_lat'] ) ) $is_google_map_good = false;

//print_r($EventData);

// maps are good to go
if( $is_google_map_good ):
	$map_data = array(
		'address'=> stripslashes( $location_address ),
		'latlng'=> (!empty($location_lat) ? $location_lat.','.$location_lon : null),
		'location_type'=> $location_type,		
		'scroll'=> EVO()->cal->check_yn('evcal_gmap_scroll')? 'no':'yes',
		'mty'=> ( EVO()->cal->get_prop('evcal_gmap_format') ? EVO()->cal->get_prop('evcal_gmap_format'): 'roadmap'),
		'zoom'=> ( EVO()->cal->get_prop('evcal_gmap_zoomlevel') ? EVO()->cal->get_prop('evcal_gmap_zoomlevel'): '12'),
		'mapIcon'=> ( EVO()->cal->get_prop('evo_gmap_iconurl') ? EVO()->cal->get_prop('evo_gmap_iconurl'): ''),
		'delay'=>400,
		'map_canvas_id'=> $object->id."_gmap",
	);

	echo "<div class='evo_metarow_gmap evcal_evdata_row evorow evcal_gmaps ".$object->id."_gmap {$location_type}' id='".$object->id."_gmap' style='max-width:none' ". $this->helper->array_to_html_data( $map_data ) .">". EVO()->elements->get_preload_map() ."</div>";

endif;