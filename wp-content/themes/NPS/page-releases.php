<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$args = array(
    'posts_per_page'   => '10',
    'post_type'        => 'news_press',
    'post_status'      => 'publish',
    'orderby'          => 'date',
    'order'            => 'DESC',
    'paged'            => (get_query_var('paged')) ? get_query_var('paged') : 1,
    'meta_query'       => array(
        'relation'     => 'AND',
        array(
            'key'      => 'type',
            'value'    => 'press'
        )
    )
);

query_posts($args);
$context['pages'] = Timber::get_pagination();
$news_posts = Timber::get_posts($args);
$context['press_post'] = $news_posts;

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
include_once('sidebar.php');


// Promotion Context
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
include_once('sidebar-promotion.php');

wp_reset_query();

// Render Template
$templates = array('page-releases.twig');
Timber::render($templates, $context);