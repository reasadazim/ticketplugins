<?php 
/**
 * EventCard Time html content
 * @4.6
 */

$iconTime = "<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_002', 'fa-clock-o',$evOPT )."'></i></span>";
						
// time for event card
$timezone = (!empty($object->timezone)? ' <em class="evo_eventcard_tiemzone">'. $object->timezone.'</em>':null);

// event time
$time_content = "<span class='evo_eventcard_time_t'>". apply_filters('evo_eventcard_time', $object->timetext. $timezone, $object) . "</span>";

// custom timezone text
if( !EVO()->cal->check_yn('evo_gmt_hide','evcal_1') && !empty($this->ev_tz) ){

	$GMT_text = $this->help->get_timezone_gmt( $this->ev_tz, $EVENT->start_unix);
	$time_content .= "<span class='evo_tz marr5'>(". $GMT_text .")</span>";
}							

// view in my time - local time
if( !empty($this->ev_tz) && EVO()->cal->check_yn('evo_show_localtime','evcal_1') ){

	extract( $this->timezone_data );
		
	$data = array(
		'__df'=> $__df,
		'__tf'=> $__tf,
		'__f'=> $__f,
		'times'=> $EVENT->start_unix . '-' . $EVENT->end_unix,
		'tzo' => $this->help->get_timezone_offset( $this->ev_tz,  $EVENT->start_unix)
	);
	
	//$time_content.= $this->get_view_my_time_content( $this->timezone_data , $EVENT->start_unix, $EVENT->end_unix);	

	$time_content .= "<div class='padt5'><span class='evo_btn_arr tzo_trig evo_mytime evo_hover_op6' {$this->help->array_to_html_data($data)}>". $__t  . "<i class='fa fa-chevron-right'></i></span></div>";	
}


echo "<div class='evo_metarow_time evorow evcal_evdata_row evcal_evrow_sm ".$end_row_class."'>
		{$iconTime}
		<div class='evcal_evdata_cell'>							
			<h3 class='evo_h3'>".$iconTime . evo_lang_get('evcal_lang_time','Time')."</h3><p>".$time_content."</p>
		</div>
	</div>";
