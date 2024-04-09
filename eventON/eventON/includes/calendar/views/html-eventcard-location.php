<?php 
/**
 * EventCard location html content
 * @4.6.1
 */

$iconLoc = "<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_003', 'fa-map-marker',$evOPT )."'></i></span>";
						
if(!empty($location_name) || !empty($location_address)){
	
	$locationLink = (!empty($location_link))? '<a target="'. ($location_link_target=='yes'? '_blank':'') .'" href="'. evo_format_link($location_link).'">':false;

	
	$btn_data = array(
		'lbvals'=> array(
			'lbc'=>'evo_location_lb_'.$location_term_id,
			'lbac'=>'lb_max',
			't'=>	$location_name,
			'ajax'=>'yes',
			'ajax_type'=>'endpoint',
			'ajax_action'=>'eventon_get_tax_card_content',
			'end'=>'client',
			'd'=> array(					
				'term_id'=> $location_term_id,
				'tax'=>'event_location',
				'load_lbcontent'=>true
			)
		)
	);
	


	//$loc_more = "<span class='evo_expand_more_btn evo_trans_sc1_1 sm marl10' style='margin-top: -5px;'><i class='fa fa-plus'></i></span>";

	$loc_more_1 = "<div class='padt10'><span class='evo_btn_arr evolb_trigger' {$this->help->array_to_html_data($btn_data)}>". evo_lang('Other Events')  . "<i class='fa fa-chevron-right'></i></span></div>";
	
	$OT = 
	"<div class='evcal_evdata_row evo_metarow_time_location evorow '>
		
			{$iconLoc}
			<div class='evcal_evdata_cell' data-loc_tax_id='{$EventData['location_term_id']}'>";

			// if location information is hidden
			if( $location_hide){
				$OT.= "<h3 class='evo_h3'>".$iconLoc. evo_lang_get('evcal_lang_location','Location'). "</h3>";
				$OT .= "<p class='evo_location_name'>". EVO()->calendar->helper->get_field_login_message() . "</p>";
			
			}else{
				
				$OT.= "<h3 class='evo_h3 evodfx'>".$iconLoc.($locationLink? $locationLink:''). evo_lang_get('evcal_lang_location','Location').($locationLink?'</a>':'')."</h3>";

				if( !empty($location_name) && !$EVENT->check_yn('evcal_hide_locname') )
					$OT.= "<p class='evo_location_name'>". $locationLink. $location_name . ($locationLink? '</a>':'') ."</p>";



				// for virtual location
				if( $location_type == 'virtual'){
					if( $locationLink) 
						$OT.= "<p class='evo_virtual_location_url'>" . evo_lang('URL:'). $locationLink . ' '. $location_link."</a></p>";
				}else{

					if(!empty($location_address) && $location_address != $location_name ){
						$OT .= "<p class='evo_location_address'>". $locationLink . stripslashes($location_address) . ($locationLink? '</a>':'') ."</p>";
					}
					
				}	

				// location other events button
				if( !EVO()->cal->check_yn('evo_card_loc_btn') ) $OT.= $loc_more_1;										
			}
			$OT.= "</div>
		
	</div>";
}

echo  $OT;