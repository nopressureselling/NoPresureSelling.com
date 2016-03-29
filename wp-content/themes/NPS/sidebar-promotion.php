<?php
$promotions = get_field('industry_promotions', 'option');
if($promotions){ // loop through promotions and show all that match current industry
    foreach($promotions as $promo){
        if($industry->term_id == $promo['industry']->term_id) {
            $showDefault = false;
            $promo_context['promo'] = $promo;
        }else if(!$promo['industry']){
            $promo_context['promo'] = $promo;
        }
    }
}

$context['promo_sidebar'] = Timber::get_sidebar('components/sidebar-promotion.twig', $promo_context);
$context['promo_banner'] = Timber::get_sidebar('components/banner-promotion.twig', $promo_context);
