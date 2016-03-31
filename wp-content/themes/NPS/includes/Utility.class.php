<?php
class Utility {
    public $warnings = array();
    public $errors = array();
    public $includes = "";
    public $html_classes = "";

    public function __construct($form = array()){
        $this->form = $form;

        if(file_exists(__DIR__.'/Browser.class.php')){
	        include_once(__DIR__.'/Browser.class.php');
            $browser = new Browser();
        }else{
            array_push($this->warnings, "Couldn't find Browser class in the same directory as the Utility class in ".__FILE__." on line ".__LINE__);
        }

        if(file_exists(__DIR__.'/MobileDetect.class.php')){
	        include_once(__DIR__.'/MobileDetect.class.php');
            $mobile = new MobileDetect();
        }else{
            array_push($this->warnings, "Couldn't find MobileDetect class in the same directory as the Utility class ".__FILE__." on line ".__LINE__);
        }

        if( isset($browser) && isset($mobile) ){
            $this->generate_classes($browser->getBrowser(), $browser->getPlatform(), $browser->getVersion(), $mobile->isMobile(), $mobile->isTablet());
        }

        $this->zzid();
    }

    // HELPER FUNCTIONS
    public function lc_words($str){

		// search pattern
	    $pattern = '#\b([a-z])#i';

		// the function call
	    $result = preg_replace_callback($pattern, array($this,'lc_words_cb'), $str);

		return $result;
    }

	function lc_words_cb ( $matches ) {

		$string = strtolower($matches[0]);

		return $string;
	}


	public function update_form_elements($key, $val){

        $this->form[$key] = array(
            "type"		=>		"hidden",
            "db_name"	=>		$key,
            "value" 	=>		$val
        );
    }

    public function return_warnings_errors(){
        echo("<pre>");
        if(count($this->warnings) > 0){
            echo("WARNINGS<br />");
            foreach($this->warnings as $warning){
                echo("&bull; $warning<br />");
            }
            echo("<br />");
        }else{
            echo("NO WARNINGS<br /><br />");
        }
        if(count($this->errors) > 0){
            echo("ERRORS<br />");
            foreach($this->errors as $error){
                echo("&bull; $error<br />");
            }
            echo("<br />");
        }else{
            echo("NO ERRORS<br /><br />");
        }
        echo("</pre>");
    }

    /*
     * Description
     * adds GA embed code to $includes
     *
     * Parameters
     * @param   string  $ga_account - (required) Account name
     *
     */
    public function google_analytics($ga_account){
        if($ga_account){
            return <<<GACODE
                <script>
                  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

                  ga('create', '$ga_account', 'zionandzion.com');
                  ga('send', 'pageview');

                </script>
GACODE;
        }else{
            array_push($this->errors, "Called google_analytics() without the account ID parameter ".__FILE__." on line ".__LINE__);
        }

    }


    /*
     * Description
     * constructs $html_classes variable for output as dom element classes
     *
     * Parameters
     * @param   string  $browser_name       - (required) Browser name from Browser PHP class
     * @param   string  $browser_platform   - (required) Browser platform from Browser PHP class
     * @param   string  $browser_version    - (required) Browser version from Browser PHP class
     * @param   boolean $is_mobile          - Boolean response from Mobile_Detect PHP class if request is from mobile
     * @param   boolean $is_tablet          - Boolean response from Mobile_Detect PHP class if request is from tablet
     *
     */
    public function generate_classes($browser_name, $browser_platform, $browser_version, $is_mobile = false, $is_tablet = false){
        $browser_name = $this->lc_words(str_replace(" ","-", $browser_name));
        $browser_platform = $this->lc_words(str_replace(" ","-", $browser_platform));
        $browser_platform = ($browser_platform == "apple")?"mac":$browser_platform;
        $browser_version = explode(".",$browser_version);
        $browser_version = $browser_version[0];
        $browser_version = $browser_name."-".$browser_version;
        $is_mobile = ($is_mobile)?"mobile":"";
        $is_tablet = ($is_tablet)?"tablet":"";
        $is_phone = ($is_mobile && !$is_tablet)?"phone":"";

        $this->html_classes = $browser_name." ".$browser_platform." ".$browser_version." ".$is_phone." ".$is_tablet." ".$is_mobile;
    }


    /*
     * Description
     * If ZZID is found, store it as a cookie incase the user navigates to other pages or comes back to the page and insert the ZZID into the form array to be passed along with the DB and email.
     *
     * Returns
     * the zzid if it exists
     */
    public function zzid(){
        $zzid = isset($_GET['zzid']) ? esc_html( $_GET['zzid'] ) : false;

        if($zzid){
            if($_COOKIE['zzid'] != $zzid){
                setcookie('zzid', $zzid, time()+3600*24*365, '/'); // 365 day expiration
            }
            $this->update_form_elements("zzid", $zzid);
            return $zzid;
        }else{
            array_push($this->warnings, "Called zzid() and no ZZID parameter found via \$_GET ".__FILE__." on line ".__LINE__);
        }
    }


    /*
     * Description
     * Adds Callcap embed code to $includes variable
     *
     * Parameters
     * @param   string  $callcap_id - (required) Callcap ID to be passed into the embed code
     *
     * Returns
     * @return returnVar
     */
    public function callcap($callcap_id){
        if($callcap_id){
            $zzid = $this->zzid();

            $this->includes .= <<<CALLCAP
                <!-- CALLCAP CODE -->
                <script src="//reports.callcap.com/track/v1.js" type="text/javascript"></script>
                <script language="javascript">
                    webcap({u : '$callcap_id', k : '', c : '$zzid', a : '', p : ''});
                </script>
                <!-- END CALLCAP CODE -->
CALLCAP;
        }else{
            array_push($this->errors, "Called callcap() without the ID parameter ".__FILE__." on line ".__LINE__);
        }

    }


    /*
     * Description
     * Adds Callcap rotator embed code to $includes variable, callcap writes phone number result into #callcap_phone_number
     *
     * Parameters
     * @param   string  $callcap_id     - (required) Callcap ID used to determine rotator group
     * @param   number  $polling_int    - the number of seconds you want to check callcap for a phonecall, defaults to 5
     * @param   string  $class_name     - class name where callcap will output the phone number, defaults to 'callcap_phone_number'
     *
     */
    public function callcap_rotator($callcap_id, $pollingInt = 5, $class_name = 'callcap_phone_number'){
        if($callcap_id){
            $zzid = $this->zzid();

            $this->includes .= <<<CCROTATOR
                <script src="//api.callcap.com/track/webmatch.js" type="text/javascript"></script>
                <script language="javascript">
                    var timingInt = $pollingInt;
                    var webmatch = new Webmatch( {
                        phone_format: 'paren',
                        instance_name: 'webmatch',
                        instance_class: '$class_name',
                        rotate: '$callcap_id',
                        k: '', //Keyword ID (This is generally used to track keyword campaigns. This is separate from the organic search term automatically captured)
                        c: '$zzid', //Creative ID (This is generally used to track different versions of an ad or landing page, such as for A/B testing, etc.)
                        a: '', //Ad Group (Can be used to track multiple ads in the same Ad Group.)
                        p: '', //Ad Placement (This can be used to track different placement options for ads.)
                        pull_parameters: false,
                        pollinginterval: timingInt*1000
                    } ).init();

                    function callcap_webmatch_callback(data) {
                        if(console){
                            console.log(data);
                        }
                        if (!data.error) {
                            if (data.lastcall < timingInt+10) {
                                  if(!window.callSuccess){
                                        if(console){
                                            console.log('likely call');
                                        }

                                        ga('send', 'event', 'phone_number', 'likely_dialed', {'nonInteraction': 1});
                                        _gaq.push(['_trackPageview', '/likely_phone_call']);

                                        window.callSuccess = true;
                                  }
                            }
                        } else {
                            logevent(data.error);
                        }
                    }
                </script>
CCROTATOR;
        }else{
            array_push($this->errors, "Called callcap_rotator() without the ID parameter ".__FILE__." on line ".__LINE__);
        }

    }


    /*
     * NOT TESTED YET!!
     *
     * Description
     * Adds clicktale embed code to $includes variable
     *
     * Parameters
     * @param   string  $chart  - (required) FusionChart name for the type of chart you wish to render
     *
     */
    public function clicktale(){
        $this->clicktale .= <<<CLICKTALE
            <div id="ClickTaleDiv" style="display: none;"></div>
            <script type='text/javascript'>
                var WRInitTime=(new Date()).getTime();
                document.write(unescape("%3Cscript%20src='"+
                        (document.location.protocol=='https:'?
                                'https://clicktalecdn.sslcs.cdngc.net/www/':
                                'http://s.clicktale.net/')+
                        "WRd.js'%20type='text/javascript'%3E%3C/script%3E"));


                var ClickTaleSSL=1;
                if(typeof ClickTale=='function') ClickTale(11337,1,"www07");

                function ClickTaleOnRecording(){
                        _gaq.push(['_setCustomVar', 1, "CTUID", ClickTaleGetUID(), 1]);
                        _gaq.push(['_setCustomVar', 2, "CTSID", ClickTaleGetSID(), 3]);
                        _gaq.push(['_trackEvent', 'Flush_Custom_Var_From_Queue', 'Flush_Custom_Var_From_Queue', 'Flush_Custom_Var_From_Queue',0,true]);
                }
            </script>
CLICKTALE;
    }
}