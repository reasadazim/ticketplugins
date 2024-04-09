<?php	
/*
 *	The template for displaying lightbox event location 
 * 	In order to customize this archive page template
 *	Override this template by coping it to ../yourtheme/eventon/templates/ folder
 
 *	@Author: AJDE
 *	@EventON
 *	@version: 4.6
 */	

//print_r($temp_data);

$TAX = new EVO_Tax();
$taxonomy_slug = 'event_location';

?>
<div class='evo_taxlb_more pad50 evotax_term_card evo_location_card'>

	<div class='evo_taxlb_header padb50 evotac'>
		<h2 class='evo_h2 padb5'><?php echo $temp_data->name;?></h2>
		<div class='evo_tax_base_details marb20'>
			<p class='evo_taxlb_contacts mar0 padt10 evodfx evogap10 evo_fx_jc_c evofx_ww'>
				<?php if(!empty($temp_data->location_address)):?>
					<span class='marr10'><i class='fa fa-map-marker marr5'></i> <?php echo $temp_data->location_address;?></span>
				<?php endif;?>
				<?php if(!empty($temp_data->loc_phone)):?>
					<span class='marr10'><i class='fa fa-phone marr5'></i> <?php echo $temp_data->loc_phone;?></span>
				<?php endif;?>
				<?php if(!empty($temp_data->loc_email)):?>
					<span class='marr10'><i class='fa fa-envelope marr5'></i> <?php echo $temp_data->loc_email;?></span>
				<?php endif;?>
			</p>
		</div>
	</div>

	<div class='evo_taxlb_main evodfx'>		
		<?php

		ob_start();

		// location images
			do_action('evo_taxlb_images', $TAX, $taxonomy_slug, $temp_data->location_img_id );			

		//description
			if( !empty( $temp_data->description )):
				?>
				<div class='tax_term_description padt20 padb20'>
					<?php echo $temp_data->description;?>								
				</div>
			<?php endif;?>
			
		<?php 

		// social share
			do_action('evo_taxlb_social_share', $TAX, $temp_data);
			
		
		// google map
			do_action('evo_taxlb_google_map', $taxonomy_slug, $temp_data);
			
		// related locations
			if( !empty( $temp_data->loc_rel)){
				$term_ids = explode(',', $temp_data->loc_rel);
				
				echo "<div class='evo_taxlb_rel mart40 '>";
				echo "<h2 class='evo_h2' style='font-size:24px;'>". evo_lang('Related Locations') . "</h2>";
				echo "<p class='' style='margin:0; padding:0 0 5px'>" . evo_lang('Find upcoming events on these locations') . "</p>";
				
				do_action('evo_taxlb_related_terms', $taxonomy_slug , $TAX, $term_ids );

				echo "</div>";
				
			}

		$right_content = ob_get_clean();

		?>

		<?php if(!empty( $right_content ) && $right_content != ' '):?><div class='evo_taxlb_l'><?php echo $right_content;?></div><?php endif;?>
		<div class='evo_taxlb_r'>

			<?php do_action('evo_taxlb_upcoming_events', $taxonomy_slug, $temp_data); ?>	

		</div>

	</div>

</div>