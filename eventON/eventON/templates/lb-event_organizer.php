<?php	
/*
 *	The template for displaying lightbox event organizer 
 * 	In order to customize this archive page template
 *	Override this template by coping it to ../yourtheme/eventon/templates/ folder
 
 *	@Author: AJDE
 *	@EventON
 *	@version: 4.6
 */	

//print_r($temp_data);

$TAX = new EVO_Tax();
$taxonomy_slug = 'event_organizer';

?>
<div class='evo_taxlb_more pad50 evopad0_sm evotax_term_card evo_organizer_card'>

	<div class='evo_taxlb_header padb50 evotac'>
		<h2 class='evo_h2 padb5'><?php echo $temp_data->name;?></h2>
		<div class='evo_tax_base_details marb20'>
			<p class='evo_taxlb_contacts mar0 padt10 evodfx evogap10 evo_fx_jc_c evofx_ww'>
				<?php if(!empty($temp_data->organizer_address)):?>
					<span class='marr10'><i class='fa fa-map-marker marr5'></i> <?php echo $temp_data->organizer_address;?></span>
				<?php endif;?>
				<?php if(!empty($temp_data->organizer_contact)):?>
					<span class='marr10'><i class='fa fa-phone marr5'></i> <?php echo $temp_data->organizer_contact;?></span>
				<?php endif;?>
				<?php if(!empty($temp_data->contact_email)):?>
					<span class='marr10'><i class='fa fa-envelope marr5'></i> <?php echo $temp_data->contact_email;?></span>
				<?php endif;?>
				<?php if(!empty($temp_data->organizer_link)):?>
					<span class='marr10'><i class='fa fa-link marr5'></i> <a href='<?php echo $temp_data->organizer_link;?>' target='_blank'><?php echo $temp_data->organizer_link;?></a></span>
				<?php endif;?>
			</p>
		</div>
	</div>

	<div class='evo_taxlb_main evodfx'>
		<div class='evo_taxlb_l'>
			<?php

			// organizer images
				do_action('evo_taxlb_images', $TAX, $taxonomy_slug, $temp_data->organizer_img_id );				

			//description
				if( !empty( $temp_data->description_full )):?>
				<div class='tax_term_description padt20 padb20'>
					<?php echo $temp_data->description_full;?>								
				</div>
				<?php 	endif;

			// social share
				do_action('evo_taxlb_social_share', $TAX, $temp_data);

			// google map
			do_action('evo_taxlb_google_map', $taxonomy_slug, $temp_data);
			
			?>			

			<?php
			// related organizers
				if( !empty( $temp_data->org_rel)){
					$term_ids = explode(',', $temp_data->org_rel);
					
					echo "<div class='evo_taxlb_rel mart40 '>";
					echo "<h2 class='evo_h2' style='font-size:24px;'>". evo_lang('Related Organizers') . "</h2>";
					echo "<p class='' style='margin:0; padding:0 0 5px'>" . evo_lang('Find upcoming events from these organizers') . "</p>";
					
					do_action('evo_taxlb_related_terms', $taxonomy_slug , $TAX, $term_ids );

					
					echo "</div>";
					
				}
			?>		
			
		</div>
		<div class='evo_taxlb_r'>

			<?php do_action('evo_taxlb_upcoming_events', $taxonomy_slug, $temp_data); ?>			

		</div>

	</div>

</div>