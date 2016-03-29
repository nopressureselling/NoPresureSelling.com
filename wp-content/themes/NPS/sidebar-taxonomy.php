<?php
$sidebar_context['data_target'] = ($data_target)?$data_target:false;

// Create list of taxonomies ordered by custom_order field
$taxonomy = get_terms($taxonomy_slug, array(
    'hide_empty'=> false
));
$sidebar_context['taxonomy'] = array();
foreach($taxonomy as $taxon){
    $posts = get_posts(array(
        'post_type'     => $cpt_slug,
        'industry'      => $taxon->slug
    ));
    if($posts){
        $order = get_field('custom_order', $taxon);
        $sidebar_context['taxonomy'][$order] = $taxon;
    }
}
sort($sidebar_context['taxonomy']);

// create context variables by parsing url
$fields = get_fields($post->ID);
if($fields['industry']){
    $sidebar_context['current_taxon'] = $fields['industry'];
    $url_array = explode('/', $post->permalink);
    array_pop($url_array);
    array_pop($url_array);
    $url_array = array_slice($url_array, -1, 1);
    $sidebar_context['slug'] = $url_array[0];
}else {
    $url_array = explode('/', $post->permalink);
    array_pop($url_array);
    $url_array = array_slice($url_array, -1, 1);
    $sidebar_context['slug'] = $url_array[0];
}
$context['sidebar'] = Timber::get_sidebar('components/sidebar-taxonomy.twig', $sidebar_context);
?>