<?php	
/*
 *	The template for displaying event categoroes - event organizer 
 *
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


	// organizer link
		$link_target = $term_name = $term_link = '';
		if( !empty( $temp_data->organizer_link ) ){
			$link_target = (!empty($temp_data->organizer_link_target) && $temp_data->organizer_link_target == 'yes')? '_blank':'';

			$term_link = $temp_data->organizer_link;

			$term_name = $term_link ? 
				'<a target="'.$link_target.'" href="'. $term_link .'">' .  $term->name . '</a>':
				 $term->name;
		}

?>

<div class='wrap evotax_term_card evo_organizer_card alignwide'>
	<div class='evo_card_wrapper'>	

		<div id='' class="content-area">

			<div class='eventon site-main'>
				<header class='page-header'>
					<h1 class="page-title"><?php evo_lang_e('Events by this organizer');?></h1>
				</header>

				<div class='entry-content'>
					<div class='evo_term_top_section dfx evofx_dr_r evogap10 evomarb10'>
						
						<?php 
						// image
						do_action('evo_taxlb_images', $TAX, $taxonomy, $temp_data->organizer_img_id );	
						?>	

						<div class='evo_tax_details'>
							<h2 class="tax_term_name organizer_name evo_h2 ttu"><span><?php echo $term_name;?></span></h2>		
							<?php 
							//description
							if( !empty( $temp_data->description_full )):?>
								<div class='tax_term_description padt20 padb20'>
									<?php echo $temp_data->description_full;?>								
								</div>
							<?php 	endif;?>

							<p class='evo_taxlb_contacts mar0 padt10 evodfx evogap10 evofx_jc_fs evofx_dr_c'>

								<?php if(!empty($temp_data->organizer_contact)):?>
									<span class='evodb padt10 padb10 border'><i class='fa fa-phone marr5'></i> <?php echo $temp_data->organizer_contact;?></span>
								<?php endif;?>
								<?php if(!empty($temp_data->contact_email)):?>
									<span class='evodb padt10 padb10 border'><i class='fa fa-envelope marr5'></i> <?php echo $temp_data->contact_email;?></span>
								<?php endif;?>
								<?php if(!empty($temp_data->organizer_address)):?>
									<span class='evodb padt10 padb10'><i class='fa fa-map-marker marr5'></i> <?php echo $temp_data->organizer_address;?></span>
								<?php endif;?>
							</p>
							<?php 


							// social share
							do_action('evo_taxlb_social_share', $TAX, $temp_data);
							?>			

							<?php if( $term_link):?>
								<p class='mar0 pad0'><a class='evo_btn evcal_btn' href='<?php echo $term_link;?>' target='<?php echo $link_target;?>'><?php evo_lang_e('Learn More');?></a></p>
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
					// related organizers
						if( !empty( $temp_data->org_rel)){
							$term_ids = explode(',', $temp_data->org_rel);
							
							echo "<div class='evo_taxlb_rel mart40 '>";
							echo "<h2 class='evo_h2' style='font-size:24px;'>". evo_lang('Related Organizers') . "</h2>";
							echo "<p class='' style='margin:0; padding:0 0 5px'>" . evo_lang('Find upcoming events from these organizers') . "</p>";
							
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