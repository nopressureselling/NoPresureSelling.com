/* ==========================================================
 * LIBRARIES
 ========================================================= */

// @codekit-prepend "../bower_components/bootstrap/js/transition.js"
// @codekit-prepend "../bower_components/bootstrap/js/affix.js"
// @codekit-prepend "../bower_components/bootstrap/js/alert.js"
// @codekit-prepend "../bower_components/bootstrap/js/button.js"
// @codekit-prepend "../bower_components/bootstrap/js/carousel.js"
// @codekit-prepend "../bower_components/bootstrap/js/collapse.js"
// @codekit-prepend "../bower_components/bootstrap/js/dropdown.js"
// @codekit-prepend "../bower_components/bootstrap/js/modal.js"
// @codekit-prepend "../bower_components/bootstrap/js/tooltip.js"
// @codekit-prepend "../bower_components/bootstrap/js/popover.js"
// @codekit-prepend "../bower_components/bootstrap/js/scrollspy.js"
// @codekit-prepend "../bower_components/bootstrap/js/tab.js"
// @codekit-prepend "../bower_components/components-modernizr/modernizr.js"
// @codekit-prepend "../bower_components/readmore/readmore.min.js"
// @codekit-prepend "../bower_components/spin.js/spin.js"
// @codekit-prepend "../bower_components/spin.js/jquery.spin-min.js"
// @codekit-prepend "../bower_components/jquery.scrollTo/jquery.scrollTo.min.js"
// @codekit-prepend "../bower_components/owlcarousel/owl-carousel/owl.carousel.js"
// @codekit-prepend "../bower_components/jquery-placeholder/jquery.placeholder.js"

/* ==========================================================
 * MISC SCRIPTS
 ========================================================= */

// @codekit-prepend "./functions.js"
// @codekit-prepend "./ajaxy.js"

/* ==========================================================
 * COMPONENTS
 ========================================================= */

// @codekit-prepend "./components/sidebar.js"
// @codekit-prepend "./components/masthead.js"
// @codekit-prepend "./components/testimonials.js"
// @codekit-prepend "./components/logos.js"
// @codekit-prepend "./components/video-thumbnail.js"

/* ==========================================================
 * GLOBAL WINDOW RESIZE FUNCTION
 ========================================================= */

timer = setTimeout(function(){}, 1);
function resize(w){

    //SPECIFIC SIZES

        if( media_is('xs') ){


        }

        if( media_is('sm') ){


        }

        if( media_is('md') ){


        }

        if( media_is('lg') ){


        }

    // ALL
    updateParticle();


    // CASCADING SIZES

        // > xs
        if( media_is('sm') || media_is('md') || media_is('lg') ){

        }

        // > sm
        if( media_is('md') || media_is('lg') ){
            // Primary Nav
            $('#primary-nav .custom-box').each(function(){

                //var $this = $(this),
                //    height = $this.closest('.ubermenu-submenu').outerHeight();
                //$this.find('a').css('height', height);

            });

        }

        // > md
        if( media_is('lg') ){

        }

        // < lg
        if( media_is('xs') || media_is('sm') || media_is('md') ){

        }

        // < md
        if( media_is('xs') || media_is('sm') ){


        }
}

function media_is(className){
    var $item = $('<div class="visible-' + className + '"></div>'),
        visible;

    $body.append($item);
    visible = ($item.css('display') != 'none');
    $item.remove();
    return visible;
}

function updateParticle() {
    $("<img/>") // Make in memory copy of image to avoid css issues
   .attr("src", $('.masthead').attr("data-url"))
   .load(function() {
        var $particle = $('.masthead #particle');
        var mastheadWidth = $('.masthead').width();
        var mastheadHeight = $('.masthead').height();
        var imageWidth = this.width;
        var imageHeight = this.height;

        var xScale = mastheadWidth / imageWidth;
        var yScale = mastheadHeight / imageHeight;
        var scale;
        var yOffset = 0;
        var xOffset = 0;

        if (xScale > yScale) {
            // The image fits perfectly in x axis, stretched in y
            scale = xScale;
            yOffset = (mastheadHeight - (imageHeight * scale)) / 2;
        } else {
            // The image fits perfectly in y axis, stretched in x
            scale = yScale;
            xOffset = (mastheadWidth - (imageWidth * scale)) / 2;
        }

        $particle.css('top', ($particle.attr('data-offset-top')) * scale + yOffset);
        $particle.css('left', ($particle.attr('data-offset-left')) * scale + xOffset);
        $particle.css('display', 'block');
   });
    // Get largest dimension increase
}

/* ==========================================================
 * GLOBAL DOM READY FUNCTION
 ========================================================= */

$(function(){

    window.$body = $('body');
    window.$html = $('html');
    window.$window = $(window);
    window.$document = $(document);
    window.base_url = $('meta[name="base_url"]').attr('content');
    window.template_url = $('meta[name="theme_url"]').attr('content');

    // SVG Fallback //////
    if (!Modernizr.svg){
      $('[data-img="svg"]').each(function(){
          var _this = $(this),
              fallback = _this.attr('data-fallback');
          _this.attr('src', fallback);
      });
    }

    // Placeholder polyfill
    if(Modernizr.input.placeholder){
        console.log('meh');
        $('input').is('[placeholder]').placeholder();
    }

    //GLOBAL LOGIC /////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

        // Header
        var header_nav_height = 36;

        if( $window.scrollTop() >= header_nav_height ){
            $body.addClass('sticky-header');
        }
        $window.scroll(function(){
            if( $window.scrollTop() >= header_nav_height ){
                $body.addClass('sticky-header sticky-animation');
            }else{
                $body.removeClass('sticky-header');
            }
        });

        // Read More Link
        $('.read-more.with-collapse').readmore({
            speed: 200,
            collapsedHeight: 140,
            moreLink: '<a href="#">READ MORE</a>',
            lessLink: '<a href="#">CLOSE</a>',
            afterToggle: function(trigger, element, expanded) {
                if(expanded) { // The "Read More" link was clicked
                    $(element).find('.bottom-fade-out').hide();
                }else{
                    $(element).find('.bottom-fade-out').show();
                }
            }
        });
        $('.read-more.with-collapse').each(function(){
            // 140 is the collapsedHeight var and 16 is the default threshold

            if(typeof $(this).attr('data-readmore') !== typeof undefined && $(this).attr('data-readmore') !== false) {
                $(this).append('<div class="bottom-fade-out"></div>');
            }
        });

        // Infusionsoft Form
        $('.infusion-form .infusion-field').each(function(){
            $input = $(this).find('.infusion-field-input-container');
            var label = $input.siblings('label').text();
            $input.attr('placeholder', label);
        });
        $('.infusion-form .infusion-submit input').replaceWith('<button class="btn btn-default" type="submit">Submit</button>');

        // Infustionsoft Promotion Form
        $('body.home .cta-right .promo-form-btn, .component.promotion-container.sidebar .promo-form-btn, .component.promotion-container.banner .promo-form-btn').on('click', function(){
            var $this = $(this);
            $('#the-modal .modal-title').text( $this.attr('data-title') );
            $('#the-modal .modal-body').html( $this.siblings('.hidden-form').html() );
            $('#the-modal').modal('show');
        });


    //PAGE LOGIC ///////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

        // Home
        if( $body.hasClass('home') ){

            // Dropdown
            var $industry_dropdown = $('.industry-dropdown');

            $industry_dropdown.find('.dropdown').on('click',function(e){
                e.stopPropagation();
                if($industry_dropdown.hasClass('open') && !$industry_dropdown.hasClass('closing')){
                    $industry_dropdown.addClass('closing');
                    setTimeout(function(){
                        $industry_dropdown.removeClass('open closing');
                    },1000);
                }else{
                    $industry_dropdown.addClass('open');
                }
            });
            $document.on('click', function(){
                $industry_dropdown.addClass('closing');
                setTimeout(function(){
                    $industry_dropdown.removeClass('open closing');
                },500);
            });
        }


    //TRIGGER RESIZE FUNCTION ON WINDOW LOAD //////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////

    $window.on('load',function(){

        $window.resize(function(){
            // must fire immediately, can't wait for timeout
            var $particle = $('.masthead #particle');
            $particle.css('display', 'none');

            clearTimeout(timer);
            timer = setTimeout(function(){
            $(this).trigger('resizeEnd');
            }, 100);
        });

        $window.on('resizeEnd', function(){
          resize($window.width());
        });
        resize($window.width());

    });

});