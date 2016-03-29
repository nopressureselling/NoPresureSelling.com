<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;


// Promotion Context
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
include_once('sidebar-promotion.php');


// Breadcrumbs and Back Button
$back_link = get_bloginfo('url').'/blog/';
$context['back_link'] = $back_link;

$context['breadcrumbs'] = array(
    array(
        'label'         => 'Blog',
        'permalink'     => $back_link
    ),
    array(
        'label'         => $post->title,
    )
);

// Render Template
$templates = array('single-blog.twig');
Timber::render($templates, $context);