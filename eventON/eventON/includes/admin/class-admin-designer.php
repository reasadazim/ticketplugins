<?php
/**
 * Various Interactive Designer class
 * @version   4.6
 */

class EVO_Desginer{
	function get_eventcard_designer(){
		ob_start();

		$cal_help = new evo_cal_help();

		$event_card_fields = $cal_help->get_eventcard_fields(true);

		$all_fields = array();
		foreach($event_card_fields as $FK=>$FF){
			if( empty($FF)) continue;
			$all_fields[] = $FK;
		}

		// variable to save this data -- evo_ecl - eventcard layout
		$evo_ecl = $cal_help->get_eventcard_structure_array( );

		// preprocess legacy settings to new version
		if( empty($evo_ecl)){

			$fields = $hidden_items = array();

			// previous saved event order
			$eventcard_order = EVO()->cal->get_prop('evoCard_order','evcal_1');

			if( $eventcard_order){
				$old_order = $eventcard_order;

				$hidden_card_items = EVO()->cal->get_prop('evoCard_hide','evcal_1');
				
				$fields = explode(',', $old_order);
				$hidden_items = $hidden_card_items ? $hidden_card_items : '';
				$hidden_items = explode(',', $hidden_items);
			}else{
				$fields = $all_fields;
			}

			$count = 1;
			foreach($fields as $ii){

				if( empty($ii)) continue;
				if( in_array($ii, array('time','location','learnmore','learnmore'))) continue;

				if( $ii == 'timelocation'){
					$evo_ecl[ $count][1] = array(
						'n' =>'time','h'=> (in_array($ii, $hidden_items) ? 'y': ''),
					);
					$evo_ecl[ $count][2] = array(
						'n' =>'location','h'=> (in_array($ii, $hidden_items) ? 'y': ''),
					);
					$count++; continue;
				}

				if( $ii == 'learnmoreICS'){
					$evo_ecl[ $count][1] = array(
						'n' =>'learnmore','h'=> (in_array($ii, $hidden_items) ? 'y': ''),
					);
					$evo_ecl[ $count][2] = array(
						'n' =>'addtocal','h'=> (in_array($ii, $hidden_items) ? 'y': ''),
					);
					$count++; continue;
				}

				$evo_ecl[ $count][1] = array(
					'n' =>$ii,'h'=> (in_array($ii, $hidden_items) ? 'y': ''),
				);
				$count++;
			}
		}

		$default_evc_color = EVO()->cal->get_prop('evcal__bc1in','evcal_1');
		if( !$default_evc_color) $default_evc_color = 'f3f3f3';

		$processed_fields = array();

		?>
		<form class='evo_eventcard_designer_form'>
			<?php wp_nonce_field( 'evo_evard_save', 'evo_noncename' );?>
			<input type='hidden' name='action' value='eventon_save_eventcard_designer'>
		
		<div class='evo_card_designer evoposr' data-dc='<?php echo $default_evc_color;?>'>
			<div class='evocard_design_holder'>
				<?php 

				$used_fields = array();

				//print_r($evo_ecl);

				foreach($evo_ecl as $R=>$boxes){

					$L = ( array_key_exists('L1', $boxes)) ? ' L':'';
					$CC = '';

					//echo count($boxes);
					
					foreach($boxes as $B=>$DD){
						if( !isset($DD['n'])) continue;
						$N = $DD['n'];

						if( in_array($N, $all_fields)) $used_fields[] = $N;
						
						$H = isset($DD['h']) ? $DD['h']:'';
						$C = !empty($DD['c']) ? $DD['c']:$default_evc_color;

						if( !isset( $event_card_fields[$N][1] )) continue;

						// if already processed
						if( in_array( $N, $processed_fields)) continue;
						$processed_fields[] = $N;
						

						// stacked boxes begin container
						if( $B == 'L1' || $B == 'R1') $CC .= "<span class='ecd_row_box_h'>";

						// if display the color reset button
						$clr_reset = $default_evc_color == $C ? ' dn':'';
						
						$name = $event_card_fields[$N][1];
						$CC .= "<span class='ecd_row_box". ($H ? ' hidden':'') ."' data-b='{$B}' data-n='{$N}' data-h='{$H}' data-c='{$C}'> 
							<span class='ecd_act1'>
								<i class='vis fa fa-eye". ($H ? '-slash':'') ."'></i>
								<span class='colorselector clr' hex='{$C}' style='background-color:#{$C}' title='". __('Field Color','eventon')."'></span>
								<span class='clr_reset{$clr_reset}' data-hex='{$default_evc_color}' style='background-color:#{$default_evc_color}' title='". __('Reset to Default Color','eventon')."'></span>
							</span>
							<em>{$name}</em>
							<span class='ecdad_act'><i class='fa fa-minus-circle'></i></span>
							</span>";

						// stacked boxes close container
						if( count($boxes) == 3 && ( $B == 'L2' || $B == 'R2')) $CC .= "</span>";
						if( count($boxes) == 4 && ( $B == 'L3' || $B == 'R3')) $CC .= "</span>";
					}

					if( empty($CC)) continue;

					echo "<p class='ecd_row{$L}' data-r='{$R}'><span class='ecd_row_in'>".$CC."</span><i class='fa fa-minus-circle ecd_del_row'></i></p>";
				}	

				$unused_fields = array_diff($all_fields , $used_fields);

				?>

			</div>
			<input type='hidden' id='evo_card_fields' value='<?php echo json_encode($evo_ecl);?>' name='evo_ecl'/>
			
			<p id='ecd_adding_buttons'>
				<b>+ Add a row</b>
				<span class='ecd_add_rows full' data-c='1'><b></b></span> 
				<span class='ecd_add_rows half' data-c='2'><b></b><b></b></span>
				<span class='ecd_add_rows onethird' data-c='3'><b></b><b></b><b></b></span>	
				<span class='ecd_add_rows halfL' data-hc='2' data-hl='L' data-c='1'><em><b></b><b></b></em><b></b></span>	
				<span class='ecd_add_rows halfR' data-hc='2' data-hl='R' data-c='1'><b></b><em><b></b><b></b></em></span>	

				<span class='ecd_add_rows halfL' data-hc='3' data-hl='L' data-c='1'>
					<em><b></b><b></b><b></b></em><b></b></span>	

				<span class='ecd_add_rows halfR' data-hc='3' data-hl='R' data-c='1'><b></b><em><b></b><b></b><b></b></em></span>	
			</p>
			<div id='evo_card_field_selector' class=''>
				<h4 style='margin:0 0 10px'><?php _e('Unused Event Card Fields','eventon');?></h4>
				<div id='evo_card_field_selector_f'>
					<?php
					if( is_array($unused_fields) && count($unused_fields)>0){
						foreach($unused_fields as $ff){
							if( in_array($ff, array('timelocation','learnmoreICS'))) continue;
							echo "<span data-n='{$ff}'>". $event_card_fields[$ff][1] ."</span>";
						}
					}
					?>
				</div>
				<p class='nothing' style='<?php echo count($unused_fields) > 0 ? "display:none":'';?>'><?php _e('You are using all the available fields','eventon');?>!</p>
				<span style='margin-top:10px' id='evo_card_field_selector_c' class='evo_admin_btn btn_triad'><?php _e('Cancel','eventon');?></span>
			</div>
		</div>
		<?php

		EVO()->elements->_print_settings_footer_btns( array(
			'save_changes'=> array(
				'label'=> __('Save Changes','eventon'),
				'data'=> array(
					'lightbox_key'=>'evo_ecard_designer',
					'uid'=>'evo_evard_save',
					'ajax_action'=>'eventon_save_eventcard_designer',
				),
				'class'=> 'evo_btn evolb_trigger_save',
			)
		));
		?>
		</form>
		<?php

		return ob_get_clean();
	}
}