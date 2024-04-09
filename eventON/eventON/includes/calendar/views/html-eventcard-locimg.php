<?php 
/**
 * EventCard location image html content
 * @4.6
 */

$TAX = new EVO_Tax();
$img_urls = $TAX->process_tax_img_from_data( $location_img_id );

// if loc images exitsts
if( count($img_urls)> 0):

	$first_img_url = $img_urls[1];

	$fullheight = (int)EVO()->calendar->get_opt1_prop('evo_locimgheight',400);

	$cover_content = '';
	$more_btn_html = '';
	
	// text over location image
	$inside = $inner = '';
	if( $EVENT->check_yn('evcal_name_over_img') ){

		$cover_content .= ( !empty( $location_name ) ? "<h3 class='evo_h3'>{$location_name}</h3>" : '' );
		$cover_content .= (!empty($location_address) ? '<span style="padding-bottom:10px">'. stripslashes($location_address) .'</span>':'' );
		$cover_content .= (!empty($location_description) ? '<span class="location_description">'. $location_description .'</span>':'' );	

		if( !empty( $cover_content )){
			$more_btn_html = "<span class='evo_locimg_more evo_transit_all evo_trans_sc1_1'><i class='fa fa-plus'></i></span>";
		}	
	}
	

	$inside .= "
	<div class='evo_gal_main_img' style='background-image:url(". $first_img_url[0] ."); height:100%; width:100%'>
	<div class='evo_locimg_over'>
		<div class='evo_locimg_over_in'>{$cover_content}</div>
	</div>
	<div class='evo_gal_bottom evo_locimg_bottom '>
		<div class='evo_gal_icons evo_locimg_left'>";

		foreach( $img_urls as $index => $d){
			$inside .= "<div class='evo_gal_icon evo_locimg_gal ". ($index == 1 ? 'on':'') ." evo_transit_all evo_trans_sc1_05' data-index='{$index}' data-u='{$d[0]}'>
				<span class='locimg_1' style='background-image:url(".$d[1].")'></span>
			</div>";
		}
			
		$inside .= "</div>
		<div class='evo_locimg_right'>
			". ( !empty( $location_name ) ? "<h3 class='evo_h3 evo_locimg_title'>{$location_name}</h3>" : '' ) ."
			{$more_btn_html}
		</div>								
	</div>
	</div>
	";

	echo "<div class='evcal_evdata_row evo_metarow_locImg evorow evo_gal_box ".( !empty($inside)?'tvi':null)."' style='height:{$fullheight}px; padding:0'  id='". $first_img_url[2] ."_locimg' >{$inside}</div>";

endif;