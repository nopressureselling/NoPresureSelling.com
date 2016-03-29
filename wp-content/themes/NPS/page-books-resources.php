<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

echo("<pre>");
print_r($post);
echo("</pre>");
// Sidebar Context
$sidebar_context = Timber::get_context();
include_once('sidebar-books-resources.php');


// Promotion Context
$promo_context = Timber::get_context();
include_once('sidebar-promotion.php');


// Render Template
$templates = array('page-books-resources.twig');
Timber::render($templates, $context);