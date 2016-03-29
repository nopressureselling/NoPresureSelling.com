<?php
/*
 * Template name: Services Template
 */

// Page
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

// Masthead
if($post->masthead) {
    $background_image = new TimberImage($post->background_image);
    $context['masthead'] = array(
        'image' => $background_image->src,
        'text' => $post->title,
        'particle_effect' => $post->particle_effect,
        'particle_offset_top'   => ($post->particle_offset_top)?$post->particle_offset_top:'',
        'particle_offset_left'  => ($post->particle_offset_left)?$post->particle_offset_left:''
    );
}

// Sections
$sections = get_field('sections',$post->ID);
$sections_array = array();
$i = 0;
foreach($sections as $section){
    if(in_array($section['acf_fc_layout'], $context['full_width_sections'])){ $i++; }
    if(!$sections_array[$i]){
        $sections_array[$i] = array();
    }
    array_push($sections_array[$i], $section);
    if(in_array($section['acf_fc_layout'], $context['full_width_sections'])){ $i++; }
}
$context['sections'] = $sections_array;


// Promotion
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
include_once('sidebar-promotion.php');


// Render Template
$templates = array('template-services.twig');
Timber::render($templates, $context);