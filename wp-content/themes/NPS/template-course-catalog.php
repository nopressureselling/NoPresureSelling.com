<?php
/*
 * Template name: Course Catalog Template
 */

// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$industry = get_the_terms($post->ID, 'industry');
if($industry) {
    $industry = array_values($industry);
    $industry = $industry[0];
}

$args = array(
    'posts_per_page'   => '15',
    'post_type'        => 'course',
    'industry'         => $industry->slug,
    'post_status'      => 'publish',
    'orderby'          => 'date',
    'order'            => 'DESC',
    'paged'            => (get_query_var('paged')) ? get_query_var('paged') : 1
);

query_posts($args);
$context['courses'] = Timber::get_posts($args);
$context['pages'] = Timber::get_pagination();

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

// Sidebar Context
$sidebar_context = Timber::get_context();
$sidebar_context['post'] = $post;
$sidebar_context['industry'] = $industry;
include_once('sidebar-industry.php');


// Promotion Context
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
$promo_context['industry'] = $industry;
include_once('sidebar-promotion.php');

wp_reset_query();

// Render Template
$templates = array('template-course-catalog.twig');
Timber::render($templates, $context);