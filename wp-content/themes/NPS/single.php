<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;


// Render Template
$templates = array('single.twig');
Timber::render($templates, $context);