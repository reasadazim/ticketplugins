<?php
/**
 *	EventON Template functions for template system
 *	@version 4.6
 */

defined( 'ABSPATH' ) || exit;


add_action('eventon_before_header','evotemp_before_header');
add_action('eventon_before_main_content','evotemp_before_main_content');
add_action('eventon_single_content_wrapper','evotemp_content_wrapper');

add_action('eventon_single_after_loop','evotemp_after_loop');

add_action('eventon_before_single_event','evotemp_before_single_event');
add_action('eventon_before_event_content','evotemp_before_single_event_content');
add_action('eventon_before_single_event_summary','evotemp_before_single_event_summary');
add_action('eventon_single_event_summary','evotemp_single_event_summary');

add_action('eventon_after_main_content','evotemp_after_main_content');
add_action('eventon_single_after_loop','evotemp_single_event_after_loop');
add_action('eventon_after_event_content','evotemp_after_event_content');
add_action('eventon_after_single_event_summary','evotemp_after_single_event_summary');
add_action('eventon_after_single_event','evotemp_after_single_event');


add_action('eventon_arch_before_header','evoarch_before_header');
add_action('eventon_arch_main_content','evoarch_main_content');
add_action('eventon_arch_after_content','evoarch_after_content');

// Tax Lightbox 4.6
	add_action('evo_taxlb_social_share', 'evo_taxlb_social_share', 10, 2);
	function evo_taxlb_social_share( $TAX, $temp_data){

		$ss_content = '';
		
		foreach( $TAX->get_social_sites() as $key ){

			if( empty( $temp_data->{$key} )) continue;

			$link = $temp_data->{$key};
			$link = strpos($link, 'http') === false ? 'https://'. $link : $link;

			$ss_content .= "<a class='evo_ss evo_wbg marr10' href='{$link}' target='_blank'><i class='fa fa-{$key}'></i></a>";

		}

		if( !empty( $ss_content )) 
			echo "<div class='evo_taxlb_social_share_bar padb20 evodfx evofx_ww evogap10'>{$ss_content}</div>";
	}
	add_action('evo_taxlb_related_terms', 'evo_taxlb_related_terms', 10, 3);
	function evo_taxlb_related_terms( $taxonomy_slug , $TAX, $term_ids ){
		$help = new evo_helper();

		foreach($term_ids as $id){

			if( empty($id)) continue;

			$term_data = $TAX->get_term_data( $taxonomy_slug, $id );

			//print_r($term_data);
			$btn_data = array(
				'lbvals'=> array(
					'lbc'=>'evo_'.$taxonomy_slug.'_lb_'.$id,
					'lbac'=>'lb_max',
					't'=>	$term_data->name,
					'ajax'=>'yes',
					'ajax_type'=>'endpoint',
					'ajax_action'=>'eventon_get_tax_card_content',
					'end'=>'client',
					'd'=> array(	
						'term_id'=> $id,
						'tax'=> $taxonomy_slug,
						'load_lbcontent'=>true
					)
				)
			);

			echo "<a class='evolb_trigger mart10 pad20 evobr15 evodb evo_curp evotdn evo_hover_op7' style='background-color: var(--evo_cl_b5' ". $help->array_to_html_data( $btn_data ).">";
			echo "<h3 class='evo_h3' style='padding:0 0 5px'>". $term_data->name . "</h3>";
			
			if( !empty( $term_data->termmeta ) && isset( $term_data->termmeta['location_address']))
				echo "<p class='' style='margin:0; padding:0'>". $term_data->termmeta['location_address'] . "</p>";

			echo "</a>";
		}
	}

	add_action('evo_taxlb_images', 'evo_taxlb_images', 10, 3);
	function evo_taxlb_images($TAX, $taxonomy_slug, $term_img_ids){
		$img_urls = $TAX->process_tax_img_from_data( $term_img_ids );

		if( count($img_urls)> 0):

			$fullheight = (int)EVO()->calendar->get_opt1_prop('evo_locimgheight',400);

			?>
			<div class='evo_gal_box evoposr <?php echo $taxonomy_slug;?> evopadb20'>
				<div class='evo_gal_main_img borderr25' style='height:100%;min-height:<?php echo $fullheight;?>px; background-image:url(<?php echo $img_urls[1][0];?>)'></div>
				
				<div class='evo_gal_bottom evoposa'>
					<div class='evo_gal_icons'>
					<?php 
					foreach( $img_urls as $index => $d){
						echo "<div class='evo_gal_icon ". ($index == 1 ? 'on':'') ." evo_transit_all evo_trans_sc1_05 evo_curp' data-index='{$index}' data-u='{$d[0]}'>
							<span class='' style='background-image:url(".$d[1].")'></span>
						</div>";
					}
					?>
					</div>
				</div>
			</div>
		<?php 
		endif;
	}

	add_action('evo_taxlb_upcoming_events', 'evo_taxlb_upcoming_events', 10, 2);
	function evo_taxlb_upcoming_events( $taxonomy_slug, $temp_data){
		?>
		<h3 class="evotax_term_subtitle "><?php evo_lang_e('Upcoming Events');?></h3>						
		<?php 

		$eventtop_style = EVO()->cal->get_prop('evosm_eventtop_style','evcal_1') == 'white'? '0':'2';

		$shortcode = apply_filters('evo_tax_archieve_page_shortcode', 
			'[add_eventon_list number_of_months="5" '.$taxonomy_slug.'='.$temp_data->term_id.' hide_mult_occur="no" hide_empty_months="yes" eventtop_style="'. $eventtop_style.'" ux_val="3a"]', 
			$taxonomy_slug,
			$temp_data->term_id
		);
		echo do_shortcode($shortcode);
		
	}
	add_action('evo_taxlb_google_map', 'evo_taxlb_google_map', 10, 2);
	function evo_taxlb_google_map( $taxonomy_slug, $temp_data){

		$address = false;

		if( $taxonomy_slug == 'event_organizer' ){
			if( empty( $temp_data->organizer_address )) return;
			$address = $temp_data->organizer_address;
		}
		if( $taxonomy_slug == 'event_location' ){
			if( empty( $temp_data->location_address )) return;
			$address = $temp_data->location_address;
		}

		$help = new evo_helper();

		EVO()->cal->set_cur('evcal_1');
		$zoomlevel = EVO()->cal->get_prop('evcal_gmap_zoomlevel');
			if(!$zoomlevel) $zoomlevel = 16;

		$map_type = EVO()->cal->get_prop('evcal_gmap_format');
			if(!$map_type) $map_type = 'roadmap';

		
		$latlon = !empty( $temp_data->location_lat ) && !empty( $temp_data->location_lon ) ? 
			$temp_data->location_lat .','.$temp_data->location_lon :'';
		$loc_type = !empty( $temp_data->location_type ) ? $temp_data->location_type : 'add';

		$map_data = array(
			'address'=> stripslashes( $address ),
			'latlng'=> $latlon,
			'location_type'=> $loc_type,
			'zoom'=> $zoomlevel,
			'scroll'=> EVO()->cal->check_yn('evcal_gmap_scroll')? 'no':'yes',
			'mty'=>$map_type,
			'delay'=>400
		);
	?>
	<div id='evo_<?php echo $taxonomy_slug;?>_term_<?php echo $temp_data->term_id;?>' class="evo_trigger_map evo_location_map term_location_map evobr15" <?php echo $help->array_to_html_data($map_data);?>><?php echo EVO()->elements->get_preload_map();?></div>
		<?php
	}

// Archive page function 
	function evoarch_before_header(){
		if( !evo_current_theme_is_fse_theme() )  get_header('events');

		wp_enqueue_style( 'evo_single_event');		
		EVO()->frontend->load_evo_scripts_styles();		
	}
	function evoarch_main_content(){
		$archive_page_id = evo_get_event_page_id();

		// check whether archieve post id passed
		if($archive_page_id){

			$archive_page  = get_page($archive_page_id);	
			
			echo "<div class='wrapper evo_archive_page'>";

			do_action('evo_event_archive_page_before_content');

			echo apply_filters('the_content', $archive_page->post_content);

			do_action('evo_event_archive_page_after_content');

			echo "</div>";

		}else{
			echo "<p>ERROR: Please select a event archive page in eventON Settings > Events Paging > Select Events Page</p>";
		}
	}
	function evoarch_after_content(){
		if( !evo_current_theme_is_fse_theme() ) get_footer('events');
	}

// Other templates
add_filter( 'post_class', 'evo_event_post_class', 30, 3 );
function evo_event_post_class($class='', $event = null){
	global $post, $event;
	
	if( $post->post_type == 'ajde_events' && !empty($event)){
		$class[] = 'evo_event_content';
		$class[] = $event->ID;
	}
	
	return $class;
}

function evotemp_before_header(){
	global $post, $wp_query;

	$RI = 0;
	$L = 'L1';

	if( isset($_GET['ri']))	$RI = (int)$_GET['ri'];
	if( isset($_GET['l'])) $L = sanitize_text_field( $_GET['l'] );

	$EVENT = evo_setup_event_data( $post, $RI);

	//print_r($EVENT->get_start_end_times() );

	
	// support passing URL like ..../var/ri-2.l-L2/

	// addition to default to current repeat event
		if( EVO()->cal->check_yn('evosm_rep_cur_def','evcal_1') ){
			if(!isset($wp_query->query['var']) ){
				$wp_query->query['var'] = 'ri-current.l-L1';
			}
		}
		

		if(isset($wp_query->query["var"])){
			$_url_var = $wp_query->query["var"];
			
			$url_var = explode('.', $_url_var);
			$vars = array();
			
			foreach($url_var as $var){
				$split = explode('-', $var);

				switch ($split[0]) {
					case 'ri':
						$RI = (int)$split[1];
						$EVENT->ri = $RI;	

						// if RI passed as current
						if( !is_numeric( $split[1]) ){
							$out = $EVENT->get_next_current_repeat(1, 'start', $split[1]);

							if( $out && isset($out['ri'])){
								$EVENT->ri = $out['ri'];			
							}
						}
					break;

					case 'l':
						$L = $split[1];
					break;
				}
				
			}

			evo_set_global_lang($L); // set global language

			// virtual event access
			if($_url_var == 'event_access'){					
				
				$vir_url = $EVENT->virtual_url('direct');

				if($vir_url){
					wp_redirect( $vir_url ); exit;
				} 
			}

		}

	if( !evo_current_theme_is_fse_theme() )  get_header('events');
}

function evotemp_before_main_content(){
	wp_enqueue_style( 'evo_single_event');		
	EVO()->frontend->load_evo_scripts_styles();		

}

	// when the post is called put event data into global
	function evo_setup_event_data( $post, $RI=0){

		unset( $GLOBALS['event'] );

		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		if ( empty( $post->post_type ) || ! in_array( $post->post_type, array( 'ajde_events' ), true ) ) {
			return;
		}

		$GLOBALS['event'] = $EVENT = new EVO_Event($post->ID, '', $RI , true, $post);

		//print_r(  $EVENT->get_translated_datetime( 'F,Y' ,$EVENT->start_unix ) );

		return $GLOBALS['event'];
	}
// Single events
	function evotemp_content_wrapper(){

		?>
		<div class='evo_page_content <?php echo EVO()->cal->check_yn('evosm_1','evcal_1') ? 'evo_se_sidarbar':null;?>'>
		<?php
	}
	function evotemp_after_loop(){}
	function evotemp_after_main_content(){
		if( !evo_current_theme_is_fse_theme() ) get_footer('events');
	}
	function evotemp_before_single_event(){}
	function evotemp_before_single_event_content(){

		global $event;

		// if password protected event
		//if( $event->is_password_required() ) echo 'Password Protected event';


		$rtl = EVO()->cal->check_yn( 'evo_rtl','evcal_1');

		$event_id = get_the_ID();
		$json = apply_filters('evo_event_json_data',array(), $event_id);

		// eventtop style
		$eventtop_style = EVO()->cal->get_prop('evosm_eventtop_style');
		if(!$eventtop_style) $eventtop_style = 'immersive';

		?>
		<div id='evcal_single_event_<?php echo get_the_ID();?>' class='ajde_evcal_calendar eventon_single_event evo_sin_page<?php echo ($rtl?'evortl':'') .' '. $eventtop_style;?>' data-eid='<?php echo $event_id;?>' data-l='<?php echo EVO()->lang;?>' data-j='<?php echo json_encode($json);?>'>
		<?php

		// event data 
		$event_map_data = $event->get_event_data_for_gmap();
		$help = new evo_helper();

		// deprecating ?><div class='evo-data' <?php echo $help->array_to_html_data( $event_map_data );?>></div>
		<div class='evo_cal_data' data-sc='<?php echo json_encode($event_map_data);?>'></div>
				<?php

		// calendar month header
		$repeati = $event->ri;
		$lang = EVO()->lang;	

		$formatted_time = eventon_get_formatted_time( $event->get_event_time() , $event->tz );	
		$header_text =  get_eventon_cal_title_month($formatted_time['n'], $formatted_time['Y'], $lang);

		
		// if show month year header
		if( EVO()->cal->check_yn('evosm_show_monthyear')):
			?><div id='evcal_head' class='calendar_header'><p id='evcal_cur'><?php echo $header_text;?></p></div><?php
		endif;
	}


	function evotemp_before_single_event_summary(){}

	function evotemp_single_event_summary(){
		global $event;

		// eventtop style
		$eventtop_style = EVO()->cal->get_prop('evosm_eventtop_style');
		if(!$eventtop_style) $eventtop_style = 'immersive';



		$single_events_args = apply_filters('eventon_single_event_page_data',array(
			'etc_override'=>'no',
			'eventtop_style'=> ($eventtop_style == 'color'? 2:0),
			'eventtop_layout_style'=> EVO()->cal->get_prop('evosm_eventtop_layout_style'),
			'show_et_ft_img'=>'yes', // @4.5.5
		));

		// override event color on page with event typ color @since 4.3
		if( EVO()->cal->check_yn('evosm_etc_override') ) $single_events_args['etc_override'] = 'yes';

		$content =  EVO()->calendar->get_single_event_data( $event->ID, EVO()->lang, $event->ri, $single_events_args);		

		// login only access
		$thisevent_onlylogged_cansee = $event->check_yn('_onlyloggedin');
		
		// pluggable access restriction to event
			$continue_with_page_content = apply_filters('evo_single_page_access', true , $thisevent_onlylogged_cansee);

		// stop single event page from loading without access 4.6
		if( !$continue_with_page_content ) return;

		if( $thisevent_onlylogged_cansee && !is_user_logged_in() ):

			echo "<p class='evo_single_event_noaceess'>".evo_lang('You must login to see this event')."<br/><a class='button' href=". 
			wp_login_url( $event->get_permalink() ) ." title='".evo_lang('Login')."'>".evo_lang('Login')."</a></p>";

		else:

			// repeat header
			echo $event->get_repeat_header_html();

			if( !EVO()->cal->check_yn('evosm_hide_title') ):?><h1 class='evosin_event_title'>
				<?php echo $event->get_title(); ?></h1><?php
			endif;
				
			echo isset($content[0]) ? $content[0]['content'] : '';
		endif;
	}
	function evotemp_after_event_content(){

		// comments section
		if( !EVO()->cal->check_yn('evosm_comments_hide')){
			
			if( !evo_current_theme_is_fse_theme() ):			
			?>
			<div id='eventon_comments'><?php comments_template( '', true );	?></div>
			<?php
			endif;
		}

		?></div><!---ajde_evcal_calendar--><?php 
	}
	function evotemp_after_single_event_summary(){}
	function evotemp_single_event_after_loop(){

		// side bar
		if(EVO()->cal->check_yn( 'evosm_1','evcal_1')){
			if ( is_active_sidebar( 'evose_sidebar' ) ){

				if( !evo_current_theme_is_fse_theme() ):
				?>
				<div class='evo_page_sidebar'>
					<ul id="sidebar">
						<?php dynamic_sidebar( 'evose_sidebar' ); ?>
					</ul>
				</div>
				<?php
				endif;
			}
		}
		?></div><!-- evo_page_content--><?php
	}
	function evotemp_after_single_event(){}

// General
function evo_get_page_header(){
	if( !evo_current_theme_is_fse_theme() )	get_header();
}
function evo_get_page_footer(){
	if( !evo_current_theme_is_fse_theme() )	get_footer();
}
function evo_get_page_sidebar(){
	if( !evo_current_theme_is_fse_theme() ){
		echo "<div class='evo_sidebar'>";
		get_sidebar();
		echo "</div>";
	}
}