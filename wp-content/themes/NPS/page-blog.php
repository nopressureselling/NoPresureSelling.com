<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$args = array(
    'posts_per_page'   => '3',
    'post_type'        => 'blog',
    'post_status'      => 'publish',
    'orderby'          => 'date',
    'order'            => 'DESC',
    'meta_query'       => array(
        'relation'     => 'AND',
        array(
            'key'      => 'featured_post',
            'value'    => '1',
            'compare'  => '=='
        )
    )
);

$blog_posts = Timber::get_posts($args);
$context['featured_post'] = $blog_posts;

$args = array(
    'posts_per_page'   => '7',
    'post_type'        => 'blog',
    'post_status'      => 'publish',
    'orderby'          => 'date',
    'order'            => 'DESC',
    'paged'            => (get_query_var('paged')) ? get_query_var('paged') : 1,
    'meta_query'       => array(
        'relation'     => 'AND',
        array(
            'key'      => 'featured_post',
            'value'    => '0',
            'compare'  => '=='
        )
    )
);

query_posts($args);
$context['pages'] = Timber::get_pagination();
$context['blog_posts'] = Timber::get_posts($args);

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
$templates = array('page-blog.twig');
Timber::render($templates, $context);