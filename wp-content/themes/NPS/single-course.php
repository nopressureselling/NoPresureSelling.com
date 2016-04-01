<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$industry = get_the_terms($post->ID, 'industry');
if($industry) {
    $industry = array_values($industry);
    $industry = $industry[0];
	$industry_slug = $industry->slug . '/';
} else {
	$industry_slug = '';
}
$context['industry'] = $industry;


// Breadcrumbs and Back Button
$back_link = get_bloginfo('url').'/course-catalog/'.$industry_slug;
$context['back_link'] = $back_link;

$context['breadcrumbs'] = array(
    array(
        'label'         => $industry->name,
        'permalink'     => $back_link
    ),
    array(
        'label'         => $post->title,
    )
);

// Sections
$sections = get_field('sections',$post->ID);
$sections_array = array();
$i = 0;
foreach($sections as $section){
	if(in_array($section['acf_fc_layout'], $context['full_width_sections'])){
		$i++;
	}
	if(isset($sections_array[$i]) && !$sections_array[$i] || !isset($sections_array[$i])){
		$sections_array[$i] = array();
	}
	array_push($sections_array[$i], $section);
	if(in_array($section['acf_fc_layout'], $context['full_width_sections'])){
		$i++;
	}
}
$context['sections'] = $sections_array;


// Render Template
$templates = array('single-course.twig');
Timber::render($templates, $context);