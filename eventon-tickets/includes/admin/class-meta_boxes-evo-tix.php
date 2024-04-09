<?php
/**
 * evo-tix post html
 * @version 2.2.8
 */

// INITIAL VALUES
	global $post, $evotx, $ajde;

	wp_nonce_field( 'evotx_edit_post', 'evo_noncename_tix' );

	$TIX 		= new evotx_tix();
	$HELPER 	= new evotx_helper();
	$TIX->evo_tix_id = $post->ID;

	$TIX_CPT = new EVO_Evo_Tix_CPT( $post->ID );

	$event_id 			= $TIX_CPT->get_event_id();	
	$repeat_interval 	= $TIX_CPT->get_repeat_interval();
	$EVENT 				= new EVO_Event( $event_id, '', $repeat_interval );
	$event_meta 		= $EVENT->get_data();
	$ticket_number 		= $TIX_CPT->get_ticket_number();

	//print_r( get_post_custom(2059));


	// Order data
	$order_id 		= $TIX_CPT->get_order_id();	
	$order 			= new WC_Order( $order_id );	
	$order_status 	= $order->get_status();

	$EA = new EVOTX_Attendees();
	$TH = $all_ticket_in_order = $EA->_get_tickets_for_order($order_id);


// new ticket number method in 1.7
	if( $ticket_number){
		if( isset($TH[$event_id][$ticket_number]) ){
			$_TH = array();
			$_TH[$event_id][$ticket_number] = $TH[$event_id][$ticket_number];
			$TH = $_TH;
		} 
	}

// Debug email templates
	if(isset($_GET['debug']) && $_GET['debug']):
		
		$order_id = $TIX_CPT->get_order_id();
		$order = new WC_Order( $order_id);
		$tickets = $order->get_items();

		$order_tickets = $TIX->get_ticket_numbers_for_order($order_id);

		$email_body_arguments = array(
			'orderid'=>$order_id,
			'tickets'=>$order_tickets, 
			'customer'=>'Ashan Jay',
			'email'=>'yes'
		);

		$email = new evotx_email();
		$tt = $email->get_ticket_email_body($email_body_arguments);
		print_r($tt);


	endif;

// get event times			
	$event_time = $EVENT->get_formatted_smart_time();

$this_ticket_data = array();

?>	
<div class='eventon_mb' style='margin:-6px -12px -12px'>
<div style='background-color:#ECECEC; padding:15px;'>
	<div style='background-color:#fff; border-radius:8px;'>
	<table width='100%' class='evo_metatable' cellspacing="" style='vertical-align:top' valign='top'>


		
		<?php // Ticket ?>
		<tr><td colspan='2'><b><?php _e('Event Ticket','evotx');?></b>					
			<div id='evotx_ticketItem_tickets' >
				<?php 
					if($TH):
						//print_r($TH);
						foreach($TH[$event_id] as $ticket_number=>$td):
							$this_ticket_data = $td;
							echo $EA->__display_one_ticket_data($ticket_number, $td, array(
								'orderStatus'=> $order_status,
								'showStatus'=>true,
								'guestsCheckable'=>$EA->_user_can_check(),	
							));
						endforeach;
					endif;
				?>
			</div>
		</td></tr>

		<tr><td><?php _e('Event','evotx');?> </td>
		<td><?php echo '<a style="white-space:normal" class="button" href="'.get_edit_post_link($event_id).'">'.get_the_title( $event_id ).': '. $event_id. '</a>';?> 
			<?php
				// if this is a repeat event show repeat information						
				if(!empty($event_meta['evcal_repeat']) && $event_meta['evcal_repeat'][0]=='yes'){
					echo "<p>".__('This is a repeating event. Repeat Instance Index','evotx').': '. $repeat_interval ."</p>";
				}
			?>
		</td></tr>

		<?php
		$data_fields = array(
			'wcid'=> array(
				__('Woocommerce Order ID','evotx').'#',
				'<a class="button" href="'.get_edit_post_link($order_id).'">'.$order_id.'</a> <span class="evotx_wcorderstatus '.$order_status.'" style="line-height: 20px; padding: 5px 20px;">'. __(sprintf('%s',$order_status),'evotx') .'</span>'
			)
		);

		foreach( array(
			'type'=>__('Ticket Type','evotx'),
			'email'=>__('Order Email','evotx'),
			'qty'=>__('Quantity','evotx'),
			'cost'=>__('Cost for ticket(s)','evotx'),

		) as $k=>$v){
			$d = $TIX_CPT->get_prop($k);	

			if( !$d){
				if( isset( $this_ticket_data[ $k]) ) $d = $this_ticket_data[ $k];
			}
			$d = !$d? '--': $d;
			if( $k=='cost') $d = $HELPER->convert_to_currency($d);

			$data_fields[ $k] = array( $v , $d);
		}

		// ticket number and time
		if( $ticket_number ) $data_fields['tix_num'] = array(__('Ticket Number','evotx'),$ticket_number);
		$data_fields['tix_time'] = array(__('Ticket Time','evotx'),$event_time);
		
		// checked in 
			$st_count = $TIX->checked_count($post->ID);
			$status = $TIX->get_checkin_status_text('checked');
			$__count = ': '.(!empty($st_count['checked'])? $st_count['checked']:'0').' out of '. $TIX_CPT->get_prop('qty');
			$data_fields['tix_checkin'] = array(__('Ticket Checked-in Status','evotx'), $status.$__count );

		// tickets purchased by
			$purchaser_id = $TIX_CPT->get_prop('_customerid');
			$purchaser = get_userdata($purchaser_id);
			if($purchaser) $data_fields['tix_purch'] = array(__('Ticket Purchased by','evotx'), $purchaser->last_name.' '.$purchaser->first_name );

		// Ticket number instance
			$_ticket_number_instance = $TIX_CPT->get_ticket_number_instance();
			$data_fields['tix_numinst'] = array(__('Ticket Instance Index in Order','evotx') . $ajde->wp_admin->tooltips('This is the event ticket instance index in the order. Changing this will alter ticket holder values. Edit with cautions!') , "<input style='width:100%' type='text' name='_ticket_number_instance' value='{$_ticket_number_instance}'/>");

		// Other Ticket Data
			$data_fields['tix_od'] = array( '<b>'. __('Other Ticket Data','evotx') . '</b>', null, true);
			$data_fields['tix_od1'] = array(  __('Order Item ID','evotx'), $TIX_CPT->get_order_item_id() );
			$data_fields['tix_od2'] = array(  __('Woocommerce Product ID','evotx'), $TIX_CPT->get_prop('wcid') );
			$data_fields['tix_od3'] = array(  "<a id='evotix_sync_with_order' data-oid='{$order_id}' class='evo_admin_btn' >".__('Sync with WC Order','evotx')."</a>".$ajde->wp_admin->tooltips('This will sync order item ids of woocommerce order with this ticket!')."<span style='margin-left:40px;'></span>", null , true);

		// print HTML
		foreach($data_fields as $key=>$val){
			$CP = ( !empty($val[2]) && $val[2]) ? "colspan='2'":null;
			echo "<tr><td {$CP}>". $val[0] ."</td><td>". (isset($val[1])?$val[1]:null) ."</td></tr>";
		}
		
		
		 
		if($TH):

			$ticket_number_index = $TIX_CPT->get_prop('_ticket_number_index');
			$ticket_number_index = $ticket_number_index? $ticket_number_index: '0';
			
			foreach(array(
				'order_id'=> $order_id,
				'event_id'=> $event_id,
				'ri'=> $repeat_interval,
				'Q'=>$ticket_number_index,
				'event_instance'=>$_ticket_number_instance
			) as $F=>$V){
				echo "<input type='hidden' name='{$F}' value='{$V}'/>";
			}


			//print_r($TH);
		?>

		<?php
		// Additional ticket holder information
		?>
			<tr><td colspan='2'><b><?php _e('Additional Ticket Holder Information','evotx');?></b></td></tr>
			<tr><td><?php _e('Name','evotx');?>: </td>
				<td data-d=''>
					<input style='width:100%' type='text' name="_ticket_holder[name]" value='<?php 	echo $TH[$event_id][$ticket_number]['name'];	?>'/>
				</td>
			</tr>

			<?php 

			// print out additional ticket holder data
			if( isset($TH[$event_id][$ticket_number]['th']) && is_array($TH[$event_id][$ticket_number]['th']) && isset($TH[$event_id][$ticket_number]['th']['name']) ):

				unset($TH[$event_id][$ticket_number]['th']['name']);


				foreach($TH[$event_id][$ticket_number]['th'] as $f=>$v){

					if( in_array($f, array('customer_id','oS','aD'))) continue;

					?>
					<tr><td><?php echo __(sprintf( '%s', $f), 'evotx');?>: </td>
						<td data-d=''>
							<input style='width:100%' type='text' name='_ticket_holder[<?php echo $f;?>]' value='<?php 	echo $v;	?>'/>
						</td>
					</tr>
					<?php
				}					


			endif;?>


		<?php
		// Other tickets on the same order
		?> 

			<tr><td colspan='2'><b><?php _e('Other Tickets on Same Order ID','evotx');?>: <?php echo $order_id;?></b></td></tr>
			<tr><td colspan='2'>
				<?php
					$count = 0;
					foreach($all_ticket_in_order as $__event_id=>$_event_tickets){
						
						foreach( $_event_tickets as $ticket_number => $ticket_data){

							if( $ticket_data['id'] ==  $post->ID) continue;

							//print_r($ticket_data);
							echo '<a href="'. get_edit_post_link( $ticket_data['id'] ) .'" class="evo_admin_btn">'.$ticket_number.'</a> ';
							$count ++;
						}
						
					}

					// no other tickets message
					if( $count == 0){
						echo __('No other tickets','evotx');
					}

				?>
				</td>
			</tr>


		<?php endif;?>
		<?php						
			do_action('eventontx_tix_post_table',$post->ID, $TIX_CPT->get_props(), $event_id, $TIX_CPT);
		?>
		
		
	</table>
	</div>
</div>
</div>