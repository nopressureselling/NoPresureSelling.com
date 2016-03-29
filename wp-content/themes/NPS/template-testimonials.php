<?php
/*
 * Template name: Testimonials Template
 */

// Page
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$industry = get_the_terms($post->ID, 'industry');
if($industry) {
    $industry = array_values($industry);
    $industry = $industry[0];
}
$context['industry'] = $industry;

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

// Logos
$context['logos'] = Timber::get_posts(array(
    'post_type'     => 'client',
    'industry'      => $industry->slug
));


//Testimonials
$args = array(
    'posts_per_page'    => '10',
    'post_type'         => 'testimonial',
    'meta_key'          => 'featured',
    'paged'             => (get_query_var('paged')) ? get_query_var('paged') : 1
);

if($industry){
    $args['industry'] = $industry->slug;
}
$testimonials = Timber::get_posts($args);
$context['testimonials'] = $testimonials;

// Sidebar
$sidebar_context = Timber::get_context();
$sidebar_context['post'] = $post;
$sidebar_context['industry'] = $industry;
include_once('sidebar-industry.php');


// Promotion
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
include_once('sidebar-promotion.php');

// Pagination - must define after sidebar because of query_posts and wp_reset_query
query_posts($args);
$context['pages'] = Timber::get_pagination();
wp_reset_query();

// Render Template
$templates = array('template-testimonials.twig');
Timber::render($templates, $context);