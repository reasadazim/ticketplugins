<?php
/**
 * Event Taxonomy Class 
 * @version 4.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

class EVO_Tax {

	public $term_id, $EVENT, $help;
	public function __construct(  $event_id = '', $ri = ''){
		$this->help = new evo_helper();
		$this->EVENT = !empty( $event_id ) ? new EVO_Event( $event_id , '', $ri ) : '';
	}

	// return lightbox taxnomy details html
		function get_lightbox_content( $post ){

			ob_start();

			$template_path = eventon_get_template_part( 'lb', $post['tax'] ,'', true );

			if( !file_exists( $template_path )) return false;

			$temp_data = $this->get_term_data( $post['tax'] , $post['term_id'] );

			require_once( $template_path );
			
			return ob_get_clean();

		}
	
	

	// get taxonomy image data if available
		public function process_tax_img_from_data( $tax_img_id ){
			$img_urls = array();
			// get loc img urls
			if( is_array( $tax_img_id ) ){
				foreach($tax_img_id as $index => $img_id){
					$URL = wp_get_attachment_image_src( $img_id,'full');
					$URL2 = wp_get_attachment_image_src( $img_id,'thumbnail');
					if( !empty( $URL ))	$img_urls[ $index ] = array( $URL[0] , $URL2[0] , $img_id );
				}	
			}else{
				$URL = wp_get_attachment_image_src($tax_img_id,'full');
				$URL2 = wp_get_attachment_image_src($tax_img_id,'thumbnail');
				if( !empty( $URL ))	$img_urls[ 1 ] = array( $URL[0] , $URL2[0] , $tax_img_id);
			}
			return $img_urls;
		}

	// event taxonomy data / @4.2
		function get_taxonomy_data( $tax, $term_id = false , $event_id = '', $load_meta_data = true ){
			
			// get terms
			if( empty( $event_id)){
				$terms = apply_filters('evodata_taxonomy_terms', get_terms(array('taxonomy'=>$tax) ), $tax, $term_id, $this );
			}else{
				$terms = apply_filters('evodata_taxonomy_terms', wp_get_post_terms($event_id, $tax), $tax, $term_id, $this );
			}
			
			if ( $terms && ! is_wp_error( $terms ) ){
				$R = array();				

				foreach($terms as $term){

					if( $term_id && $term->term_id != $term_id ) continue; 
					$R[ $tax ][ $term->term_id ] = $this->get_term_data( $tax, $term->term_id, $term , $load_meta_data);

				}				
				return $R;			

			}else{	return false;	}
		}

	// get any taxonomy term data including evo saved term meta from options @since 4.3 u4.6
		function get_term_data( $tax, $term_id, $term = '' , $load_meta_data = true){
			
			$term = (!empty( $term ) ) ? $term : get_term_by('term_id', $term_id, $tax);

			if ( !$term ) return false;


			// meta data
			if( $load_meta_data){
				$meta_key_array = $this->get_taxonomy_meta_array( $tax );
			}


			// if meta data key exists
			if( !empty( $meta_key_array ) && count($meta_key_array)>0){


				$termmeta = $this->get_term_meta( $tax, $term_id );

				foreach( $meta_key_array as $I=>$key){
					$K = is_integer($I)? $key: $I;				
					$term->$K = (empty($termmeta[$key]))? '': $termmeta[$key];
				}
			}

			// append descriptino 2 to full 
				$term->description_full = $term->description;
				if( !empty( $term->description2 )){
					$term->description_full .= '<div class="evo_sd">'. $term->description2 .'</div>';
				}

			// pass link 
				$term->link = get_term_link( $term , $tax);

				//print_r($term);

			return $term;
		}


	// taxonomy meta data array - 4.6
		function get_taxonomy_meta_array($tax){
			$meta_data = array();

			$meta_data['event_organizer'] = $this->get_organizer_social_meta_array();
			
			$meta_data['event_organizer']['img_id'] = 'evo_org_img';
			$meta_data['event_organizer']['organizer_img_id'] = 'evo_org_img';
			$meta_data['event_organizer']['organizer_contact'] = 'evcal_org_contact';
			$meta_data['event_organizer']['contact_email'] = 'evcal_org_contact_e';
			$meta_data['event_organizer']['organizer_address'] = 'evcal_org_address';
			$meta_data['event_organizer']['organizer_link'] = 'evcal_org_exlink';
			$meta_data['event_organizer']['organizer_link_target'] = '_evocal_org_exlink_target';
			$meta_data['event_organizer']['description2'] = 'description2';
			$meta_data['event_organizer']['org_rel'] = 'org_rel';

			$meta_data['event_location'] = $this->get_location_social_meta_array();
			$meta_data['event_location']['location_address'] = 'location_address';
			$meta_data['event_location']['location_lat'] = 'location_lat';
			$meta_data['event_location']['location_lon'] = 'location_lon';
			$meta_data['event_location']['location_img_id'] = 'evo_loc_img';
			$meta_data['event_location']['location_link'] = 'evcal_location_link';
			$meta_data['event_location']['location_city'] = 'location_city';
			$meta_data['event_location']['location_state'] = 'location_state';
			$meta_data['event_location']['location_country'] = 'location_country';
			$meta_data['event_location']['location_link_target'] = 'evcal_location_link_target';
			$meta_data['event_location']['location_getdir_latlng'] = 'location_getdir_latlng';
			$meta_data['event_location']['location_type'] = 'location_type';
			$meta_data['event_location']['loc_phone'] = 'loc_phone';
			$meta_data['event_location']['loc_email'] = 'loc_email';
			$meta_data['event_location']['loc_rel'] = 'loc_rel';

			$meta_data = apply_filters( 'evo_single_event_taxonomy_meta_array', $meta_data, $tax, $this);

			return isset($meta_data[ $tax ]) ? $meta_data[ $tax ]: false;
		}

	//  social media icons
		function get_social_sites(){
			return array(
				'twitter',
				'facebook',
				'instagram',
				'linkedin',
				'youtube',
				'whatsapp',
				'tiktok',
			);
		}
		function get_organizer_social_meta_array(){
			return apply_filters('evo_organizer_archive_page_social', array(
				'twitter'=>'evcal_org_tw',
				'instagram'=>'evcal_org_ig',
				'whatsapp'=>'evcal_org_wa',
				'tiktok'=>'evcal_org_tt',
				'facebook'=>'evcal_org_fb',
				'linkedin'=>'evcal_org_ln',
				'youtube'=>'evcal_org_yt'
			));
		}
		function get_location_social_meta_array(){
			return apply_filters('evo_location_archive_page_social', array(
				'twitter'=>'loc_tw',
				'instagram'=>'loc_ig',
				'facebook'=>'loc_fb',
				'youtube'=>'loc_yt'
			));
		}

	// supportive function
		function get_tax_names_list( $tax, $skip_ids = array(), $pre_vals = array() ){
			global $wpdb;

			$OUT = count($pre_vals) > 0 ? $pre_vals : array();

			$R = $wpdb->get_results( $wpdb->prepare(
				"SELECT t.term_id, tt.name
				FROM {$wpdb->prefix}term_taxonomy AS t
				INNER JOIN {$wpdb->prefix}terms AS tt ON (tt.term_id = t.term_id )
				WHERE t.taxonomy=%s", $tax
			));

			//print_r($skip_ids);

			if($R && count($R)>0){
				foreach($R as $C){
					// skip ids
					if( is_array($skip_ids) && count($skip_ids)>0 && in_array($C->term_id, $skip_ids)) continue;
					$OUT[ $C->term_id ] = $C->name;
				}
			}

			//print_r($OUT);

			return $OUT;
		}

	// get term meta data from options
		function get_term_meta( $tax , $term_id , $secondarycheck = false){
			$termmetas =  EVO()->calendar->get_tax_meta();

			//print_r($termmetas);

			if( empty($termmetas[$tax][$term_id])){
				if($secondarycheck){
					$secondarymetas = get_option( "taxonomy_".$term_id);
					return (!empty($secondarymetas)? $secondarymetas: false);
				}else{ return false;}
			} 
			return $termmetas[$tax][$term_id];
		}
	

}