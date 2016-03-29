<?php
// Page Context
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

// Industries
// merge default industries context var defined in functions file with page attribute from home page dropdown
$home = Timber::get_post(array('page_id' => 2));
$home_industries = $home->get_field('industry_dropdown');
foreach($home_industries as $home_industry){
    foreach($context['industries'] as $industry){
        if($industry->term_id == $home_industry['industry']->term_id){
            $industry->page = $home_industry['page'];
        }
    }
}

// Sidebar Context
$sidebar_context = Timber::get_context();
$sidebar_context['post'] = $post;
include_once('sidebar.php');


// Promotion Context
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
include_once('sidebar-promotion.php');


// Render Template
$templates = array('page-industries.twig');
Timber::render($templates, $context);