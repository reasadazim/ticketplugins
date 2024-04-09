<?php 
/**
 * EventCard directions html content
 * @4.6
 */

$_lang_1 = evo_lang_get('evcalL_getdir_placeholder','Type your address to get directions');
$_lang_2 = evo_lang_get('evcalL_getdir_title','Click here to get directions');

echo "<div class='evo_metarow_getDr evorow evcal_evdata_row evcal_evrow_sm getdirections'>
		<form action='https://maps.google.com/maps' method='get' target='_blank'>
			<input type='hidden' name='daddr' value=\"{$_from_address}\"/> 
			<div class='evo_get_direction_content evo_fx_dr_r evogap10'>
				<span class='evogetdir_header evodfx evofx_dr_r evofx_ai_c marr10'>
					<i class='mainicon fa ".get_eventON_icon('evcal__fai_008a', 'fa-route',$evOPT )."'></i> 
					<h3 class='evo_h3' style='padding-bottom:5px;'>". evo_lang('Get Directions') ."</h3>
				</span>
				<span class='evogetdir_field evodfx evofx_1_1 evo_fx_dr_c evow100p'>					
					<input class='evoInput evotac' type='text' name='saddr' placeholder='{$_lang_1}' value='' style='margin:10px 0;'/>
				</span>
				<button type='submit' class='evo_get_direction_button evo_btn_arr_circ dfx fx_ai_c' title='{$_lang_2}'><i class='fa fa-chevron-right'></i> </button>
			</div>
		</form>
	</div>";