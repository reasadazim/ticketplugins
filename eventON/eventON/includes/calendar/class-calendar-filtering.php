<?php
/**
* Calendar Filtering
* @version 4.6.1
*/

class EVO_Cal_Filering{
	public $cal, $SC;
	public function __construct(){
		$this->cal = EVO()->evo_generator;
		
		add_filter('evo_cal_main_btns', array($this, 'cal_header_btn'),10,2);
		add_action('evo_cal_main_btns_end', array($this,'sort_icon'), 10,1);
	}

	function cal_header_btn( $A, $arg){
		$sortfilter = true;

		if( $this->cal->evcal_hide_sort == 'yes') return $A;
		if(isset($arg['hide_so']) && $arg['hide_so'] == 'yes') return $A;
		if(isset($arg['filters']) && $arg['filters'] == 'no') return $A;

		if(empty($this->cal->evopt1['evcal_filter_options'])) return $A;
		$A['evo-filter-btn'] = '';
		return $A;
	}

	// SORT EVENTS button
		function sort_icon($args){
			if( $this->cal->evcal_hide_sort == 'yes') return false;
			if(isset($args['hide_so']) && $args['hide_so'] == 'yes') return false;

			$sorting_options = (!empty($this->cal->evopt1['evcal_sort_options']))?$this->cal->evopt1['evcal_sort_options']:array();

			if( count ($sorting_options) <1) return false;
			
			echo "<span class='cal_head_btn evo-sort-btn'>";
			$this->get_sort_content($args);
			echo "</span>";
		}

		// for header buttons
		function get_sort_content($args){	

			if( $this->cal->evcal_hide_sort != 'yes'){ // if sort bar is set to show

				$sorting_options = (!empty($this->cal->evopt1['evcal_sort_options']))?$this->cal->evopt1['evcal_sort_options']:array();

				// sorting section
				$evsa1 = array(
					'date'=>'Date',
					'title'=>'Title',
					'color'=>'Color',
					'posted'=>'Post Date'
				);
				$sort_options = array(	1=>'sort_date', 'sort_title','sort_color','sort_posted');
					$__sort_key = substr($args['sort_by'], 5);

				if(count($sorting_options)>0){
					echo "<div class='evo_header_drop_menu eventon_sort_line'>";

						$cnt =1;
						foreach($evsa1 as $so=>$sov){
							if(in_array($so, $sorting_options) || $so=='date' ){
							echo "<p data-val='sort_".$so."' data-type='".$so."' class='evs_btn evo_sort_option ".( ($args['sort_by'] == $sort_options[$cnt])? 'evs_hide select':null)."' >"
									.$this->cal->lang('evcal_lang_s'.$so,$sov)
									."</p>";
							}
							$cnt++;
						}
					echo "</div>";
				}
			}

		}

	// get post tags by ajde_events post type
		function get_terms_id_by_post_type( $taxonomy, $post_type ) {
		    global $wpdb;
		    $query = $wpdb->get_results( $wpdb->prepare( "SELECT t.*
		    	FROM $wpdb->terms AS t 
		    	INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id 
		    	INNER JOIN $wpdb->term_relationships AS r ON r.term_taxonomy_id = tt.term_taxonomy_id 
		    	INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id 
		    	WHERE p.post_type = %s  
		    	AND tt.taxonomy = %s
		    	GROUP BY t.term_id"
		    , $post_type, $taxonomy));

		   	//print_r($query);
		    return $query;
		}

	// HTML Calendar header filter and sort content @4.6
		public function get_filter_data(){
			$SC = $this->SC;

			if( empty( $SC )) return;

			// enabled filters in settings
			$filtering_options = EVO()->cal->get_prop('evcal_filter_options','evcal_1');
				if( !$filtering_options ) $filtering_options = array();
			$filter_show_set_only =  $SC->_is('filter_show_set_only');

			$_filter_array = $this->cal->shell->get_all_event_tax();
			$_filter_array = apply_filters('eventon_so_filters', $_filter_array);
			$__text_all_ = $this->cal->lang('evcal_lang_all', 'All');

			$filter_data = array();

			foreach($_filter_array as $ff=>$vv){
				if(!in_array($vv, $filtering_options)) continue;
				$skip = false;

				$raw_filter_val = rtrim( ( $SC->get_prop( $vv ) ? $SC->get_prop( $vv ) : 'all') , ',');


				extract( $this->process_filter_terms( $raw_filter_val ,$ff ) );
				
				// custom taxonomonies
				if( in_array($ff, array('evpf','evvir','evst','evotag') ) ){
					$filter_data[ $vv ] = array(
						'__tax'=> $vv,
						'__terms' => $raw_terms_array,
						'__filter_type' =>'custom',
						'__def_val'=> $raw_terms_array,
						'__invals'=> $in_values,
						'__notvals'=>$not_values,						
					);
				}

				switch ($ff) {
					case 'evpf': // past future events
						$filter_data[ $vv ]['__name'] = evo_lang('Past and Future Events');
						$filter_data[ $vv ]['__list'] = array(
							'all' => array("all", $__text_all_ ),
							'past' => array("past", evo_lang('Only Past Events') ),
							'future' => array("future", evo_lang('Only Future Events') ),
						);						
					break;
					case 'evvir':
						$filter_data[ $vv ]['__name'] = evo_lang('Virtual Events');
						$filter_data[ $vv ]['__list'] = array(
								'all' => array("all", $__text_all_ ),
								'vir' => array("vir", evo_lang('Virtual Events') ),
								'nvir' => array("nvir", evo_lang('Non Virtual Events') ),
							);						
					break;

					case 'evst':
						// translated values
						$VV = array();
						foreach( EVO()->cal->get_status_array('front') as $f=>$v){
							$VV[ $v ] = array( $f, evo_lang( $v ) );
						}

						$filter_data[ $vv ]['__name'] = evo_lang('Events Status');
						$filter_data[ $vv ]['__list'] = $VV;

						
					break;

					case 'evotag':
						$tags = $this->get_terms_id_by_post_type( 'post_tag','ajde_events');

						// if no tag terms
						if(count($tags) == 0 ) break;					

						$__list = array('all' => array('all', $__text_all_ ));
						 
						// all event tags
						foreach($tags as $tag){

							// show only set filter values if set
							if($filter_show_set_only && !in_array($tag->term_id, $raw_terms_array) ) 
								continue;

							$__list[ $tag->slug ] = array( (int) $tag->term_id, $tag->name );

						}

						$filter_data[ $vv ]['__name'] = evo_lang('Tag');
						$filter_data[ $vv ]['__filter_type'] = 'tax';
						$filter_data[ $vv ]['__list'] = $__list;

					break;
					
				}

				// other taxonomies
				if( !isset( $filter_data[ $vv ]) ):
					$cats = get_terms( apply_filters('evo_get_frontend_filter_tax',
						array( 	'taxonomy'=> $vv,'hide_empty'=> false,	)
					));

					if( !$cats ) continue;

					$__list = array();
					
					
					// If filter value set to NOT-all > dont show any
						if( in_array('all', $not_values) ) continue;

					// all value
					if(!$filter_show_set_only ){
						// when to select the all value
						$__list['all'] = array('all', $__text_all_ );
					}

					// each taxonomy term
						foreach($cats as $ct){
							// show only set filter values if set & NOT values are empty
								if($filter_show_set_only && !in_array($ct->term_id, $in_values ) && count($not_values) == 0)
									continue;

							// if NOT filter value > skip it
								if( in_array( $ct->term_id, $not_values) ) continue;
							
							// if term is parent level
							$_par = $ct->parent == 0? 'y':'n';

							$term_name = $this->cal->lang('evolang_'.$vv.'_'.$ct->term_id,$ct->name );

							$icon_str = $this->cal->helper->get_tax_icon($vv,$ct->term_id, $this->cal->evopt1 ) ;

							$__list[ $ct->slug ] = array( $ct->term_id, $term_name , $icon_str,$_par);
																
						}
						
					// Language for the taxonomy name text
						$_isthis_ett = (in_array($vv, $_filter_array))? true:false;
						$ett_count = ($ff==1)? '':$ff;

						$lang__ = ($_isthis_ett && isset($this->cal->lang_array['et'.$ett_count]))? 
							$this->cal->lang_array['et'.$ett_count]:
							(!empty($this->cal->lang_array[$ff])? $this->cal->lang_array[$ff]: 
								evo_lang(str_replace('_', ' ', $vv)) );
					
					
					
					$filter_data[ $vv ] = array(
						'__name' => $lang__,
						'__tax'=> $vv,
						'__terms' => $raw_terms_array,
						'__filter_type' =>'tax',
						'__def_val'=> $raw_terms_array,
						'__invals'=> $in_values,
						'__notvals'=>$not_values,
						'__list'=> $__list,
					);
				endif;

				$filter_data[ $vv ]['nterms'] = $filter_data[ $vv ]['__terms'];
				$filter_data[ $vv ]['tterms'] = $filter_data[ $vv ]['__terms'];
				$filter_data[ $vv ]['terms'] = $filter_data[ $vv ]['__terms'];
				unset( $filter_data[ $vv ]['__terms'] );
			}

			return $filter_data;

		}
		public function get_content($args, $sortbar=true){

			$this->SC = $this->cal->SC;
			$help = new evo_helper();

			// define variable values	
				$filtering_options = EVO()->cal->get_prop('evcal_filter_options','evcal_1');
				if( !$filtering_options ) $filtering_options = array();
			
			$content='';

			if(count($filtering_options) == 0) return;

			$this->cal->reused(); // update reusable variables real quikc

			ob_start();

			// argument values
				$SO_display = $this->SC->_is('exp_so') ? 'vis': '';
				$filter_show_set_only =  $this->SC->_is('filter_show_set_only');

			echo "<div class='evo_filter_bar evo_main_filter_bar eventon_sorting_section {$SO_display}'>";

			// 4.6
			do_action('evo_filter_begin', $this->SC );

			$__text_all_ = $this->cal->lang('evcal_lang_all', 'All');
			
			// EACH EVENT TYPE
				$_filter_array = $this->cal->shell->get_all_event_tax();
				$_filter_array = apply_filters('eventon_so_filters', $_filter_array);

			do_action('evo_filter_container_before', $this->SC);

			echo "<div class='evo_filter_container evodfx evo_fx_ai_c'>";

			do_action('evo_filter_container_after', $this->SC);

			echo "<div class='evo_filter_container_in'>";

			do_action('evo_filter_container_in_after', $this->SC);

			echo "<div class='eventon_filter_line' >";

				echo EVO()->elements->get_preload_html( array(
		    		'echo'=> false,
		    		'pclass'=>'loading_filters',
		    		'styles'=>'min-height:30px;',
		    		's'=> array(
		    			array(
		    				'dr'=>'r','gap'=>'10',
		    				array( 'w'=>'100%', 'h'=>'30px','mb'=>'1','m'=>5)
		    			)
		    		)
		    	));
				

				// (---) Hook for addon
				echo  do_action('eventon_sorting_filters', $content);

			echo "</div>"; // #eventon_filter_line	
			echo "</div>"; // #evo_filter_container_in	

			echo "<div class='evo_filter_nav evo_filter_l'><i class='fa fa-chevron-left'></i></div>";
			echo "<div class='evo_filter_nav evo_filter_r'><i class='fa fa-chevron-right'></i></div>";

			echo "</div>"; // #evo_filter_container	
			
			echo "<div class='evo_filter_aply_btns'>";

				// clear filters
				if( $this->SC->_is( 'filter_clear') ){
					echo "<p class='evo_filter_clear'>". 	evo_lang('Clear All')	."</p>";
				}

				// for select filter type
				if( $this->SC->get_prop('filter_type') =='select' ){
					echo "<p class='evo_filter_submit'>".   evo_lang('Apply') 	."</p>";
				}				

			echo "</div>"; // close - evo_filter_aply_btns

			// filter float menu html
			echo "<div class='evo_filter_menu'></div>";
	
			echo "</div>"; // #eventon_sorting_section

			return ob_get_clean();
		}


	// process filter values
		function process_filter_terms( $value = '', $tax = ''){

			$raw_filter_val = $value;

			// preliminary
			$not_values = $in_values = array();

			// run through all passed terms
				foreach(explode(',', $raw_filter_val) as $single_term_id ){
					if( empty($single_term_id)) continue;
					if($raw_filter_val == 'NOT-all' || $value == 'NOT-ALL'){
						$not_values[] = 'all';
						continue;
					}
					if( strpos($single_term_id, 'NOT-')!== false ){
						$not_values[] = str_replace('NOT-', '', $single_term_id);
					}else{
						$in_values[] = $single_term_id;
					}
				}

			// if NOT values passed without IN value > include all
				if( count($not_values)> 0 && count($in_values) == 0 ){
					$in_values[] = 'all';
				}

			// if NOT set to all  > clear in values
				if( in_array('all', $not_values) ){
					$in_values = array();
				}

			// process raw filter value => array format
				if( $raw_filter_val == 'all' ){
					$raw_terms_array = array('all');
				}else{
					$raw_terms_array = array_map('intval', explode(',', $raw_filter_val ) );
				}

			// for past/future which does not use term ids
				if( $tax == 'evpf') $raw_terms_array = $raw_filter_val;

			return array(
				'raw_terms'=> $raw_filter_val,
				'raw_terms_array' => $raw_terms_array,
				'in_values'=> $in_values,
				'not_values'=> $not_values,
			);
		}

	// Apply filters to calendar WP Query arguments
		public function apply_evo_filters_to_wp_argument($wp_arguments){
						
			$SC = $this->cal->shortcode_args;

			$wp_tax_query = $wp_meta_query = array();
			$meta_query_keys = array();
			$skip_query_keys = array('event_past_future', 'event_virtual','event_status');

			// get all available filters from shortcode
				$all_filters = $this->cal->shell->get_all_event_tax();

				foreach($all_filters as $slug=>$name){					
					if(empty($name)) continue;		
					if(in_array($name, $skip_query_keys)) continue;			
					if(!isset($SC[$name])) continue;
					$tax_name = $name == 'event_tag'? 'post_tag':$name;

					$SC_val = $SC[$name];
					$SC_filter_val = apply_filters('eventon_event_type_value', $SC_val, $name, $SC);	

					$terms_array = $values_array = explode(',', $SC_filter_val);
					$terms_array = array_filter( array_unique($terms_array) );

					// if this tax is all > skip it
					if( in_array('all', $terms_array) && count($terms_array) == 1) continue;

					if(in_array($name, $meta_query_keys)){
						$wp_meta_query[] = array(
							'key'=> $name,
							'value'=>$SC_filter_val,
						);
					}else{

						$operator = 'IN';
						$terms = '';
					
						// NOT filter process @updated 4.3.3
						if(strpos($SC_filter_val, 'NOT-')!== false){

							// separate not values 							
							$not_values = $in_values = array();

							// run through all passed terms
								foreach($values_array as $value){
									if( empty($value)) continue;
									if( strpos($value, 'NOT-')!== false ){
										$not_values[] = str_replace('NOT-', '', $value);
									}else{
										$in_values[] = $value;
									}
								}


								$not_values = array_unique($not_values);// remove duplicates

							// not do any terms
							if( in_array('NOT-all', $values_array) || in_array('NOT-ALL', $values_array) ){
								$operator='NOT EXISTS';
								$terms = 'all';
							}else{

								$wp_tax_add = array();

								// for NOT values
								if( count($not_values)>0 ){

									// add to tax query
									$wp_tax_add[] = array(
										'taxonomy'=> $tax_name,
										'field'=> 	'id',
										'terms'=>	$not_values,
										'operator'=>'NOT IN',
									);
								}

								// for IN values
								if( count($in_values)>0 && !in_array('all', $in_values) ){
									
									// add to tax query
									$wp_tax_add[] = array(
										'taxonomy'=> $tax_name,
										'field'=> 	'id',
										'terms'=>	$in_values,
										'operator'=>'IN',
									);
								}

								$wp_tax_add['relation'] = 'AND';

								if( count($in_values)>0 && !in_array('all', $in_values) ){
									$wp_tax_query[] = $wp_tax_add;
								}else{
									$wp_tax_query = array_merge($wp_tax_query, $wp_tax_add);
								}

								continue;
							
							}
						}else{
							$terms = array_filter($terms_array);
						}

						// add to tax query
						$wp_tax_query[] = array(
							'taxonomy'=> $tax_name,
							'field'=> 	apply_filters('eventon_filter_field_type', 'id',$name),
							'terms'=>	$terms,
							'operator'=>$operator,
						);
					}
				}	

				//print_r($wp_tax_query);

			// Append to wp_query
				if(!empty($wp_tax_query)){
					
					$filter_relationship = isset($SC['filter_relationship'])? $SC['filter_relationship']: 'AND';
					$wp_tax_query['relation']= $filter_relationship;

					$filters_tax_wp_argument = array('tax_query'=>$wp_tax_query);					
					$wp_arguments = array_merge($wp_arguments, $filters_tax_wp_argument);
				}
				if(!empty($wp_meta_query)){
					$filters_meta_wp_argument = array(	'meta_query'=>$wp_meta_query	);
					$wp_arguments = array_merge($wp_arguments, $filters_meta_wp_argument);
				}


			return $wp_arguments;
		}

	// APPLY filters to event List
		function apply_filters_to_event_list($event_list, $filter_type='all'){
			$SC = $this->cal->shortcode_args;

			if(!is_array($event_list)) return $event_list;

			// past future event filter			
			if( ($filter_type =='all' || $filter_type=='past_future') && isset($SC['event_past_future']) && $SC['event_past_future'] != 'all'){
				$new_event_list = array();
				if($SC['event_past_future'] == 'past'){							
					foreach($event_list as $event){
						if(isset($event['event_past']) && $event['event_past'] == 'yes') $new_event_list[] = $event;
					}
				}
				if($SC['event_past_future'] == 'future'){
					foreach($event_list as $event){
						if(isset($event['event_past']) && $event['event_past'] == 'no') $new_event_list[] = $event;
					}
				}
				$event_list = $new_event_list;
			}

			// pagination filter
			if( $filter_type =='all' || $filter_type=='pagination'){
				if($SC['show_limit_paged']>0 && 
					$SC['show_limit_ajax']=='yes' && 
					$SC['event_count']>0
				){
					$increment = $SC['event_count'];
					$paged = (int)$SC['show_limit_paged'];
					$bottom = (($paged-1)*$increment);
					$top = ($paged * $increment) ;
					$event_count = count($event_list);

					$index =1;
					foreach($event_list as $id=>$event){
						//echo "$index > $top && < $bottom -{$event['event_id']}<br/>";
						if($index <= $top && $index > $bottom){
						}else{
							unset($event_list[$id]);
						}
						$index++;
					}
				}
			}

			// event count filter
			if( $filter_type=='event_count' || $filter_type =='all' ){
				
				// make sure event count is only run for one month
				if(isset($SC['number_of_months']) && $SC['number_of_months'] >1) return $event_list;
				
				if(isset($SC['event_count']) && $SC['event_count'] >0){
					// if show limit then show all events but css hide
					if(!empty($SC['show_limit']) && $SC['show_limit']=='yes'){
						$lesser_of_count = count($event_list);
					}else{
						// make sure we take lesser value of count
						$lesser_of_count = (count($event_list)<$SC['event_count'])?
							count($event_list): $SC['event_count'];
					}

					// for each event until count
					$index =1;
					foreach($event_list as $id=>$event){
						if($index > $lesser_of_count){						
							unset($event_list[$id]);
						}
						$index++;
					}					
				}
			}

			return $event_list;
		}

	// pre filter featured events top and month/year long events top
		function move_important_events_up( $EL){
			$EL = $this->move_ft_to_top( $EL);
			$EL = $this->move_ml_yl_to_top( $EL);
			return $EL;
		}

	// process events list for no events or load more
		function no_more_events_add( $EL){
			$SC = $this->cal->shortcode_args;
			$content_li='';


			// if there are events in the list array
			if( is_array($EL) && count($EL)>0){

				// print all the events
				foreach($EL as $event)	$content_li.= $event['content'];


				// load more events button
				if( isset($SC['show_limit']) && $SC['show_limit']=='yes' && 
					((count($EL)> $SC['event_count'] && $SC['show_limit_ajax']=='no' ) || ($SC['show_limit_ajax'] =='yes')
					) ){
					$content_li.= '<div class="evoShow_more_events" style="'.( $SC['tile_height']!=0? 'height:'.$SC['tile_height'].'px':'' ).'"><span>'.$this->cal->lang_array['evsme'].'</span></div>';
				}
			}else{
				$HELP = new evo_cal_help();
				if( ($SC['sep_month'] == 'yes' && $SC['number_of_months']>1 )|| $SC['number_of_months'] ==1 ){
					$content_li = "<div class='eventon_list_event no_events'>";
					$content_li .= $HELP->get_no_event_content();
					$content_li .=  "</div>";
				}
					
			}

			return $content_li;
		}

	// Other secondary filtering
		function move_ft_to_top($eventlist){
			$args = $this->cal->shortcode_args;
			if($args['ft_event_priority']=='yes' ){

				$ft_events = $events = array();
				foreach($eventlist as $event){

					$featured = (isset($event['event_pmv']['_featured']) && $event['event_pmv']['_featured'][0]=='yes')? true:false;

					if($featured){
						$ft_events[]=$event;
					}else{
						$events[]=$event;
					}
				}

				// move featured events to top
				return array_merge($ft_events,$events);
			}
			return $eventlist;
		}

		// @u 4.5.6
		function move_ml_yl_to_top($eventlist){
			$args = $this->cal->shortcode_args;

			$ml_events = $yl_events = $events = array();

			// if no events
				if( count($eventlist) == 0) return $eventlist;

			foreach($eventlist as $event){

				if( $event['etx_type'] == 'ml' ){
					$ml_events[] = $event;
				}elseif(  $event['etx_type'] == 'yl' ){
					$yl_events[] = $event;
				}else{
					$events[] = $event;
				}
			}
			

			if(isset($args['ml_priority']) && $args['ml_priority']=='yes' ){
				
				// move featured events to top
				return array_merge($ml_events,$events);
			}

			if(isset($args['yl_priority']) && $args['yl_priority']=='yes' ){

				// move featured events to top
				return array_merge($yl_events,$events);
			}

			// if move month long events to the bottom
			if(isset($args['ml_toend']) && $args['ml_toend']=='yes' ){
				
				// move featured events to top
				return array_merge( $events , $ml_events);
			}
			// if move year long events to the bottom
			if(isset($args['yl_toend']) && $args['yl_toend']=='yes' ){
				
				// move featured events to top
				return array_merge( $events , $yl_events);
			}

			return $eventlist;
		}
}