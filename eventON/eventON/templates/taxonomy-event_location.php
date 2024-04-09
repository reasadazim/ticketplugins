<?php	
/*
 *	The template for displaying event categoroes - event location 
 * 	In order to customize this archive page template
 *	Override this template by coping it to ../yourtheme/eventon/ folder
 
 *	@Author: AJDE
 *	@EventON
 *	@version: 4.6.1
 */	
	

	evo_get_page_header();

	$help = new evo_helper();

	$taxonomy = get_query_var( 'taxonomy' );
	$term = get_query_var( 'term' );
	$term = get_term_by( 'slug', $term, $taxonomy );

	$TAX = new EVO_Tax();

	do_action('eventon_before_main_content');
	
	$temp_data = $TAX->get_term_data( $taxonomy, $term->term_id); 


	// location link
		$location_link_target = $location_term_name = $location_term_link = '';
		if( !empty( $temp_data->location_link ) ){
			$location_link_target = (!empty($temp_data->location_link_target) && $temp_data->location_link_target == 'yes')? '_blank':'';

			$location_term_link = $temp_data->location_link;

			$location_term_name = $location_term_link ? 
				'<a target="'.$location_link_target.'" href="'. $location_term_link .'">' .  $term->name . '</a>':
				 $term->name;
		}

?>

<div class='wrap evotax_term_card evo_location_card alignwide'>	
	<div class='evo_card_wrapper'>	

		<div id='' class="content-area">

			<div class='eventon site-main'>

				<header class='page-header'>
					<h1 class="page-title"><?php evo_lang_e('Events at this location');?></h1>
				</header>

				<div class='entry-content'>
					
					<div class='evo_term_top_section dfx evofx_dr_r evogap10 evomarb10'>

						<?php 

						// image
						do_action('evo_taxlb_images', $TAX, $taxonomy, $temp_data->location_img_id );	


						// details
						?>
						<div class='evo_tax_details'>
							<h2 class="location_name tax_term_name evo_h2 ttu"><span><?php echo $location_term_name;?></span></h2>
							
							<p class='evo_taxlb_contacts mar0 padt10 evodfx evogap10 evofx_jc_fs evofx_ww'>
								<?php if(!empty($temp_data->location_address)):?>
									<span class="marr10"><i class='fa fa-map-marker marr10'></i> <?php echo $temp_data->location_address;?></span>
								<?php endif;?>
								<?php if(!empty($temp_data->loc_phone)):?>
									<span class='marr10'><i class='fa fa-phone marr5'></i> <?php echo $temp_data->loc_phone;?></span>
								<?php endif;?>
								<?php if(!empty($temp_data->loc_email)):?>
									<span class='marr10'><i class='fa fa-envelope marr5'></i> <?php echo $temp_data->loc_email;?></span>
								<?php endif;?>
							</p>

							
							<?php // description
							if( !empty( $temp_data->description )):?>
							<div class='location_description tax_term_description mart15 marb15'>
								<?php echo $temp_data->description;?>								
							</div>
							<?php endif;

							// social share
							do_action('evo_taxlb_social_share', $TAX, $temp_data);
							?>

							<?php if( $location_term_link):?>
								<p class='pad0 mart10'><a class='evo_btn evcal_btn' href='<?php echo $location_term_link;?>' target='<?php echo $location_link_target;?>'><?php evo_lang_e('Learn More');?></a></p>
							<?php endif;?>
						</div>

					</div>
					
					<?php 

					// google map
						do_action('evo_taxlb_google_map', $taxonomy, $temp_data);
					?>


					<div class='evo_term_events'>
						<?php do_action('evo_taxlb_upcoming_events', $taxonomy, $temp_data); ?>	
					</div>

					<?php 
					// related locations
					if( !empty( $temp_data->loc_rel)){
						$term_ids = explode(',', $temp_data->loc_rel);
						
						echo "<div class='evo_taxlb_rel mart40 '>";
						echo "<h2 class='evo_h2' style='font-size:24px;'>". evo_lang('Related Locations') . "</h2>";
						echo "<p class='' style='margin:0; padding:0 0 5px'>" . evo_lang('Find upcoming events on these locations') . "</p>";
						
						do_action('evo_taxlb_related_terms', $taxonomy , $TAX, $term_ids );

						echo "</div>";
						
					}
					?>

				</div>
			</div>
		</div>

		<?php evo_get_page_sidebar(); ?>
	</div>
</div>

<?php	do_action('eventon_after_main_content'); ?>

<?php 	evo_get_page_footer(); ?>