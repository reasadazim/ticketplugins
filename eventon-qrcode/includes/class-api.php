<?php
/**
 *	QR Code api access
 *	@version 2.0.1
 */

class EVOQR_API{
	public function __construct(){

		if( EVO()->cal->check_yn('evoqr_enable_api_access','evcal_1'))
			add_action( 'rest_api_init', array($this, 'custom_rest_api'));			
	}

	public function custom_rest_api(){
		
		// get events list
			register_rest_route( 
				'eventon','/events_list', 
				array(
					'methods' => 'GET',
					'callback' => array($this,'events_list'),					
					'permission_callback' => function (WP_REST_Request $request) {
	                	return true;
	                	//return EVOQR()->checkin->is_user_have_permission_to_checkin();
	            	}
				) 
			);

		// attendees list
			register_rest_route( 
				'eventon','/attendees_list/(?P<id>[a-zA-Z0-9-_]+)', 
				array(
					'methods' => 'GET',
					'callback' => array($this,'attendees_list'),
					'permission_callback' => function (WP_REST_Request $request) {
	                	return true;
	                	//return EVOQR()->checkin->is_user_have_permission_to_checkin();
	            	}
				) 
			);

		// one attendee
			register_rest_route( 
				'eventon','/one_attendee/(?P<id>[a-zA-Z0-9--]+)', 
				array(
					'methods' => 'GET',
					'callback' => array($this,'one_attendee'),	
					'args'	=> array(
						'id'=> array(
							'validate_callback'=> function($param, $request, $key){
								return true;
							}
						)
					),				
					'permission_callback' => function (WP_REST_Request $request) {
	                	return true;
	                	//return EVOQR()->checkin->is_user_have_permission_to_checkin();
	            	}
				) 
			);

		// checkin / check out ticket number
			register_rest_route( 
				'eventon','/checkin/(?P<id>[a-zA-Z0-9-]+)',  // /(?P<slug>\w+)
				array(
					'methods' => 'GET',
					'callback' => array($this,'checkin_ticket_number'),	
					'args'	=> array(
						'id'=> array(
							'validate_callback'=> function($param, $request, $key){
								return true;
							}
						)
					),				
					'permission_callback' => function (WP_REST_Request $request) {
	                	return true;
	                	//return EVOQR()->checkin->is_user_have_permission_to_checkin();
	            	}
				) 
			);
	}

	// get list of attendees for event id
	public function attendees_list($data){

		$output_data = array();

		if(!isset($data['id'])){
			$output_data[] = 'Missing Event ID';
		}elseif(!strpos($data['id'], '_')){
			$output_data[] = 'Event ID passed incorrect format';
		}else{
			$event_uid = (int)$data['id'];

			$EID = explode('_', $event_uid);
			$RI = !empty($EID[1]) ? $EID[1] : '0';

			$EVENT = new EVO_Event( $EID[0], '', $RI);

			// rsvp
			if( $EVENT->check_yn('evors_rsvp')){
				$RSVP = new EVORS_Event( $EVENT);

				$list = $RSVP->GET_rsvp_list();

				foreach($list as $rsvp_status=>$guests){
					foreach($guests as $guest_id=>$guest_data){

						$output_data[$guest_id] = array(
							'attendee_id'=>$guest_id,
							'ticket_number'=>$guest_id,
							'rsvp_status'=> $rsvp_status
						);

						foreach( array(
							'first_name'=> 'fname',
							'last_name'=> 'lname',
							'check_status'=> 'status',
							'email'=> 'email',
							'count'=> 'count',
						) as $FF=>$VV){
							$output_data[$guest_id][ $FF ] = ( !isset( $guest_data[ $VV ]) ) ? '-':$guest_data[ $VV ];
						}						
					}
				}
			}

			// ticket
			if( $EVENT->check_yn('evotx_tix') ){
				$EA = new EVOTX_Attendees();
				$TH = $EA->get_tickets_for_event( $EID[0] );

				if( !$TH || count($TH)< 0 ){
					$output_data['message'] = 'No attendees for the event';
					$output_data['message_code'] = 'no_attendees';
				}else{
					foreach($TH as $tn=>$guest_data){

						$output_data[$tn] = array(
							'attendee_id'=>$guest_data['id'],
							'ticket_number'=>$tn
						);

						foreach( array(
							'name'=> 'name',
							'check_status'=> 's',
							'email'=> 'email',
							'order_id'=> 'o',
							'order_status'=> 'oS',
						) as $FF=>$VV){
							$output_data[$tn][ $FF ] = ( !isset( $guest_data[ $VV ]) ) ? '-':$guest_data[ $VV ];
						}

					}
				}
			}			
		}


		//create response object
		$response = new WP_REST_Response( $output_data );		
		$response->set_status( 201 );// add custom status code		
		$response->header( 'Location', 'http://myeventon.com/' );// add custom header

		return $response;
	}

	public function one_attendee($data){
		$output_data = array();

		if(!isset($data['id'])){
			$output_data[] = 'Missing Ticket Number';
		}else{	

			$ticket_number = $data['id'];

			// ticket
			if(strpos($ticket_number, '-')){

				$ET = new evotx_tix();
				$evotix_id = $ET->evo_tix_id = $ET->get_evotix_id_by_ticketnumber( $ticket_number );

				$EA = new EVOTX_Attendees();
				$attendee_data = $EA->get_one_ticket_data( $evotix_id );

				foreach($attendee_data as $ticket_number=>$guest_data){

					$output_data = array(
						'attendee_id'=>$guest_data['id'],
						'ticket_number'=>$ticket_number
					);

					foreach( array(
						'name'=> 'name',
						'check_status'=> 's',
						'email'=> 'email',
						'order_id'=> 'o',
						'order_status'=> 'oS',
						'spaces'=> '1',
						'event_id'=> 'event_id',
					) as $FF=>$VV){
						$output_data[ $FF ] = ( !isset( $guest_data[ $VV ]) ) ? '-':$guest_data[ $VV ];
					}

					if( isset($guest_data['oD']['event_title'])){
						$output_data['event_name'] = $guest_data['oD']['event_title'];
					}
					if( isset($guest_data['oD']['event_start_raw'])){
						$output_data['start_raw'] = $guest_data['oD']['event_start_raw'];
					}
					if( isset($guest_data['oD']['event_duration'])){
						$output_data['duration'] = $guest_data['oD']['event_duration'];
					}

				}

			// rsvp
			}else{
				$RSVP = new EVO_RSVP_CPT($ticket_number);
				$EVENT = new EVO_Event($RSVP->event_id(), '', $RSVP->repeat_interval());

				$output_data = array(
					array(
						'attendee_id'=>		$ticket_number,
						'ticket_number'=>	$ticket_number,
						'first_name'=> 		$RSVP->first_name(),
						'last_name'=>		$RSVP->last_name(),				
						'check_status'=>	$RSVP->checkin_status(),				
						'rsvp_status'=>	$RSVP->get_rsvp_status(),				
						'email'=>			$RSVP->email(),				
						'spaces'=>			$RSVP->count(),	
						'event_name'=>		$EVENT->get_title(),				
						'event_id'=>		$EVENT->get_event_uniqid(),				
						'start_raw'=>		$EVENT->start_unix,				
						'duration'=>		$EVENT->duration,				
					)
				);
			}				
		}
	
		//create response object
		$response = new WP_REST_Response( $output_data );		
		$response->set_status( 201 );// add custom status code		
		$response->header( 'Location', 'http://myeventon.com/' );// add custom header

		return $response;
	}

	// checkin(not checked in/checked out) checked attendees
	public function checkin_ticket_number($data){
		$output_data = array();

		$new_check_status = $data->get_param('checkin_status');

		if( !in_array($new_check_status, array('checked','check-in'))){
			$output_data[] = 'Missing a valid new checkin status parameter checkin_status';
		}elseif(!isset($data['id'])){
			$output_data[] = 'Missing Ticket Number';
		}else{
			$ticket_number = $data['id'];

			// ticket
			if(strpos($ticket_number, '-')){
				$ET = new evotx_tix();
				$evotx_id = $ET->evo_tix_id = $ET->get_evotix_id_by_ticketnumber( $ticket_number );

				$TIX = new EVO_Evo_Tix_CPT( $evotx_id );
				$current_checkin_status = $TIX->get_status();
				$order_status = $TIX->get_order_status();

				if( $current_checkin_status == $new_check_status){
					if( $current_checkin_status == 'checked'){
						$output_data['message'] = 'Already checked!';
						$output_data['message_code'] = 'already_checked';
					}else{ // passing check-in
						$output_data['message'] = 'Already set to checked out!';
						$output_data['message_code'] = 'already_checked_out';
					}
				}else{		

					if( $order_status != 'wc-completed' ){
						$output_data['message'] = ($order_status == 'wc-refunded') ? 
							'Ticket purchase order has been refunded':
							'Ticket purchase order is not completed!';
						$output_data['message_code'] = 'order_not_completed';
					}else{
						$TIX->set_status( $new_check_status );
					
						if( $new_check_status == 'checked'){
							$output_data['message'] = 'Successfully checked!';
							$output_data['message_code'] = 'checked';
						}else{
							$output_data['message'] = 'Successfully checked out!';
							$output_data['message_code'] = 'checked_out';
						}	
					}					
				}

			// rsvp
			}else{

				$RSVP = new EVO_RSVP_CPT($ticket_number);
				$current_checkin_status = $RSVP->checkin_status();
				$rsvp_status = $RSVP->get_rsvp_status();

				if( $current_checkin_status == $new_check_status){
					if( $current_checkin_status == 'checked'){
						$output_data['message'] = 'Already checked!';
						$output_data['message_code'] = 'already_checked';
					}else{ // passing check-in
						$output_data['message'] = 'Already set to checked out!';
						$output_data['message_code'] = 'already_checked_out';
					}
				}else{
					if( $rsvp_status == 'n'){
						$output_data['message'] = 'Guest has rsvped as NO!';
						$output_data['message_code'] = 'rsvped_no';
					}else{						
						$RSVP->set_prop('status', $new_check_status );

						if( $new_check_status == 'checked'){
							$output_data['message'] = 'Successfully checked!';
							$output_data['message_code'] = 'checked';
						}else{
							$output_data['message'] = 'Successfully checked out!';
							$output_data['message_code'] = 'checked_out';
						}
						
					}
				}
			}
		}


		//create response object
		$response = new WP_REST_Response( $output_data );		
		$response->set_status( 201 );// add custom status code		
		$response->header( 'Location', 'http://myeventon.com/' );// add custom header

		return $response;
	}

	public function events_list($args = ''){

		$args = !empty($args)? $args: array();
		$data_args = array();
		
		// only allow publish posts
		$_adds = array( 'wp_args'=> array('post_status'=>'publish'));
		$data_args = array_merge_recursive($data_args, $_adds);
			

		// Pass event shortcodes
			$filters = $_filters = array();
			$SC = EVO()->calendar->shortcode_args;

			foreach(EVO()->calendar->shell->get_all_event_tax() as $slug=>$name){
				if(empty($args[$name]) || $args[$name]=='all') continue;
				$SC[ $name ] = $args[$name];
			}
		
			EVO()->calendar->update_shortcode_arguments( $SC );


		// GET EVENT DATA
		$query_events = EVO()->calendar->get_events_from_wp_query( $data_args );
		$output_events = array();
		
		if($query_events){

			foreach( $query_events->posts as $post):			

				$EVENT = new EVO_Event( $post->ID, '',0,true, $post);

				$event_type = 'no_attendees';
				if( $EVENT->check_yn('evors_rsvp') ) $event_type = 'rsvp';
				if( $EVENT->check_yn('evotx_tix') ) $event_type = 'tickets';

				if( $EVENT->is_repeating_event() ){

					foreach( $EVENT->get_repeats() as $rindex=>$repeat){
						if(!is_array($repeat)) continue;
						$EVENT->ri = $rindex;

						$uid = $EVENT->get_event_uniqid();

	 					$output_events[ $uid ] = array(
	 						'name'		=> html_entity_decode( $EVENT->get_title() ),
	 						'start'		=> $EVENT->start_unix,
	 						'start_raw'		=> $EVENT->start_unix_raw,
	 						'duration'	=> $EVENT->duration,
	 						'timezone'	=> $EVENT->get_prop('evo_event_timezone'),
	 						'event_type'	=> $event_type,
	 					);
					}

					$EVENT->ri = 0;

				}else{
 					$uid = $EVENT->get_event_uniqid();

 					$output_events[ $uid ] = array(
 						'name'		=> html_entity_decode( $EVENT->get_title() ),
 						'start'		=> $EVENT->start_unix,
 						'start_raw'		=> $EVENT->start_unix_raw,
 						'duration'	=> $EVENT->duration,
 						'timezone'	=> $EVENT->get_prop('evo_event_timezone'),
 						'location_name'=> $EVENT->get_location_name(),
 						'event_type'	=> $event_type,
 					);
				}

			endforeach;
		}else{
			$output_events[] = 'no_events';
		}

		//create response object
		$response = new WP_REST_Response( $output_events );		
		$response->set_status( 201 );// add custom status code		
		$response->header( 'Location', 'http://myeventon.com/' );// add custom header

		return $response;

	}
}