<?php
// Page Context
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$industry = get_the_terms($post->ID, 'industry');
if($industry) {
    $industry = array_values($industry);
    $industry = $industry[0];
}
$context['industry'] = $industry;


// Breadcrumbs and Back Button
$back_link = get_bloginfo('url').'/upcoming-seminars/'.$industry->slug.'/';
$context['back_link'] = $back_link;

$context['breadcrumbs'] = array(
    array(
        'label'         => 'Upcoming Seminars',
        'permalink'     => $back_link
    ),
    array(
        'label'         => $post->title,
    )
);

// Instructors
$instructors = array();
if($post->get_field('instructors')) {
    foreach ($post->get_field('instructors') as $instructor) {
        // existing team member relationship
        if ($instructor['team_member_toggle'] == 1) {
            foreach ($instructor['existing_team_member'] as $team_member) {
                $name = $team_member->post_title;
                $sections = get_field('sections', $team_member->ID);
                foreach ($sections as $section) {
                    if ($section['acf_fc_layout'] == 'content') {
                        $bio = $section['editor'];
                    }
                }
                $thumbnail = get_field('thumbnail', $team_member->ID);

                array_push($instructors, array(
                    'name' => $name,
                    'bio' => $bio,
                    'thumbnail' => $thumbnail,
                    'permalink' => $team_member->permalink
                ));
            }
        } else {
            // custom a la carte team member entry
            $name = $instructor['name'];
            $bio = $instructor['bio'];
            $thumbnail = $instructor['thumbnail'];

            array_push($instructors, array(
                'name' => $name,
                'bio' => $bio,
                'thumbnail' => $thumbnail
            ));
        }
    }
    $context['instructors'] = $instructors;
}


// Sidebar Context
$sidebar_context = Timber::get_context();
$sidebar_context['post'] = $post;
$sidebar_context['industry'] = $industry;
include_once('sidebar-industry.php');


// Promotion Context
$promo_context = Timber::get_context();
$promo_context['post'] = $post;
include_once('sidebar-promotion.php');


// Render Template
$templates = array('single-seminar.twig');
Timber::render($templates, $context);