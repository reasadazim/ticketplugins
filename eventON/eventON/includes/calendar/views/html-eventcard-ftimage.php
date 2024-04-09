<?php 
/**
 * EventON event featured image
 * @version 4.6.1
 */

$__hoverclass = (!empty($object->hovereffect) && $object->hovereffect!='yes')? ' evo_imghover':null;
$__noclickclass = (!empty($object->clickeffect) && $object->clickeffect=='yes')? ' evo_noclick':null;
$__zoom_cursor = (!empty($evOPT['evo_ftim_mag']) && $evOPT['evo_ftim_mag']=='yes')? ' evo_imgCursor':null;

EVO()->cal->set_cur('evcal_1');

// main image
	if( $object->main_image && is_array($object->main_image)):

		//print_r($object);

		$IMG = $BGURL = '';
		$main_image = $object->main_image;

		$height = !empty($object->img[2])? $object->img[2]:'';
		$width = !empty($object->img[1])? $object->img[1]:'';

		$new_width = 0;

		$img_hw_ratio = $main_image['full_h'] / $main_image['full_w'];

		// minimum height
		$__height = EVO()->cal->get_prop('evo_ftimgheight');
		if( !$__height) $__height = 400;

		// image style 
		$img_sty = EVO()->cal->get_prop('evo_ftimg_height_sty');
		if( !$img_sty) $img_sty = 'def';


		if( $img_sty != 'def') $IMG = "<span style='height:{$__height}px; background-image:url({$object->img})'></span>";
		if( $img_sty == 'def') $BGURL = $object->img;
		
		
		$_mi_data = array(
			'f'=>  $object->img,
			'h'=> $main_image['full_h'],
			'w'=> $main_image['full_w'],
			'ratio'=> $img_hw_ratio,
			'event_id'=>$EVENT->ID,
			'ri'=>$EVENT->ri
		);

		echo "<div class='evocard_main_image evobr15 evobgsc evobgpc evodfx evofx_jc_c evofx_ai_c evofz48 {$img_sty}' style='height:{$__height}px; background-image:url({$BGURL});' ". $this->helper->array_to_html_data( $_mi_data ) .">{$IMG}</div>";

	endif;

// additional images
	$adds = $EVENT->get_prop('_evo_images');

	if( apply_filters('evo_eventcard_additional_images', $adds, $object, $EVENT) ){

		do_action('evo_eventcard_ftimage_before_gal', $EVENT);

		echo "<div class='evocard_fti_in evow100p evobot0 evoposa '>";

		do_action('evo_eventcard_ftimage_gal_1', $EVENT);

		echo "<div class='evo_event_images evogap10 evodfx evofx_jc_c evofx_ww'>";

		$data = apply_filters('evo_one_ftimage_data', array(
			'f'=> $object->img,
			'h'=> $main_image['full_h'],
			'w'=> $main_image['full_w'],
			'i'=> '0'
		), $object->img_id , $EVENT );

		echo "<span class='evo_event_more_img select evobr15 evobrdB2 evocurp evobgsc  evo_trans_sc1_05' style='background-image:url({$object->img})' ". $this->helper->array_to_html_data( $data ) ."></span>";
		

		$imgs = explode(',', $adds);
		$imgs = array_filter($imgs);

		$x = 1;
		foreach($imgs as $img){

			$caption = get_post_field('post_excerpt',$img);
			$thumb = wp_get_attachment_image_src($img);
			$full = wp_get_attachment_image_src($img,'full');

			$data = apply_filters('evo_one_ftimage_data', array(
				'f'=> $full[0],
				'h'=> $full[2],
				'w'=> $full[1],
				'i'=> $x
			), $img , $EVENT );

			echo "<span class='evo_event_more_img evobrdB2 evobr15 evocurp evobgsc  evo_trans_sc1_05' title='{$caption}' style='background-image:url({$thumb[0]})' ". $this->helper->array_to_html_data( $data ) ."></span>";
			$x++;
		}
		echo "</div>";
		echo "</div>";
	}

	do_action('evo_eventcard_ftimg_end', $object, $EVENT);
