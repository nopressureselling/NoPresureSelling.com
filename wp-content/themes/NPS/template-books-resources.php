<?php
/*
 * Template name: Books & Resources Template
 */

// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$industry = get_the_terms($post->ID, 'industry');
if($industry) {
    $industry = array_values($industry);
    $industry = $industry[0];
}

$args = array(
    'posts_per_page' => '10',
    'post_type'     => 'book_resource',
    'orderby'        => 'post_date',
    'order'          =>  'DESC',
    'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1
);
if($industry){ // filter by industry taxon
    $args['industry'] = $industry->slug;
}

query_posts($args);
$context['pages'] = Timber::get_pagination();

$context['products'] = Timber::get_posts($args);


// Masthead
if($post->masthead) {
    $background_image = new TimberImage($post->background_image);
    $context['masthead'] = array(
        'image'                 => $background_image->src,
        'text'                  => $post->title,
        'particle_effect'       => $post->particle_effect,
        'particle_offset_top'   => ($post->particle_offset_top)?$post->particle_offset_top:'',
        'particle_offset_left'  => ($post->particle_offset_left)?$post->particle_offset_left:''
    );
}

// Sidebar Context
$sidebar_context = Timber::get_context();
$sidebar_context['post'] = $post;

$data_target = '#books-resources-results'; // by defining this variable, ajax will be enabled in the sidebar
$context['data_target'] = $data_target; // pass to template context so pagination becomes ajaxed

if($industry){
    $sidebar_context['industry'] = $industry;
    include_once('sidebar-industry.php');
}else{
    $taxonomy_slug = 'industry';
    $cpt_slug = 'book_resource';
    include_once('sidebar-taxonomy.php');
}



// Promotion Context
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
$promo_context['industry'] = $industry;
include_once('sidebar-promotion.php');

wp_reset_query();

// Render Template
$templates = array('template-books-resources.twig');
Timber::render($templates, $context);