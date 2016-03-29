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
if ($post->type == 'press') {
    $back_link = get_bloginfo('url').'/pressroom/releases';
} else {
    $back_link = get_bloginfo('url').'/pressroom/'.$post->type.'/';
}

$context['back_link'] = $back_link;

$context['breadcrumbs'] = array(
    array(
        'label'         => ($post->type == 'news')?'In The News':'Press Releases',
        'permalink'     => $back_link
    ),
    array(
        'label'         => $post->title,
    )
);

// Render Template
$templates = array('single-news_press.twig');
Timber::render($templates, $context);