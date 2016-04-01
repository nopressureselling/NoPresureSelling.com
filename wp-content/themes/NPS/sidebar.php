<?php
$args = array(
    'numberposts'   => -1,
    'post_type'     => 'page',
    'post_parent'   => $post->post_parent,
    'order_by'      => 'menu_order',
    'order'         => 'ASC'
);
$sidebar_context['pages'] = Timber::get_posts($args);
$context['sidebar'] = Timber::get_sidebar('components/sidebar.twig', $sidebar_context);