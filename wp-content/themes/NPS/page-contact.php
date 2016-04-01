<?php
$context = Timber::get_context();
$context['post'] = new TimberPost();

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

$templates = array('page-contact.twig');
Timber::render($templates, $context);