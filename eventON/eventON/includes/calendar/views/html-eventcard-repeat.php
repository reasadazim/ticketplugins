<?php 
/**
 * EventCard Related Events html content
 * @4.6.2
 */


$UX = $EVENT->get_prop('_evcal_rep_series_ux');
if( !$UX) $UX = 'def';

$_clickable = $EVENT->check_yn('_evcal_rep_series_clickable');

echo "<div class='evo_metarow_repeats evorow evcal_evdata_row evcal_evrow_sm ".$end_row_class."'>
		<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_repeats', 'fa-repeat',$evOPT )."'></i></span>
		<div class='evcal_evdata_cell'>							
			<h3 class='evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_lang_repeats','Future Event Times in this Repeating Event Series')."</h3>";

			echo EVO()->elements->get_element(array(
				'type'=>'start_hor_content_slider',
				'row_class'=>'evo_repeat_series'
			));

			echo "<div class='evo_repeat_series_dates ".($_clickable?'clickable':'')." evodfx evofx_dr_r evogap10' data-click='".$_clickable."' data-event_url='".$object->event_permalink."'>";


	// allow for custom date time format passing to repeat event times
	$repeat_start_time_format = apply_filters('evo_eventcard_repeatseries_start_dtformat','');
	$repeat_end_time_format = apply_filters('evo_eventcard_repeatseries_end_dtformat','');

	// sort the repeats by event date @4.5.3
	asort($object->future_intervals);

	$DD = new DateTime('now', $EVENT->tz );
	$thisyear = $DD->format('Y');

	// get custom date time formats
	extract( EVO()->calendar->get_date_time_format() );

	$DT_format = 'YFj'. $date_format . $time_format;

	$show_month = $show_date = $show_time = true;

	// for each repeat in the series
	foreach($object->future_intervals as $key=>$interval){

		$_ads = '';
		$_class = '';
		$btn_data = array();

		// if open repeat as lightbox
			if( $_clickable && $UX == 'lb'){
				$btn_data = array(	
					'repeat'=> $key,
					'sc'=> array(
						'ev_uxval'=> '3a',
						'repeat_interval'=> $key,
						'event_id'=> $EVENT->ID,
						'eventtop_style'=> str_replace('_','',EVO()->cal->get_prop('evo_eventtop_style_def')),
					)
				);
				
				$_class .= ' eventon_anywhere evoajax';			
			}
			$btn_data['ux'] = $UX;
			$btn_data['l'] =  $EVENT->get_permalink($key,$EVENT->l);

		$_ads .= $this->help->array_to_html_data($btn_data);

		echo "<span class='evo_repeat_series_date evodfx evofx_dr_r evofx_ai_c evogap5 evobr15 evobrdB1 evocl1 evopad15 evottu evofz14 ". ( $object->clickable ? 'evocurp evo_trans_sc1_03 evoHbgcw':'') ."{$_class}' {$_ads}>"; 
		echo "<span class='evodfx evofx_dr_c evotac evofx_jc_c'>";
			
		
		// based on month year long calculate needed date format
			if($EVENT->is_year_long()){
				$DT_format = 'Y';
				$show_month = $show_date = $show_time = false;
			}elseif( $EVENT->is_month_long()){

				$DT_format = 'FY';
				$show_date = $show_time = false;

			}elseif( $EVENT->is_all_day()){

				$DT_format = 'jFY';
				$show_time = false;
				
			}else{}

		$ES = $EVENT->get_translated_datetime( $DT_format , $EVENT->__get_tz_based_unix( $interval[0] ) );
		$ES_tf = $EVENT->get_translated_datetime( $time_format , $EVENT->__get_tz_based_unix( $interval[0] ), false );

		$O1 = '';

		if( $show_date) $O1 .= "<b class='j evofz16'>{$ES['j']}</b>";
		$O1 .= "<b class='fy'>{$ES['F']} ". ( $ES['Y'] != $thisyear ? ' '. $ES['Y']:'') ."</b>";
		if( $show_time ) $O1 .= "<em class='t evofsn'>{$ES_tf}</em>";
		
		// if show end date/time
		if( $object->showendtime && !empty($interval[1])){

			$EE = $EVENT->get_translated_datetime( $DT_format , $EVENT->__get_tz_based_unix( $interval[1])  );
			$EE_tf = $EVENT->get_translated_datetime( $time_format , $EVENT->__get_tz_based_unix( $interval[1])  , false);

			$show = true;
			// same month
			if( isset($EE['F']) && $EE['F'] == $ES['F']){
				// same date
				if( isset( $EE['j'] ) && $EE['j'] == $ES['j']){
					if( $show_date) echo "<b class='j evofz16'>{$ES['j']}</b>";
					echo "<b class='fy'>". ( $show_month ? $ES['F'] :'' )." ". ( $ES['Y'] != $thisyear ? ' '. $ES['Y']:'') ."</b>";
					
					if( $show_time ) 
						echo "<em class='t evofsn'>{$ES_tf} - {$EE_tf}</em>";

					$show = false;
				}
			}

			// show end date/time
			if( $show){
				echo $O1;
				echo "</span>";
				echo "<i class='fa fa-minus'></i>";
				echo "<span class='evodfx evofx_dr_c evotac evofx_jc_c'>";
				if( $show_date) echo "<b class='j evofz16'>{$EE['j']}</b>";
				echo "<b class='fy'>". ( $show_month ? $EE['F'] :'' )." ". ( $EE['Y'] != $thisyear ? ' '. $EE['Y']:'') ."</b>";
				if( $show_time ) echo "<em class='t evofsn'>{$EE_tf}</em>";
			}
			
			
		}else{
			echo $O1;
		}
		
		
		echo "</span>";
		echo "</span>";
	}

echo "</div>";

echo EVO()->elements->get_element(array('type'=>'end_hor_content_slider'));

echo "</div></div>";


?>
