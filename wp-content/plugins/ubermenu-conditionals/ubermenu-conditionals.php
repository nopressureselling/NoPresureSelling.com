<?php
/*
Plugin Name: UberMenu Conditionals
Plugin URI: http://wpmegamenu.com/conditionals
Description: Control which menu items are displayed based on conditions
Version: 3.0
Author: Chris Mavricos, SevenSpark
Author URI: http://sevenspark.com
License: http://codecanyon.net/licenses/regular_extended
*/

/*
Copyright 2011-2014 Chris Mavricos, SevenSpark http://sevenspark.com
*/


function ubermenu_conditionals( $display_on , $walker , $element , $max_depth, $depth, $args , $umitem ){

	//Default state is true, but may have been adjusted
	$id = $element->ID;

	//Because these conditions are "ONLY"s, we don't care about the incoming $display_on parameter
	//Note: 'default' will maintain the incoming $display_on parameter
	

	$condition = $umitem->getSetting( 'condition_1' );
	$param = $umitem->getSetting( 'condition_parameter_1' );

	//If there is a condition, handle Dynamic URLs
	if( $condition != 'default' && $condition != '' ){
		//Allow shortcodes
		$umitem->set_url( ubermenu_conditionals_dynamic_url( $umitem ) );
	}

	$display_on = ubermenu_conditionals_test_condition( $condition , $param , $umitem , $display_on );

	$condition2 = $umitem->getSetting( 'condition_2' );
	if( $condition2 != 'default' && $condition2 != '' ){
		$param2 = $umitem->getSetting( 'condition_parameter_2' );

		$display_on_2 = ubermenu_conditionals_test_condition( $condition2 , $param2 , $umitem , $display_on );

		$operator = $umitem->getSetting( 'condition_logic_operator' );
		if( $operator == 'and' ){
			$display_on = $display_on && $display_on_2;
		}
		else{
			$display_on = $display_on || $display_on_2;
		}
	}

	return $display_on;
}

add_filter( 'ubermenu_display_item', 'ubermenu_conditionals', 10, 7 );

function ubermenu_conditionals_test_condition( $condition , $param , &$umitem , $display_on = true ){
	if( $condition != 'default' && $condition != '' ){

		//Handle params here
		if( $param ){
			//if commas, explode
			if( strpos( $param , ',' ) !== false ){
				$param = explode( ',' , $param );
			}
		}

		switch( $condition ){

			//Always display this menu item
			case 'always':
				return true;
				break;

			//Display only if user is currently logged in
			case 'user_logged_in':
				return is_user_logged_in();
				break;

			//Display only if user is currently logged out
			case 'user_logged_out':
				return !is_user_logged_in();
				break;

			case 'user_can':
				return current_user_can( $param );
				break;

			case 'user_cannot':
				return !current_user_can( $param );
				break;

			case 'is_front_page':
				return is_front_page();
				break;

			case 'not_front_page':
				return !is_front_page();
				break;

			case 'is_home':
				return is_home();
				break;

			case 'not_home':
				return !is_home();
				break;

			//Display only on pages with appropriate parameters
			case 'is_page':
				if( $param ) return is_page( $param );
				return is_page();
				break;

			case 'is_not_page':
				if( $param ) return !is_page( $param );
				return !is_page();
				break;


			case 'is_single':
				if( $param ) return is_single( $param );
				return is_single();
				break;

			case 'is_not_single':
				if( $param ) return !is_single( $param );
				return !is_single();
				break;

			case 'is_page_template':
				if( $param ) return is_page_template( $param );
				return is_page_template();
				break;

			case 'is_not_page_template':
				if( $param ) return !is_page_template( $param );
				return !is_page_template();
				break;

			case 'is_category':
				if( $param ) return is_category( $param );
				return is_category();
				break;

			case 'is_tag':
				if( $param ) return is_tag( $param );
				return is_tag();
				break;

			case 'is_tax':
				if( $param ) return is_tax( $param );
				return is_tax();
				break;

			case 'is_author':
				if( $param ) return is_author( $param );
				return is_author();
				break;

			case 'is_archive':
				return is_archive();
				break;

			case 'is_search':
				return is_search();
				break;

			case 'is_404':
				return is_404();
				break;

			case 'is_singular':
				if( $param ) return is_singular( $param );
				return is_singular();
				break;



			/* 1.1 */
			case 'is_post_type':
				return uberMenu_conditionals_is_post_type( $param );
				break;


			/* 1.2 */
			case 'user_role':
				return uberMenu_conditionals_check_user_role( $param );
				break;

			case 'user_not_role':
				return !uberMenu_conditionals_check_user_role( $param );
				break;


			/* Custom */
			default: 
				$display_on = apply_filters( 'ubermenu_conditionals_custom_condition' , $display_on , $condition , $param );
		
		}
		//http://codex.wordpress.org/Conditional_Tags		
	}
	return $display_on;
}

function ubermenu_conditionals_item_settings( $settings ){

	$settings['conditionals'][20] = array(
		'id'	=> 'condition_1',
		'title'	=> __( 'Condition 1' , 'ubermenu' ),
		'desc'	=> __( 'Display this menu item ONLY if this condition is met' , 'ubermenu' ), 
		'type'	=> 'radio',
		'type_class'=> 'ubermenu-radio-blocks',
		'ops'	=> 'uberMenu_conditionals_options',
		'default'=> 'default',
	);

	$settings['conditionals'][30] = array(
		'id'	=> 'condition_parameter_1',
		'title'	=> __( 'Condition 1 Parameter' , 'ubermenu' ),
		'desc'	=> __( 'Optional parameters to pass to the selected conditional function.  Comma-separated lists will be converted to arrays.' , 'ubermenu' ),
		'type'	=> 'text',
		'ops'	=> 'uberMenu_conditionals_options',
		'default'=> '',
	);

	$settings['conditionals'][40] = array(
		'id'	=> 'condition_logic_operator',
		'title'	=> __( 'Logical Operator' , 'ubermenu' ),
		'desc'	=> __( 'If you use two conditions, decide how they should be combined', 'ubermenu' ),
		'type'	=> 'radio',
		'type_class'=> 'ubermenu-radio-blocks',
		'ops'	=> array(
			'group'	=> array(
				'and'	=> array(
					'name'	=> __( 'AND' , 'ubermenu' ),
					'desc'	=> __( 'Both Conditions must be met', 'ubermenu' ),
				),
				'or'	=> array(
					'name'	=> __( 'OR' , 'ubermenu' ),
					'desc'	=> __( 'Either Condition can be met', 'ubermenu' ),
				),
			),
		),
		'default'=> 'and',
	);

	$settings['conditionals'][50] = array(
		'id'	=> 'condition_2',
		'title'	=> __( 'Condition 2' , 'ubermenu' ),
		'desc'	=> __( 'Display this menu item ONLY if this condition is met' , 'ubermenu' ), 
		'type'	=> 'radio',
		'type_class'=> 'ubermenu-radio-blocks',
		'ops'	=> 'uberMenu_conditionals_options',
		'default'=> 'default',
	);

	$settings['conditionals'][60] = array(
		'id'	=> 'condition_parameter_2',
		'title'	=> __( 'Condition 2 Parameter' , 'ubermenu' ),
		'desc'	=> __( 'Optional parameters to pass to the selected conditional function.  Comma-separated lists will be converted to arrays.' , 'ubermenu' ),
		'type'	=> 'text',
		'ops'	=> 'uberMenu_conditionals_options',
		'default'=> '',
	);

	return $settings;

}
add_filter( 'ubermenu_menu_item_settings' , 'ubermenu_conditionals_item_settings' );


function ubermenu_conditionals_options(){
	$ops = array(
		'basics' => array(
			'default' 				=> array(
											'name' => 'No Condition',
											'desc'	=> __( 'Display as usual' , 'ubermenu' ),
										),
			'always'				=> array(
											'name' => __( 'Always' , 'ubermenu' ),
											'desc'	=> __( 'Always display this item' , 'ubermenu' ),
										),
		),
		'users' => array(
			'group_title'			=> __( 'User Conditions', 'ubermenu' ),
			'user_logged_in' 		=> array(
											'name'	=> __( 'If user is logged in' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'user_logged_out' 		=> array(
											'name'	=> __( 'If user is not logged in' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'user_can'				=> array(
											'name'	=> __( 'If user can [capability]' , 'ubermenu' ),
											'desc'  => __( 'Required Parameter: ' , 'ubermenu' ) . '<a target="_blank" href="http://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table">Capability</a>',
										),
			'user_cannot'			=> array(
											'name'	=> __( 'If user cannot [capability]' , 'ubermenu' ),
											'desc'  => __( 'Required Parameter: ' , 'ubermenu' ) . '<a target="_blank" href="http://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table">Capability</a>',
										),
			'user_role'				=> array(
											'name'	=> __( 'User is [role]' , 'ubermenu' ),
											'desc'  => __( 'Required Parameter: ' , 'ubermenu' ) . '<a target="_blank" href="http://codex.wordpress.org/Roles_and_Capabilities">Role Slug</a>',
										),
			'user_not_role'			=> array(
											'name'	=> __( 'User is not [role]' , 'ubermenu' ),
											'desc'  => __( 'Required Parameter: ' , 'ubermenu' ) . '<a target="_blank" href="http://codex.wordpress.org/Roles_and_Capabilities">Role Slug</a>',
										),
		),
		'pages'	=> array(
			'group_title'			=> __( 'Page Conditions' , 'ubermenu' ),

			'is_front_page'			=> array(
											'name' => __( 'On front page' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'not_front_page'		=> array(
											'name' => __( 'Not on front page' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'is_home'				=> array(
											'name' => __( 'On home page (main blog)' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'not_home'				=> array(
											'name' => __( 'Not on home page (main blog)' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'is_page'				=> array(
											'name' => __( 'On a page' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Page ID ' , 'ubermenu' ),
										),
			'is_not_page'			=> array(
											'name' => __( 'Not on a page' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Page ID ' , 'ubermenu' ),
										),
			'is_single'				=> array(
											'name' => __( 'On a single Post' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Post ID ' , 'ubermenu' ),
										),
			'is_not_single'			=> array(
											'name' => __( 'Not on a single Post' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Post ID ' , 'ubermenu' ),
										),
			'is_page_template'		=> array(
											'name' => __( 'On a page template' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Template Filename ' , 'ubermenu' ),
										),
			'is_not_page_template' 	=> array(
											'name' => __( 'Not on a page template' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Template Filename ' , 'ubermenu' ),
										),
			'is_category'			=> array(
											'name' => __( 'On a Category Archive' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Category ID or list of IDs ' , 'ubermenu' ),
										),
			'is_tag'				=> array(
											'name' => __( 'On a Tag Archive' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Tag ID or list of IDs ' , 'ubermenu' ),
										),
			'is_tax'				=> array(
											'name' => __( 'On a Taxonomy Archive' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Taxonomy slug ' , 'ubermenu' ),
										),
			'is_author'				=> array(
											'name' => __( 'On an Author Archive page' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Author ID' , 'ubermenu' ),
										),
			'is_archive'			=> array(
											'name' => __( 'On any archive page' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'is_search'				=> array(
											'name' => __( 'On Search Results page' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'is_404'				=> array(
											'name' => __( 'On 404 page' , 'ubermenu' ),
											'desc'  => __( '(No parameter)' , 'ubermenu' ),
										),
			'is_singular'			=> array(
											'name' => __( 'On a single Page, Post, or Attachment' , 'ubermenu' ),
											'desc'  => __( 'Optional Parameter: Post Type slug or list of slugs ' , 'ubermenu' ),
										),

			/* 1.1 */
			'is_post_type'			=> array(
											'name' => __( 'On a Post Type [type]' , 'ubermenu' ),
											'desc'  => __( 'Required Parameter: Post Type Slug' , 'ubermenu' ),
										),
		),		
	);

	return apply_filters( 'ubermenu_conditionals_options' , $ops );
}

function ubermenu_conditionals_item_settings_panels( $panels ){
	
	$panels['conditionals'] = array(
		'title'	=> __( 'Conditionals', 'ubermenu' ),
		'icon'	=> 'check-square-o',
		'info' 	=> __( 'Conditionally control the display of this item.' , 'ubermenu' ),
		'tip'	=> __( 'Leave this section set to the defaults to display as usual.  You can set one or both conditions.' , 'ubermenu' ),
	);

	return $panels;
}
add_filter( 'ubermenu_menu_item_settings_panels' , 'ubermenu_conditionals_item_settings_panels' );

function ubermenu_conditionals_item_settings_panels_map( $map ){

	foreach( $map as $id => $panels ){
		$map[$id][] = 'conditionals';
	}

	return $map;
}
add_filter( 'ubermenu_menu_item_settings_panels_map' , 'ubermenu_conditionals_item_settings_panels_map' );


/* Custom Conditionals */

/* Since 1.1 */
function uberMenu_conditionals_is_post_type( $param ){
	
	$post_type = get_post_type();

	if( is_array( $param ) ){
		if( in_array( $post_type , $param ) ){
			return true;
		}
	}
	else{
		if( $post_type == $param ){
			return true;
		}
	}

	return false;
}


/* Since 1.2 */
function uberMenu_conditionals_check_user_role( $role, $user_id = null ) {

	if( is_numeric( $user_id ) )	$user = get_userdata( $user_id );
	else							$user = wp_get_current_user();
 
	if( empty( $user ) )			return false;

    return in_array( strtolower( $role ), (array) $user->roles );
}


/* Dynamic URLs - since 1.1 */

function ubermenu_conditionals_dynamic_url( &$umitem ){
	$url = $umitem->get_url();

	if( strpos( $url , '#umcdu-' ) === 0 ){
		$url = do_shortcode( '['. substr( $umitem->get_url() , 1 ) . ']' ) ;
	}

	return $url;
}

//Logout shortcode
function uberMenu_conditionals_logout_shortcode( $atts , $content ){
	extract( shortcode_atts( array(
		'redirect' => home_url()
	), $atts) );

	return wp_logout_url( $redirect );
}
add_shortcode( 'umcdu-logout', 'uberMenu_conditionals_logout_shortcode' );




/*
// Sample Custom Filter
function my_custom_conditional_filter( $display_on , $walker , $element , $max_depth, $depth, $args ){

	//The ID of the menu item currently being filtered
	$id = $element->ID;

	//Check for that specific menu item
	if( $id == 268 ){

		//If we're currently logged in AND on the front page
		if( is_user_logged_in() && is_front_page() ){

			//Disable the menu item
			$display_on = false;

		}

	}

	return $display_on;
}
add_filter( 'ubermenu_display_item', 'my_custom_conditional_filter', 20, 6 );
*/

