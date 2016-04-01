<?php
/*
 * Template name: Upcoming Seminars Template
 */

// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$industry = get_the_terms($post->ID, 'industry');
if($industry) {
    $industry = array_values($industry);
    $industry = $industry[0];
	$industry_slug = $industry->slug;
} else {
	$industry_slug = null;
}

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

// Seminars
$args = array(
    'post_type'         => 'seminar',
    'industry'          => $industry_slug,
    'meta_query'        => array(
        array(
            'key'       => 'seminar_date',
            'value'     => date('Ymd'),
            'type'      => 'numeric',
            'compare'   => '>'
        )
    ),
    'orderby'           => 'meta_value',
    'order'             => 'ASC'
);
$context['seminars'] = Timber::get_posts($args);


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


// Render Template
$templates = array('template-upcoming-seminars.twig');
Timber::render($templates, $context);