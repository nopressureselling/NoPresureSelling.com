function loading(color, container, topPos, fade){
    if ($.isFunction($.fn.spin)) {
        var opts = {
            lines: 11, // The number of lines to draw
            length: 0, // The length of each line
            width: 10, // The line thickness
            radius: 20, // The radius of the inner circle
            corners: 1, // Corner roundness (0..1)
            rotate: 0, // The rotation offset
            color: color, // #rgb or #rrggbb
            speed: 1, // Rounds per second
            trail: 93, // Afterglow percentage
            shadow: false, // Whether to render a shadow
            hwaccel: false, // Whether to use hardware acceleration
            className: 'spinner', // The CSS class to assign to the spinner
            zIndex: 2e9, // The z-index (defaults to 2000000000)
            top: (topPos) ? topPos : '50%', // Top position relative to parent in px or %
            left: '50%' // Left position relative to parent in px or %
        };

        $.fn.spin = function (opts) {//Incorporates spin.js
            this.each(function () {
                var $this = $(this),
                    data = $this.data();

                if (data.spinner) {
                    data.spinner.stop();
                    delete data.spinner;
                }
                if (opts !== false) {
                    data.spinner = new Spinner($.extend({color: $this.css('color')}, opts)).spin(this);
                }
            });
            return this;
        };

        if (fade) {
            if ($('.spinner').length > 0) {
                $(container).fadeTo('normal', 1);
                $('.spinner').remove();
            } else {
                $(container).fadeTo('normal', 0.2).parent().spin(opts);
            }
        } else {
            if ($('.spinner').length > 0) {
                $('.spinner').remove();
            } else {
                $(container).spin(opts);
            }
        }
    }
}

function ajax_functionality(e){
    e.preventDefault();

    if($(this).is('select')){
        $(this).attr('data-url', $(this).val());
    }
    var dataTarget = $(this).attr('data-target');
    var dataUrl = ($(this).attr('data-url'))?$(this).attr('data-url'):$(this).attr('href');
    var dataUrlTarget = ($(this).attr('data-url-target'))?$(this).attr('data-url-target'):dataTarget;
    var dataCallback = $(this).attr('data-callback');

    if($('html').hasClass("desktop")){
        var fromTop = "200px";
    }else{
        var fromTop = false;
    }

    console.log($(dataTarget));
    console.log($(dataTarget).offsetTop());
    console.log(window.pageYOffset + window.innerHeight);

    if($(this).is('select')) {
        if ($.isFunction($.fn.scrollTo)) {
            // scroll to the H tag right before the container element if it exists, otherwise scroll to the element itself
            $prevHtag = $(dataTarget).parent().prev('h1, h2, h3, h4');
            $target = ($prevHtag.length > 0)?$prevHtag:$(dataTarget);
            $.scrollTo($target, 500);
        }
    }

    loading('#81c344', dataTarget, fromTop, true);


    var dataUrlArray = dataUrl.split('/');
    var currentUrlArray = window.location.href.split('/');

    if(dataTarget && dataUrl){
        $.ajax({
            url: dataUrl,
            dataType: "html",
            success: function(response){
                var html = $(response).find(dataUrlTarget).html();
                $(dataTarget).html(html);

               if (typeof ClickTaleExec == 'function'){
                  ClickTaleExec("$('"+dataTarget+"').html('"+html.replace(/\n/g, '')+"')");
               }

                loading('#81c344', dataTarget, false, true);

                if(dataCallback){
                    window[dataCallback](dataUrl);
                }
            }
        });
    }

    if($(this).attr('no-history') == undefined || $(this).attr('no-history') == false || $(this).attr('no-history') == "false") {
        if (history.pushState) {
            history.pushState(null, document.title, this.href);
        }

        if (!$('html').hasClass('ie')) {
            $(window).bind("popstate", function (e) {
                if (window.historySingleton === '1') {
                    window.location = "http://" + window.location.host + location.pathname;
                } else {
                    window.historySingleton = '1';
                }
            });
        }
    }

    if(typeof ga !='undefined'){
        ga('send', 'pageview');
    }
}

$(function(){
    $('body').on('click', '.ajaxy:not(select)', ajax_functionality);
    $('body').on('change', 'select.ajaxy', ajax_functionality);
});