<?php
/**
 * Ticket extension of the event
 * @version 2.2.6
 */

class evotx_event extends EVO_Event{

	public $wcid, $wcmeta, $product, $ri;
	public function __construct($event_id, $event_pmv='', $RI=0, $wcid=''){
		parent::__construct($event_id, $event_pmv);
		$this->wcid = empty($wcid)? $this->get_wcid(): $wcid;
		$this->wcmeta = $this->wcid? get_post_custom($this->wcid): false;	
		$this->ri = $RI;	

		//global $product;
		$this->product = wc_get_product($this->wcid);
		$GLOBALS['product'] = $this->product;
	}

	function get_wcid(){
		return $this->get_prop('tx_woocommerce_product_id')? (int)$this->get_prop('tx_woocommerce_product_id'):false;
	}
	
	// get repeat with stocks available
	function next_available_ri($current_ri_index, $cut_off = 'start'){
		$current_ri_index = empty($current_ri_index)? 0:$current_ri_index;

		if(!$this->is_ri_count_active()) return false;
		
		// if all stocks are out of stock
		$stock_status = $this->get_ticket_stock_status();
		if($stock_status=='outofstock') return false;


		// check repeats
		$repeats = $this->get_repeats();
		if(!$repeats) return false;
	
		$current_time = EVO()->calendar->get_current_time();

		$return = false;
		foreach($repeats as $index=>$repeat){
			if($index<= $current_ri_index) continue;

			$ri_start = $this->__get_tz_based_unix( $repeat[0] );
			$ri_end = $this->__get_tz_based_unix( $repeat[1] );

			// check if start time of repeat is current
			if($cut_off == 'start' && ( $ri_start ) >=  $current_time) $return = true;
			if($cut_off != 'start' && ( $ri_end ) >=  $current_time) $return = true;

			if($return){

				$ri_stock = $this->get_repeat_stock($index);
				if($ri_stock>0) return array('ri'=>$index, 'times'=>$repeat);
			}				
		}
		
		return false;
	}


// Cart - output - array
// @1.7.2
	function add_ticket_to_cart($DATA){
		if(!isset($DATA)) return false;

		$default_ticket_price = $this->product->get_price();

		$cart_item_keys = false;
		$status = 'good'; $output = '';

		$qty = $DATA['qty'];

		//print_r( $DATA);

		// hook for ticket addons
		$plug = apply_filters('evotx_add_ticket_to_cart_before',false, $this,$DATA);
		if($plug !== false){	return $plug;	}


		// load location information
			$loc_data = $this->get_location_data();
			$location_name = isset($loc_data) && isset($loc_data['location_name']) ? $loc_data['location_name']: '';
		
		// gather cart item data before adding to cart
			$_cart_item_data_array = array(
					'evotx_event_id_wc'			=> $this->ID,
					'evotx_repeat_interval_wc'	=> $this->ri,
					'evotx_elocation'			=> $location_name,
					'evotx_lang'				=> (isset($DATA['l'])? $DATA['l']: 'L1')
				);

			// name your price
			if( isset($DATA['nyp'])){

				if( $this->check_yn('_name_yprice')){
					$min_nyp = $this->get_prop('_evotx_nyp_min');

					if( $DATA['nyp'] < $min_nyp ){
						return json_encode( array(
							'msg'=> __('Your price is lower than minimum price','evotx'), 
							'status'=> 'bad'
						));exit;
					}
				}else{
					return json_encode( array(
						'msg'=> __('Name your price is not enabled','evotx'), 
						'status'=> 'bad' 
					));exit;
				}

				$_cart_item_data_array['evotx_yprice'] = $DATA['nyp'];
			} 
			
			$cart_item_data = apply_filters('evotx_add_cart_item_meta', $_cart_item_data_array, $this, $default_ticket_price, $DATA);
			
		// Add ticket to cart
			if( is_array($cart_item_data)  ){
				$cart_item_keys = WC()->cart->add_to_cart(
					$this->wcid,
					apply_filters('evotx_add_cart_item_qty',$qty, $this, $default_ticket_price, $DATA),
					0,array(),
					$cart_item_data
				);
			// if filter pass cart item keys
			}else{
				$cart_item_keys = $cart_item_data;
			}

			if($cart_item_keys){

				
				// get total cart quantity for this item
				$DATA['cart_qty'] = WC()->cart->cart_contents[ $cart_item_keys ]['quantity'];
				do_action('evotx_after_ticket_added_to_cart', $cart_item_keys, $this, $DATA, $cart_item_data);
			}


		// Successfully added to cart
		if($cart_item_keys !== false){
			$tx_help = new evotx_helper();
			$output = $tx_help->add_to_cart_html();
			$msg = evo_lang('Ticket added to cart successfully');
		}else{
			$status = 'bad';
			$msg = evo_lang('Could not add ticket to cart, please try later!');
		}

		return json_encode( apply_filters('evotx_ticket_added_cart_ajax_data', array(
			'msg'=>$msg, 
			'status'=> $status,
			'html'=>$output,
			't'=>$DATA
		), $this, $DATA));exit;

	}

	// check if an event ticket is already in cart 
	function is_ticket_in_cart_already(){

		if( !function_exists('WC')) return;
		if( empty( WC()->cart )) return;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			// if event id and repeat interval missing skip those cart items
			if(empty($cart_item['evotx_event_id_wc'])) continue;
			if(!isset($cart_item['evotx_repeat_interval_wc'])) continue;

			if ( $cart_item['product_id'] > 0 ) {

				if( $cart_item['evotx_event_id_wc'] == $this->ID && $cart_item['evotx_repeat_interval_wc'] == $this->ri) return true;
			}
		}

		return false;
	}

// WC Ticket Product
	function wc_is_type($type){
		if(!$this->product) return false;
		return $this->product->is_type($type);
	}

	function get_wc_prop($field, $def = false){
		if(!$this->wcmeta) return $def;
		if(!isset($this->wcmeta[$field])) return $def;
		return $this->wcmeta[$field][0];
	}

// Event Repeat & Stock
	function get_repeat_stock($repeat_index = 0){
		if(!$this->is_ri_count_active()) return false;

		$ri_capacity = $this->get_prop('ri_capacity');
		if(!isset( $ri_capacity[$repeat_index] )) return 0;
		return $ri_capacity[$repeat_index];
	}

// tickets	
	
	// return is there are tickets for sale remaining
	function has_tickets(){
		// check if tickets are enabled for the event
			if( !$this->check_yn('evotx_tix')) return false;


		// if tickets set to out of stock 
			if(!empty($this->wcmeta['_stock_status']) && $this->wcmeta['_stock_status'][0]=='outofstock') return false;
		
		// if manage capacity separate for Repeats
		if( $this->is_ri_count_active() ){
			$ri_capacity = $this->get_prop('ri_capacity');
				$capacity_of_this_repeat = 
					(isset($ri_capacity[ $this->ri ]) )? 
						$ri_capacity[ $this->ri ]
						:0;
				return ($capacity_of_this_repeat==0)? false : $capacity_of_this_repeat;
		}else{
			// check if overall capacity for ticket is more than 0
			$manage_stock = (!empty($this->wcmeta['_manage_stock']) && $this->wcmeta['_manage_stock'][0]=='yes')? true:false;
			$stock_count = (!empty($this->wcmeta['_stock']) && $this->wcmeta['_stock'][0]>0)? $this->wcmeta['_stock'][0]: false;
			
			// return correct
			if($manage_stock && !$stock_count){
				return false;
			}elseif($manage_stock && $stock_count){	return $stock_count;
			}elseif(!$manage_stock){ return true;}
		}
	}

	function is_ticket_active(){
		if( !$this->check_yn('evotx_tix')) return false;
		if(!$this->wcid) return false;
		return true;
	}
		
	// check if tickets can be sold based on event start/end time with current time
	function is_stop_selling_now(){
		$stop_sell = $this->get_prop('_xmin_stopsell');
		
		EVO()->cal->set_cur('evcal_tx');
		$stopsellingwhen = EVO()->cal->get_prop('evotx_stop_selling_tickets');
		$stopsellingwhen = $stopsellingwhen && $stopsellingwhen == 'end'? 'end':'start';

		//date_default_timezone_set('UTC');	
		$current_time = EVO()->calendar->get_current_time();

		$event_unix = $this->get_event_time( $stopsellingwhen );			
		$timeBefore = $stop_sell ? (int)($this->get_prop('_xmin_stopsell'))*60 : 0;	

		$cutoffTime = $event_unix -$timeBefore;


		//echo ($cutoffTime < $current_time)?'y':'n';
		return ($cutoffTime < $current_time)? true: false;
		
	}

	// check if the stock of a ticket is sold out
	// @added 1.7
	function is_sold_out(){
		if(!empty($this->wcmeta['_stock_status']) && $this->wcmeta['_stock_status'][0]=='outofstock')
			return true;
		return false;
	}

	// check if ticket is sold individually - @since 2.2
	function is_sold_individually(){
		$val = $this->get_wc_prop( '_sold_individually');
		return ($val == 'yes') ? true : false;		
	}

	// show remaining stop or not
	// @added 1.7 @~ 1.7.2
		function is_show_remaining_stock($stock = ''){

			$tickets_in_stock = $this->has_tickets();

			if(!$this->wc_is_type('simple')) return false;
			if(is_bool($tickets_in_stock) && !$tickets_in_stock) return false;

			if(
				$this->check_yn('_show_remain_tix') &&
				evo_check_yn($this->wcmeta,'_manage_stock') 
				&& !empty($this->wcmeta['_stock']) 
				&& $this->wcmeta['_stock_status'][0]=='instock'
			){

				// show remaining count disabled
				if(!$this->get_prop('remaining_count')) return true;

				// show remaing at set but not managing cap sep for repeats
				if( $this->get_prop('remaining_count') && !$this->check_yn('_manage_repeat_cap') && (int)$this->get_prop('remaining_count') >= $this->wcmeta['_stock'][0]) return true;

				if( $this->get_prop('remaining_count') && $this->check_yn('_manage_repeat_cap') && (int)$this->get_prop('remaining_count') >= $stock ) return true;

				return false;
			}
			return false;
		}

// Attendees
	function has_user_purchased_tickets($user_id =''){
		if( !is_user_logged_in()) return false;
		if(!$this->wcid) return false;

		// if user id is not provided
		if(empty($user_id)){
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
		}

		// check customer tickets
		$AA = array(
			'posts_per_page'=>-1,
			'post_type'=>'evo-tix',
			'meta_query'=> array(
				'relation'=>'AND',
				array(
					'key'=>'_eventid',
					'value'=> $this->ID,
				),array(
					'key'=>'wcid',
					'value'=> $this->wcid,
				),array(
					'key'=>'_customerid',
					'value'=> $user_id,
				)
			)
		);

		// if manage repeat stock by repeat count active
		if( $this->is_ri_count_active() ){
			$AA['meta_query'][] = array(
				'key'=>'repeat_interval',
				'value'=> $this->ri,
			);
		}

		$TT = new WP_Query($AA);

		$bought = false;

		if( $TT->have_posts()){

			foreach($TT->posts as $P){

				$order_id = get_post_meta($P->ID, '_orderid',true);
				$order_st = get_post_status( $order_id);
				
				if($order_st != 'wc-completed') continue;

				$bought = true;
			}
		}

		if($bought) return true;

		return false;
		//wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product->get_id() )
	}

	// get ticket post id by user id
		public function get_ticket_post_id_by_uid($uid){
			if(!$uid) return false;
			$uid = (int)$uid;

			$II = new WP_Query(array(
				'posts_per_page'=>1,
				'post_type'=>'evo-tix',
				'meta_query'=>array(
					'relation' => 'AND',
					array(	'key'	=> '_eventid','value'	=> $this->ID	),					
					array(	'key'	=> '_customerid','value'	=> $uid	),
					array(
						'relation' => 'OR',
						array(	'key'	=> 'repeat_interval','value'	=> $this->ri	),
						array(	'key'	=> 'repeat_interval','compare'	=> 'NOT EXISTS'	),
					)
				)
			));

			if(!$II->have_posts()) return  false;

			return $II->posts[0]->ID;
		}


	// check if a user has rsvped and has signed in
		public function is_user_signedin($uid){
			if(!$uid) return false;
			$uid = (int)$uid;

			$II = new WP_Query(array(
				'posts_per_page'=>1,
				'post_type'=>'evo-tix',
				'meta_query'=>array(
					'relation' => 'AND',
					array(	'key'	=> '_eventid','value'	=> $this->ID	),		
					array(	'key'	=> 'signin','value'	=> 'y'	),
					array(	'key'	=> '_customerid','value'	=> $uid	),
					array(
						'relation' => 'OR',
						array(	'key'	=> 'repeat_interval','value'	=> $this->ri	),
						array(	'key'	=> 'repeat_interval','compare'	=> 'NOT EXISTS'	),
					)
				)
			));

			return $II->have_posts() ? true: false;
		}

	public function get_guest_list(){
		$EA = new EVOTX_Attendees();
		$TH = $EA->get_tickets_for_event($this->ID);
		$total_tickets = 0;
		$output = '';

		if(!$TH || count($TH)<1) return false;

		ob_start();
		$cnt = $checked_count = 0;
		$tix_holders = array();
		$guests = array();

		//print_r($TH);
		foreach($TH as $tn=>$td){

			// validate
			if(empty($td['name'])) continue;
			if(trim($td['name']) =='') continue;

			// check for RI
			if($td['ri'] != $this->ri) continue;
			//if(in_array($td['name'], $guests)) continue;

			// skip refunded tickets
			if($td['s'] == 'refunded') continue;
			if($td['oS'] != 'completed') continue;

			// get checked count
			if($td['s']== 'checked')  $checked_count++;

			$tix_holders[ $td['name'] ] = array_key_exists( $td['name'] , $tix_holders) ? 
				$tix_holders[ $td['name'] ] + 1 : 1;		
			
			$cnt++;
		}


		foreach($tix_holders as $name=>$count){
			$guests[] = $name;
			echo apply_filters('evotx_guestlist_guest',"<span class='fullname' data-name='".$name."' >". $name . ( $count >1 ? ' ('. $count .')':'') . "</span>", $td);
		}


		$output = ob_get_clean();			

		return array(
			'guests'=>	$output,
			'count'=>	$cnt,
			'checked'=> $checked_count
		);
	}
	
// stock
	function get_ticket_stock_status(){
		return (!empty($this->wcmeta['_stock_status']))? $this->wcmeta['_stock_status'][0]: false;
	}
	function is_ri_count_active(){
		return (!empty($this->wcmeta['_manage_stock']) && $this->wcmeta['_manage_stock'][0]=='yes'
		&& ($this->get_prop('_manage_repeat_cap')) && $this->get_prop('_manage_repeat_cap')=='yes'
		&& ($this->get_prop('ri_capacity'))
		&& $this->is_repeating_event()
		)? true: false;
	}

}