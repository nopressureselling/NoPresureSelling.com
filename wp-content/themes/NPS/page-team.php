<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$args = array(
    'posts_per_page'   => -1,
    'post_type'        => 'team',
    'post_status'      => 'publish',
    'orderby'          => 'date',
    'order'            => 'DESC',
);

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

$context['members'] = Timber::get_posts($args);

// Excerpt
foreach ( $context['members'] as $member ){
    $sections = get_field('sections', $member->ID);
    $contents = array();
    foreach( $sections as $section ){
        if( $section['acf_fc_layout'] == 'content' ){
            $contents[] = $section;
        }
    }
    $member->excerpt = $contents[0]['editor'];
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
$templates = array('page-team.twig');
Timber::render($templates, $context);