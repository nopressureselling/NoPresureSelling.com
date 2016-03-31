<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

// Build section based on industry
$sections = get_field('sections',$post->ID);

    // Check for industry cookie
    $industry_id = (isset($_COOKIE['industry'])) ? $_COOKIE['industry'] : 'default';

    // Cache default section
    global $default_section;
    foreach ($sections as $section) {
        if (!isset($section['industry']->term_id)) {
            $default_section = $section;
            break;
        }
    }

    // Choose related section
    global $term;
    $default = true;
    foreach ($sections as $section) {
        if (isset($section['industry']->term_id) && $section['industry']->term_id == $industry_id){
            $context['section'] = $section;
            $term = get_term( $_COOKIE['industry'], 'industry')->slug;
            $default = false;
            break;
        }
    }

    // Default fallback
    if($default == true){
        $context['section'] = $default_section;
        $term = '';
        $context['default_version'] = true;
    }

// Masthead
$context['masthead'] = array(
    'image'                 => $context['section']['masthead_background_image']['url'],
    'text'                  => $context['section']['masthead_text']
);

// Promotion based on industry
$promotions = get_field('industry_promotions', 'option');
foreach ($promotions as $promotion){
    if( isset($_COOKIE['industry']) && $_COOKIE['industry'] !== false && $_COOKIE['industry'] !== null ) {
        if(isset($promotion['industry']->term_id) && $promotion['industry']->term_id == $_COOKIE['industry']){
            $context['promotion'] = $promotion;
        }
    }else{
        if(!$promotion['industry']){
            $context['promotion'] = $promotion;
        }
    }
}
// Sidebar Promo
if (isset($_COOKIE['industry']) && $_COOKIE['industry'] !== false && $_COOKIE['industry'] !== null){
	$industry = get_term( $_COOKIE['industry'], 'industry');
	$context['industry'] = $industry;
}
include_once('sidebar-promotion.php');

// Upcoming Seminars & News based on industry
$context['upcoming_seminars'] = Timber::get_posts(array(
    'post_type'        => 'seminar',
    'industry'         => $term,
    'posts_per_page'   => 3,
    'order'            => 'DESC',
    'post_status'      => 'publish',
    'meta_query' => array(
		array(
			'key'     => 'seminar_date',
			'value'   => date('Ymd'),
			'compare' => '>=',
		)
    )
));

$context['news'] = Timber::get_posts(array(
    'post_type'        => 'news_press',
    'industry'         => $term,
    'posts_per_page'   => 3,
    'order'            => 'DESC',
    'post_status'      => 'publish',
    'meta_key'         => 'type',
    'meta_value'       => 'news'
));

// Render Template
$templates = array('page-home.twig');
Timber::render($templates, $context);