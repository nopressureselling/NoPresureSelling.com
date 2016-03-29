<?php
if($sidebar_context['industry']) {
    setcookie('industry', $industry->term_id, time() + 60 * 60 * 24 * 365, '/');
}

// Sidebar logic
$sidebar_context['onsite_training'] = Timber::get_posts(array(
    'numberposts'   => 1,
    'post_type'     => 'training',
    'meta_key'      => 'type',
    'meta_value'    => 'onsite',
    'industry'      => $industry->slug
));

$sidebar_context['virtual_training'] = Timber::get_posts(array(
    'numberposts'   => 1,
    'post_type'     => 'training',
    'meta_key'      => 'type',
    'meta_value'    => 'virtual',
    'industry'      => $industry->slug
));

$sidebar_context['course_catalog'] = Timber::get_posts(array(
    'numberposts'   => 1,
    'post_type'     => 'course',
    'industry'      => $industry->slug
));

$sidebar_context['upcoming_seminars'] = Timber::get_posts(array(
    'numberposts'   => 1,
    'post_type'     => 'seminar',
    'industry'      => $industry->slug
));

$sidebar_context['testimonials'] = Timber::get_posts(array(
    'numberposts'   => 1,
    'post_type'     => 'testimonial',
    'industry'      => $industry->slug
));

$sidebar_context['books_resources'] = Timber::get_posts(array(
    'numberposts'   => 1,
    'post_type'     => 'book_resource',
    'industry'      => $industry->slug
));

$context['sidebar'] = Timber::get_sidebar('components/sidebar-industry.twig', $sidebar_context);
?>