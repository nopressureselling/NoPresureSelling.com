<?php

class StarterSite extends TimberSite{
	public $assets = '';
	public $customTwig = '';

	function __construct(){
		Timber::$dirname = 'twig';
		$this->assets = get_template_directory_uri() . '/assets/production';
		$this->custom_twig = new custom_twig();

		add_action('after_setup_theme', array($this, 'setup'));
		parent::__construct();
	}

	function setup(){
		$this->add_theme_supports();
		$this->add_filters();
		$this->add_actions();
		$this->add_shortcodes();
	}

	function add_theme_supports(){
		add_theme_support('post-formats');
		add_theme_support('post-thumbnails');
		add_theme_support('menus');
	}

	function add_filters(){
		add_filter('timber_context', array($this, 'add_to_context'));
		add_filter('get_twig', array($this->custom_twig, 'init'));
		add_filter( 'ubermenu_conditionals_options' , array($this, 'register_custom_conditions') );
		add_filter( 'ubermenu_conditionals_custom_condition', array($this, 'ubermenu_conditional_filters'), 10, 3 );
	}

	function add_actions(){
		add_action('wp_enqueue_scripts', array($this, 'custom_scripts'));
	}

	function add_shortcodes(){
		add_shortcode('theme_image', array($this, 'get_theme_image'));
		add_shortcode('theme_svg_image', array($this, 'get_svg_image'));
		add_shortcode('chevrons', array($this, 'chevrons'));
	}

	// scripts and styles
	function custom_scripts(){
		if( !is_admin() ){
			wp_deregister_script('jquery');
            wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js", false, null);
            wp_enqueue_script('jquery');

            wp_register_style('theme_styles', $this->assets . '/css/styles.min.css', false, 'all');
            wp_enqueue_style('theme_styles');

            wp_register_script('theme_scripts', $this->assets . '/js/scripts.min.js', false, null, true);
            wp_enqueue_script('theme_scripts');

		}
	}

	// UBERMENU FUNCTIONS
	// pull image from theme folder as a shortcode for use in ubermenu
	function get_theme_image($atts){
		extract(shortcode_atts(array(
			'name' => 'logo.png'
	   	), $atts));

		$output = '<img src="'.get_bloginfo('template_url').'/assets/production/images/'.$name.'" ';
		if($width){
			$output .= 'width="'.$width.'" ';
		}
		if($height){
			$output .= 'height="'.$height.'" ';
		}
		if($class){
			$output .= 'class="'.$class.'" ';
		}
		$output .= '/>';

		return  $output;
	}

	function register_custom_conditions( $ops ){
	    if( !isset( $ops['custom'] ) ) $ops['custom'] = array();
	    $ops['custom']['course_catalog'] = array(         //my_condition is used to identify this condition
	        'name'  => 'Course Catalog Exists',    //This name will appear in the settings panel
	        'desc'  => 'Only shows link if Course Catalog posts exist for that industry. Industry must be passed as condition parameter.' //The condition's description
	    );
		$ops['custom']['seminar'] = array(         //my_condition is used to identify this condition
			'name'  => 'Seminar Exists',    //This name will appear in the settings panel
			'desc'  => 'Only shows link if Seminar posts exist for that industry. Industry must be passed as condition parameter.' //The condition's description
		);
		$ops['custom']['training'] = array(         //my_condition is used to identify this condition
			'name'  => 'Training Exists',    //This name will appear in the settings panel
			'desc'  => 'Only shows link if Training posts exist for that industry. Industry must be passed as condition parameter.' //The condition's description
		);
	    return $ops;
	}

	function ubermenu_conditional_filters( $display , $condition, $param ){
		if( $condition == 'course_catalog' ) {
			$display = false;
			if($param){
				$posts = get_posts(array(
					'post_type'     => 'course',
                    'post_status'      => 'publish',
					'industry'      => $param
				));
				if(count($posts) > 0){
					$display = true;
				}
			}

			return $display;
		}elseif( $condition == 'seminar' ) {
			$display = false;
			if($param){
				$posts = get_posts(array(
					'post_type'     => 'seminar',
                    'post_status'      => 'publish',
					'industry'      => $param,
                    'meta_query' => array(
                        array(
                            'key'     => 'seminar_end_date',
                            'value'   => date('Ymd'),
                            'compare' => '>=',
                        )
                    )
				));
				if(count($posts) > 0){
					$display = true;
				}
			}

			return $display;
		}elseif( $condition == 'training' ) {
			$display = false;
			if($param){
				$posts = get_posts(array(
					'post_type'     => 'training',
                    'post_status'      => 'publish',
					'industry'      => $param
				));
				if(count($posts) > 0){
					$display = true;
				}
			}

			return $display;
		}

	}

	// .svg image
	function get_svg_image($atts){
		extract(shortcode_atts(array(
			'src' => 'logo.png',
			'fallback' => 'logo.png'
	   	), $atts));

		$output = '<img data-img="svg" src="'.get_bloginfo('template_url').'/assets/production/images/'.$src.'" data-fallback="'.get_bloginfo('template_url').'/assets/production/images/'.$fallback.'" ';
		if( isset($width) && is_string($width) ){
			$output .= 'width="'.$width.'" ';
		}
		if(isset($height) && is_string($height)){
			$output .= 'height="'.$height.'" ';
		}
		if(isset($class) && is_string($class)){
			$output .= 'class="'.$class.'" ';
		}
		$output .= '/>';

		return  $output;
	}

	// chevrons shortcode
	function chevrons( $atts ){
		$a = shortcode_atts( array(
			'color' => 'lt-green',
			'position' => 'right',
			'size' => 'xs'
		), $atts );

		return '<div class="chevrons position-'.$a['position'].' '.$a['size'].'"><img data-img="svg" src="'.get_bloginfo('template_url').'/assets/production/images/chevrons/chevron-'.$a['color'].'.svg" data-fallback="'.get_bloginfo('template_url').'/assets/production/images/chevrons/chevron-'.$a['color'].'.png" alt=">>"/></div>';
	}

	// global context
	function add_to_context($context){
		$context['site'] = $this;
		$context['url'] = get_bloginfo('url');
		$context['template_url'] = get_bloginfo('template_url');
		$context['footer_nav'] = new TimberMenu('Footer Nav');
		$context['full_width_sections'] = array('testimonial', 'big_list');

        //QUERY STRING PARAMS
        foreach($_GET as $k => $v){
            $context['get'][$k] = $v;
        }

		//UTILITY CLASS
        include_once(__DIR__."/includes/Utility.class.php");

        //BROWSERS
		$context['browsers'] = explode(' ', $utility->html_classes);

        //INDUSTRIES
        $industries_terms = Timber::get_terms('industry',
            array('hide_empty'    => false)
        );
        $context['industries'] = array();
        foreach ( $industries_terms as $term ) {
            $custom_order = get_field( 'custom_order', $term );
            $context['industries'][$custom_order] = $term;
        }
        ksort($context['industries'], SORT_NUMERIC);

        //MISC
		$context['site_info'] = get_fields('option');

		$context['site_info']['full_address'] = $context['site_info']['address'][0]['address_1'].' ';
		if($context['site_info']['address'][0]['address_2']){
			$context['site_info']['full_address'] .= $context['site_info']['address'][0]['address_2'].' ';
		}
		$context['site_info']['full_address'] .= $context['site_info']['address'][0]['city'].', ';
		$context['site_info']['full_address'] .= $context['site_info']['address'][0]['state'].' ';
		$context['site_info']['full_address'] .= $context['site_info']['address'][0]['zip'];

        $context['home_industry_dropdown'] = get_field('industry_dropdown', 2);

		//TEST
		if(isset($_GET['test']) && $_GET['test'] == 1){
			$context['test'] = 1;
		}

		return $context;
	}
}
new StarterSite();

class custom_twig{
	function init($twig){

		// associate twig name and function name here
		$additions = array(
			array(
				'type'        => 'filter', // filter or function ?
				'twig_string' => 'dump_data', // in twig: {{ somevar|dump_data }}
				'function'    => array($this, 'dump_data')
			),
			array(
				'type'        => 'filter', // filter or function ?
				'twig_string' => 'truncate', // in twig: {{ somevar|truncate(100) }}
				'function'    => array($this, 'truncate_words')
			),
            array(
				'type'        => 'filter', // filter or function ?
				'twig_string' => 'limit', // in twig: {{ somevar|limit(100) }}
				'function'    => array($this, 'limit')
			)
		);

		$twig->addExtension(new Twig_Extension_StringLoader());
		foreach($additions as $item){
			$args = $item['function'];
			if($item['type'] == 'filter'){
				$filter = new Twig_SimpleFilter($item['twig_string'], $args);
				$twig->addFilter($filter);
			}
			else{
				$filter = new Twig_SimpleFunction($item['twig_string'], $args);
				$twig->addFunction($filter);
			}
		}
		return $twig;
	}

	// Add new functions and filters below.
    function dump_data($message){
        echo '<pre>';
        print_r($message);
        echo '</pre>';
    }

	function truncate_words($phrase, $max_words) {
	   $phrase_array = explode(' ',$phrase);
	   if(count($phrase_array) > $max_words && $max_words > 0) {
		   $phrase = implode(' ', array_slice($phrase_array, 0, $max_words)) . '...';
	   }
	   return $phrase;
	}

    function limit($phrase, $max_char){
        if( strlen($phrase) > $max_char){
            $output = substr($phrase, 0, $max_char).'...';
        }else{
            $output = $phrase;
        }
        return $output;
    }
}

//CUSTOM LOGIC
//////////////

// Dump data
function dump_data($message){
    echo '<pre>';
    print_r($message);
    echo '</pre>';
}

// Register RoyalSlider files
function nps_register_new_royalslider_files()
{
	register_new_royalslider_files(1);
}
add_action( 'wp_enqueue_scripts', 'nps_register_new_royalslider_files' );

// Video URL for RoyalSlider
	class MyRoyalSliderRendererHelper {
		private $post;
		private $options;

		function __construct($data, $options){
			$this->post = $data;
			$this->options = $options;
		}

		function video_url(){
			return get_post_meta( $this->post->ID, 'video_url', true);
		}

		function caption(){
			return $this->post->post_excerpt;
		}
	}
	function newrs_add_custom_variables($m, $data, $options) {

		$helper = new MyRoyalSliderRendererHelper($data, $options);
		$m->addHelper('video_url', array($helper, 'video_url') );
		$m->addHelper('caption', array($helper, 'caption') );
	}
	add_filter('new_rs_slides_renderer_helper', 'newrs_add_custom_variables', 10, 4);

// THE GALLERY
function the_gallery($array){
    $shortcode = '[gallery ids="';

        $i = 1;
        $count = count($array);
        $ids = '';
        foreach( $array as $item ){
            $s = ($i < $count) ? ',' : '';
            $ids .= $item.$s;
        $i++;
        }

    $shortcode .= $ids.'"]';
    return do_shortcode($shortcode);
}

//ACF LOGIC
///////////

//CHANGE OPTIONS PAGE NAME
if( function_exists('acf_add_options_page') ){
	acf_add_options_page(array(
		'page_title' 	=> 'Promotions',
		'menu_title'	=> 'Promotions',
		'menu_slug' 	=> 'promotions',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	acf_add_options_page(array(
		'page_title' 	=> 'Site Information',
		'menu_title'	=> 'Site Info',
		'menu_slug' 	=> 'site_info',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}


//CUSTOM CSS AND JS FOR ADMIN
/////////////////////////////

function acf_custom_admin(){
?>
	<style type="text/css">

		/* ACF ALERT */
        .acf-alert{
            padding: 15px !important;
            margin: 0 0 15px !important;
            background: #EAF2FA !important;
            border-top: 1px solid #c7d7e2 !important;
            border-right: 1px solid #c7d7e2 !important;
            border-bottom: 1px solid #c7d7e2 !important;
            border-left: 1px solid #c7d7e2 !important;
            color: black !important;
        }
		.acf-postbox.seamless > .acf-fields > .acf-field.acf-alert{
			border: 1px solid #c7d7e2 !important;
		}
		.acf-alert *{
            font-size: 16px !important;
            line-height: 24px !important;
		}
		.acf-alert .acf-label{
			display: none !important;
		}

        /* custom styles for particle effect interface */
        .acf-image-uploader .view{
            min-height: 50px;
            min-width: 100%;
        }
        .acf-image-uploader .acf-soh-target{
            right: auto;
            left: 5px;
            width: 32px;
        }
        .acf-image-uploader .acf-soh-target li{
            margin: 0 0 5px 0;
        }

		/*HIDE TAGCLOUD, PARENT & DESCRIPTION FOR INDUSTRIES*/
		.edit-tags-php.taxonomy-industry .tagcloud, .edit-tags-php.taxonomy-industry .form-field.term-description-wrap, .edit-tags-php.taxonomy-industry .form-field.term-parent-wrap{
			display: none !important;
		}

		/*HIDE INDUSTRY METABOX ITEMS*/
		#industrydiv.postbox a[href="#industry-pop"], #industrydiv.postbox #industry-adder{
			display: none !important;
		}
	</style>

    <script type="text/javascript">
	jQuery(function($){
        $(document).live('acf/setup_fields', function(e, postbox){

        });

		// coordinate functionality for masthead particle effect placement
		var $img = $('.acf-field-54da970cf8034 img');
		var $checkbox = $('#acf-field_54fe104b71f18-1');
		var $posLeft = $('#acf-field_54fe10f071f19');
		var $posTop = $('#acf-field_54fe114c71f1c');

		if($checkbox.prop('checked')){
			$img.css('cursor', 'crosshair');
		}
		$img.on('click', function(e){

			var $img = $('.acf-field-54da970cf8034 img');
			var $checkbox = $('#acf-field_54fe104b71f18-1');

		   if($checkbox.prop('checked')) {
			   var offset = $(this).offset();
			   var left_percent = (e.pageX - offset.left) / $img.width();
			   left_percent = left_percent.toFixed(2);
			   var top_percent = (e.pageY - offset.top) / $img.height();
			   top_percent = top_percent.toFixed(2);

			   $("<img/>") // Make in memory copy of image to avoid css issues
			   .attr("src", $img.attr("src"))
			   .load(function() {
				   $posLeft.val(Math.round(left_percent * this.width));
				   $posTop.val(Math.round(top_percent * this.height));
				   alert('Offset values set');
			   });
		   }
		});

	});
	</script>
<?php
}
add_action('acf/input/admin_head', 'acf_custom_admin');