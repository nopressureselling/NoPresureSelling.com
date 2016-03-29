<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

// Breadcrumbs and Back Button
$back_link = get_bloginfo('url').'/about/team/';
$context['back_link'] = $back_link;

$context['breadcrumbs'] = array(
    array(
        'label'         => 'Our Team',
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
    if(in_array($section['acf_fc_layout'], $context['full_width_sections'])){ $i++; }
    if(!$sections_array[$i]){
        $sections_array[$i] = array();
    }
    array_push($sections_array[$i], $section);
    if(in_array($section['acf_fc_layout'], $context['full_width_sections'])){ $i++; }
}
$context['sections'] = $sections_array;

// Sidebar Context
$sidebar_context = Timber::get_context();
$sidebar_context['post'] = $post;
$sidebar_context['industry'] = $industry;
include_once('sidebar-industry.php');

// Render Template
$templates = array('single-team.twig');
Timber::render($templates, $context);