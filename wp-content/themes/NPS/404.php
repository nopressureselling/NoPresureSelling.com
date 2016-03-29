<?php
$context = Timber::get_context();
$context['post'] = new TimberPost();
$templates = array('404.twig');
Timber::render($templates, $context);