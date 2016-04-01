<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

// Masthead
if ( isset( $post->masthead ) ) {
	if ( !empty( $post->background_image ) ) {
		$background_image = new TimberImage($post->background_image);
		$context['masthead'] = array(
			'image' => $background_image->src,
			'text' => $post->title,
			'particle_effect' => !empty($post->particle_effect) ? $post->particle_effect : '',
			'particle_offset_top'   => !empty($post->particle_offset_top) ? $post->particle_offset_top : '',
			'particle_offset_left'  => !empty($post->particle_offset_left) ? $post->particle_offset_left : '',
		);
	}
}

// Sections
$sections = get_field('sections',$post->ID);
$sections_array = array();
$i = 0;
foreach($sections as $section){
    if(in_array($section['acf_fc_layout'], $context['full_width_sections'])){
	    $i++;
    }
	if(isset($sections_array[$i]) && !$sections_array[$i] || !isset($sections_array[$i])){
		$sections_array[$i] = array();
	}
    array_push($sections_array[$i], $section);
    if(in_array($section['acf_fc_layout'], $context['full_width_sections'])){
	    $i++;
    }
}
$context['sections'] = $sections_array;


// Sidebar Context
$sidebar_context = Timber::get_context();
$sidebar_context['post'] = $post;
include_once('sidebar.php');


// Promotion Context
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
include_once('sidebar-promotion.php');


// Render Template
$templates = array('page-no-pressure-selling.twig');
Timber::render($templates, $context);