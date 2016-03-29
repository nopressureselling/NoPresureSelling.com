<?php
/**
 * File: Browser.php
 * Author: Chris Schuld (http://chrisschuld.com/)
 * Last Modified: August 20th, 2010
 * @version 1.9
 * @package PegasusPHP
 *
 * Copyright (C) 2008-2010 Chris Schuld  (chris@chrisschuld.com)
 *
 * Released as Contao extension "browserdetection"
 * by 2011 Jan Theofel jan@theofel.de, 2010,2011 ETES GmbH www.etes.de
 *
 * changes for Contao class/module:
 * - added config directory with browser/system array
 * - added singleton logic
 * - renamed Browser() to __construct()
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details at:
 * http://www.gnu.org/copyleft/gpl.html
 *
 *
 * Typical Usage:
 *
 *   $browser = new Browser();
 *   if( $browser->getBrowser() == Browser::BROWSER_FIREFOX && $browser->getVersion() >= 2 ) {
 *   	echo 'You have FireFox version 2 or greater';
 *   }
 *
 * User Agents Sampled from: http://www.useragentstring.com/
 *
 * This implementation is based on the original work from Gary White
 * http://apptools.com/phptools/browser/
 *
 * UPDATES:
 *
 * 2010-08-20 (v1.9):
 *  + Added MSN Explorer Browser (legacy)
 *  + Added Bing/MSN Robot (Thanks Rob MacDonald)
 *  + Added the Android Platform (PLATFORM_ANDROID)
 *  + Fixed issue with Android 1.6/2.2 (Thanks Tom Hirashima)
 *
 * 2010-04-27 (v1.8):
 *  + Added iPad Support
 *
 * 2010-03-07 (v1.7):
 *  + *MAJOR* Rebuild (preg_match and other "slow" routine removal(s))
 *  + Almost allof Gary's original code has been replaced
 *  + Large PHPUNIT testing environment created to validate new releases and additions
 *  + Added FreeBSD Platform
 *  + Added OpenBSD Platform
 *  + Added NetBSD Platform
 *  + Added SunOS Platform
 *  + Added OpenSolaris Platform
 *  + Added support of the Iceweazel Browser
 *  + Added isChromeFrame() call to check if chromeframe is in use
 *  + Moved the Opera check in front of the Firefox check due to legacy Opera User Agents
 *  + Added the __toString() method (Thanks Deano)
 *
 * 2009-11-15:
 *  + Updated the checkes for Firefox
 *  + Added the NOKIA platform
 *  + Added Checks for the NOKIA brower(s)
 *
 * 2009-11-08:
 *  + PHP 5.3 Support
 *  + Added support for BlackBerry OS and BlackBerry browser
 *  + Added support for the Opera Mini browser
 *  + Added additional documenation
 *  + Added support for isRobot() and isMobile()
 *  + Added support for Opera version 10
 *  + Added support for deprecated Netscape Navigator version 9
 *  + Added support for IceCat
 *  + Added support for Shiretoko
 *
 * 2010-04-27 (v1.8):
 *  + Added iPad Support
 *
 * 2009-08-18:
 *  + Updated to support PHP 5.3 - removed all deprecated function calls
 *  + Updated to remove all double quotes (") -- converted to single quotes (')
 *
 * 2009-04-27:
 *  + Updated the IE check to remove a typo and bug (thanks John)
 *
 * 2009-04-22:
 *  + Added detection for GoogleBot
 *  + Added detection for the W3C Validator.
 *  + Added detection for Yahoo! Slurp
 *
 * 2009-03-14:
 *  + Added detection for iPods.
 *  + Added Platform detection for iPhones
 *  + Added Platform detection for iPods
 *
 * 2009-02-16: (Rick Hale)
 *  + Added version detection for Android phones.
 *
 * 2008-12-09:
 *  + Removed unused constant
 *
 * 2008-11-07:
 *  + Added Google's Chrome to the detection list
 *  + Added isBrowser(string) to the list of functions special thanks to
 *    Daniel 'mavrick' Lang for the function concept (http://mavrick.id.au)
 *
 *
 * Gary White noted: "Since browser detection is so unreliable, I am
 * no longer maintaining this script. You are free to use and or
 * modify/update it as you want, however the author assumes no
 * responsibility for the accuracy of the detected values."
 *
 * Anyone experienced with Gary's script might be interested in these notes:
 *
 *   Added class constants
 *   Added detection and version detection for Google's Chrome
 *   Updated the version detection for Amaya
 *   Updated the version detection for Firefox
 *   Updated the version detection for Lynx
 *   Updated the version detection for WebTV
 *   Updated the version detection for NetPositive
 *   Updated the version detection for IE
 *   Updated the version detection for OmniWeb
 *   Updated the version detection for iCab
 *   Updated the version detection for Safari
 *   Updated Safari to remove mobile devices (iPhone)
 *   Added detection for iPhone
 *   Added detection for robots
 *   Added detection for mobile devices
 *   Added detection for BlackBerry
 *   Removed Netscape checks (matches heavily with firefox & mozilla)
 *
 */

class Browser {

    // make Singleton
    protected static $objInstance;

    final private function __clone() {}

    public static function getInstance()
    {
        if (!is_object(self::$objInstance))
        {
            self::$objInstance = new self();
            self::$objInstance->__construct();
        }

        return self::$objInstance;
    }


    private $_agent = '';
    private $_browser_name = '';
    private $_version = '';
    private $_platform = '';
    private $_os = '';
    private $_is_aol = false;
    private $_is_mobile = false;
    private $_is_robot = false;
    private $_aol_version = '';

    const BROWSER_UNKNOWN = 'unknown';
    const VERSION_UNKNOWN = 'unknown';

    const BROWSER_OPERA = 'Opera';                            // http://www.opera.com/
    const BROWSER_OPERA_MINI = 'Opera Mini';                  // http://www.opera.com/mini/
    const BROWSER_WEBTV = 'WebTV';                            // http://www.webtv.net/pc/
    const BROWSER_IE = 'Internet Explorer';                   // http://www.microsoft.com/ie/
    const BROWSER_POCKET_IE = 'Pocket Internet Explorer';     // http://en.wikipedia.org/wiki/Internet_Explorer_Mobile
    const BROWSER_KONQUEROR = 'Konqueror';                    // http://www.konqueror.org/
    const BROWSER_ICAB = 'iCab';                              // http://www.icab.de/
    const BROWSER_OMNIWEB = 'OmniWeb';                        // http://www.omnigroup.com/applications/omniweb/
    const BROWSER_FIREBIRD = 'Firebird';                      // http://www.ibphoenix.com/
    const BROWSER_FIREFOX = 'Firefox';                        // http://www.mozilla.com/en-US/firefox/firefox.html
    const BROWSER_ICEWEASEL = 'Iceweasel';                    // http://www.geticeweasel.org/
    const BROWSER_SHIRETOKO = 'Shiretoko';                    // http://wiki.mozilla.org/Projects/shiretoko
    const BROWSER_MOZILLA = 'Mozilla';                        // http://www.mozilla.com/en-US/
    const BROWSER_AMAYA = 'Amaya';                            // http://www.w3.org/Amaya/
    const BROWSER_LYNX = 'Lynx';                              // http://en.wikipedia.org/wiki/Lynx
    const BROWSER_SAFARI = 'Safari';                          // http://apple.com
    const BROWSER_IPHONE = 'iPhone';                          // http://apple.com
    const BROWSER_IPOD = 'iPod';                              // http://apple.com
    const BROWSER_IPAD = 'iPad';                              // http://apple.com
    const BROWSER_CHROME = 'Chrome';                          // http://www.google.com/chrome
    const BROWSER_ANDROID = 'Android';                        // http://www.android.com/
    const BROWSER_GOOGLEBOT = 'GoogleBot';                    // http://en.wikipedia.org/wiki/Googlebot
    const BROWSER_SLURP = 'Yahoo! Slurp';                     // http://en.wikipedia.org/wiki/Yahoo!_Slurp
    const BROWSER_W3CVALIDATOR = 'W3C Validator';             // http://validator.w3.org/
    const BROWSER_BLACKBERRY = 'BlackBerry';                  // http://www.blackberry.com/
    const BROWSER_ICECAT = 'IceCat';                          // http://en.wikipedia.org/wiki/GNU_IceCat
    const BROWSER_NOKIA_S60 = 'Nokia S60 OSS Browser';        // http://en.wikipedia.org/wiki/Web_Browser_for_S60
    const BROWSER_NOKIA = 'Nokia Browser';                    // * all other WAP-based browsers on the Nokia Platform
    const BROWSER_MSN = 'MSN Browser';                        // http://explorer.msn.com/
    const BROWSER_MSNBOT = 'MSN Bot';                         // http://search.msn.com/msnbot.htm
    // http://en.wikipedia.org/wiki/Msnbot  (used for Bing as well)

    const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator';  // http://browser.netscape.com/ (DEPRECATED)
    const BROWSER_GALEON = 'Galeon';                          // http://galeon.sourceforge.net/ (DEPRECATED)
    const BROWSER_NETPOSITIVE = 'NetPositive';                // http://en.wikipedia.org/wiki/NetPositive (DEPRECATED)
    const BROWSER_PHOENIX = 'Phoenix';                        // http://en.wikipedia.org/wiki/History_of_Mozilla_Firefox (DEPRECATED)

    const PLATFORM_UNKNOWN = 'unknown';
    const PLATFORM_WINDOWS = 'Windows';
    const PLATFORM_WINDOWS_CE = 'Windows CE';
    const PLATFORM_APPLE = 'Apple';
    const PLATFORM_LINUX = 'Linux';
    const PLATFORM_OS2 = 'OS/2';
    const PLATFORM_BEOS = 'BeOS';
    const PLATFORM_IPHONE = 'iPhone';
    const PLATFORM_IPOD = 'iPod';
    const PLATFORM_IPAD = 'iPad';
    const PLATFORM_BLACKBERRY = 'BlackBerry';
    const PLATFORM_NOKIA = 'Nokia';
    const PLATFORM_FREEBSD = 'FreeBSD';
    const PLATFORM_OPENBSD = 'OpenBSD';
    const PLATFORM_NETBSD = 'NetBSD';
    const PLATFORM_SUNOS = 'SunOS';
    const PLATFORM_OPENSOLARIS = 'OpenSolaris';
    const PLATFORM_ANDROID = 'Android';

    const OPERATING_SYSTEM_UNKNOWN = 'unknown';

    public function __construct($useragent="") {
        $this->reset();
        if( $useragent != "" ) {
            $this->setUserAgent($useragent);
        }
        else {
            $this->determine();
        }
    }

    /**
     * Reset all properties
     */
    public function reset() {
        $this->_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        $this->_browser_name = self::BROWSER_UNKNOWN;
        $this->_version = self::VERSION_UNKNOWN;
        $this->_platform = self::PLATFORM_UNKNOWN;
        $this->_os = self::OPERATING_SYSTEM_UNKNOWN;
        $this->_is_aol = false;
        $this->_is_mobile = false;
        $this->_is_robot = false;
        $this->_aol_version = self::VERSION_UNKNOWN;
    }

    /**
     * Check to see if the specific browser is valid
     * @param string $browserName
     * @return True if the browser is the specified browser
     */
    function isBrowser($browserName) { return( 0 == strcasecmp($this->_browser_name, trim($browserName))); }

    /**
     * The name of the browser.  All return types are from the class contants
     * @return string Name of the browser
     */
    public function getBrowser() { return $this->_browser_name; }
    /**
     * Set the name of the browser
     * @param $browser The name of the Browser
     */
    public function setBrowser($browser) { return $this->_browser_name = $browser; }
    /**
     * The name of the platform.  All return types are from the class contants
     * @return string Name of the browser
     */
    public function getPlatform() { return $this->_platform; }
    /**
     * Set the name of the platform
     * @param $platform The name of the Platform
     */
    public function setPlatform($platform) { return $this->_platform = $platform; }
    /**
     * The version of the browser.
     * @return string Version of the browser (will only contain alpha-numeric characters and a period)
     */
    public function getVersion() { return $this->_version; }
    /**
     * Set the version of the browser
     * @param $version The version of the Browser
     */
    public function setVersion($version) { $this->_version = preg_replace('/[^0-9,.,a-z,A-Z-]/','',$version); }
    /**
     * The version of AOL.
     * @return string Version of AOL (will only contain alpha-numeric characters and a period)
     */
    public function getAolVersion() { return $this->_aol_version; }
    /**
     * Set the version of AOL
     * @param $version The version of AOL
     */
    public function setAolVersion($version) { $this->_aol_version = preg_replace('/[^0-9,.,a-z,A-Z]/','',$version); }
    /**
     * Is the browser from AOL?
     * @return boolean True if the browser is from AOL otherwise false
     */
    public function isAol() { return $this->_is_aol; }
    /**
     * Is the browser from a mobile device?
     * @return boolean True if the browser is from a mobile device otherwise false
     */
    public function isMobile() { return $this->_is_mobile; }
    /**
     * Is the browser from a robot (ex Slurp,GoogleBot)?
     * @return boolean True if the browser is from a robot otherwise false
     */
    public function isRobot() { return $this->_is_robot; }
    /**
     * Set the browser to be from AOL
     * @param $isAol
     */
    public function setAol($isAol) { $this->_is_aol = $isAol; }
    /**
     * Set the Browser to be mobile
     * @param boolean $value is the browser a mobile brower or not
     */
    protected function setMobile($value=true) { $this->_is_mobile = $value; }
    /**
     * Set the Browser to be a robot
     * @param boolean $value is the browser a robot or not
     */
    protected function setRobot($value=true) { $this->_is_robot = $value; }
    /**
     * Get the user agent value in use to determine the browser
     * @return string The user agent from the HTTP header
     */
    public function getUserAgent() { return $this->_agent; }
    /**
     * Set the user agent value (the construction will use the HTTP header value - this will overwrite it)
     * @param $agent_string The value for the User Agent
     */
    public function setUserAgent($agent_string) {
        $this->reset();
        $this->_agent = $agent_string;
        $this->determine();
    }
    /**
     * Used to determine if the browser is actually "chromeframe"
     * @since 1.7
     * @return boolean True if the browser is using chromeframe
     */
    public function isChromeFrame() {
        return( strpos($this->_agent,"chromeframe") !== false );
    }
    /**
     * Returns a formatted string with a summary of the details of the browser.
     * @return string formatted string with a summary of the browser
     */
    public function __toString() {
        return "<strong>Browser Name:</strong>{$this->getBrowser()}<br/>\n" .
            "<strong>Browser Version:</strong>{$this->getVersion()}<br/>\n" .
            "<strong>Browser User Agent String:</strong>{$this->getUserAgent()}<br/>\n" .
            "<strong>Platform:</strong>{$this->getPlatform()}<br/>";
    }
    /**
     * Protected routine to calculate and determine what the browser is in use (including platform)
     */
    protected function determine() {
        $this->checkPlatform();
        $this->checkBrowsers();
        $this->checkForAol();
    }
    /**
     * Protected routine to determine the browser type
     * @return boolean True if the browser was detected otherwise false
     */
    protected function checkBrowsers() {
        return (
            // well-known, well-used
            // Special Notes:
            // (1) Opera must be checked before FireFox due to the odd
            //     user agents used in some older versions of Opera
            // (2) WebTV is strapped onto Internet Explorer so we must
            //     check for WebTV before IE
            // (3) (deprecated) Galeon is based on Firefox and needs to be
            //     tested before Firefox is tested
            // (4) OmniWeb is based on Safari so OmniWeb check must occur
            //     before Safari
            // (5) Netscape 9+ is based on Firefox so Netscape checks
            //     before FireFox are necessary
            $this->checkBrowserWebTv() ||
                $this->checkBrowserInternetExplorer() ||
                $this->checkBrowserOpera() ||
                $this->checkBrowserGaleon() ||
                $this->checkBrowserNetscapeNavigator9Plus() ||
                $this->checkBrowserFirefox() ||
                $this->checkBrowserChrome() ||
                $this->checkBrowserOmniWeb() ||

                // common mobile
                $this->checkBrowserAndroid() ||
                $this->checkBrowseriPad() ||
                $this->checkBrowseriPod() ||
                $this->checkBrowseriPhone() ||
                $this->checkBrowserBlackBerry() ||
                $this->checkBrowserNokia() ||

                // common bots
                $this->checkBrowserGoogleBot() ||
                $this->checkBrowserMSNBot() ||
                $this->checkBrowserSlurp() ||

                // WebKit base check (post mobile and others)
                $this->checkBrowserSafari() ||

                // everyone else
                $this->checkBrowserNetPositive() ||
                $this->checkBrowserFirebird() ||
                $this->checkBrowserKonqueror() ||
                $this->checkBrowserIcab() ||
                $this->checkBrowserPhoenix() ||
                $this->checkBrowserAmaya() ||
                $this->checkBrowserLynx() ||
                $this->checkBrowserShiretoko() ||
                $this->checkBrowserIceCat() ||
                $this->checkBrowserW3CValidator() ||
                $this->checkBrowserMozilla() /* Mozilla is such an open standard that you must check it last */
        );
    }

    /**
     * Determine if the user is using a BlackBerry (last updated 1.7)
     * @return boolean True if the browser is the BlackBerry browser otherwise false
     */
    protected function checkBrowserBlackBerry() {
        if( stripos($this->_agent,'blackberry') !== false ) {
            $aresult = explode("/",stristr($this->_agent,"BlackBerry"));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->_browser_name = self::BROWSER_BLACKBERRY;
            $this->setMobile(true);
            return true;
        }
        return false;
    }

    /**
     * Determine if the user is using an AOL User Agent (last updated 1.7)
     * @return boolean True if the browser is from AOL otherwise false
     */
    protected function checkForAol() {
        $this->setAol(false);
        $this->setAolVersion(self::VERSION_UNKNOWN);

        if( stripos($this->_agent,'aol') !== false ) {
            $aversion = explode(' ',stristr($this->_agent, 'AOL'));
            $this->setAol(true);
            $this->setAolVersion(preg_replace('/[^0-9\.a-z]/i', '', $aversion[1]));
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is the GoogleBot or not (last updated 1.7)
     * @return boolean True if the browser is the GoogletBot otherwise false
     */
    protected function checkBrowserGoogleBot() {
        if( stripos($this->_agent,'googlebot') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'googlebot'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion(str_replace(';','',$aversion[0]));
            $this->_browser_name = self::BROWSER_GOOGLEBOT;
            $this->setRobot(true);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is the MSNBot or not (last updated 1.9)
     * @return boolean True if the browser is the MSNBot otherwise false
     */
    protected function checkBrowserMSNBot() {
        if( stripos($this->_agent,"msnbot") !== false ) {
            $aresult = explode("/",stristr($this->_agent,"msnbot"));
            $aversion = explode(" ",$aresult[1]);
            $this->setVersion(str_replace(";","",$aversion[0]));
            $this->_browser_name = self::BROWSER_MSNBOT;
            $this->setRobot(true);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is the W3C Validator or not (last updated 1.7)
     * @return boolean True if the browser is the W3C Validator otherwise false
     */
    protected function checkBrowserW3CValidator() {
        if( stripos($this->_agent,'W3C-checklink') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'W3C-checklink'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->_browser_name = self::BROWSER_W3CVALIDATOR;
            return true;
        }
        else if( stripos($this->_agent,'W3C_Validator') !== false ) {
            // Some of the Validator versions do not delineate w/ a slash - add it back in
            $ua = str_replace("W3C_Validator ", "W3C_Validator/", $this->_agent);
            $aresult = explode('/',stristr($ua,'W3C_Validator'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->_browser_name = self::BROWSER_W3CVALIDATOR;
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is the Yahoo! Slurp Robot or not (last updated 1.7)
     * @return boolean True if the browser is the Yahoo! Slurp Robot otherwise false
     */
    protected function checkBrowserSlurp() {
        if( stripos($this->_agent,'slurp') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Slurp'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->_browser_name = self::BROWSER_SLURP;
            $this->setRobot(true);
            $this->setMobile(false);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Internet Explorer or not (last updated 1.7)
     * @return boolean True if the browser is Internet Explorer otherwise false
     */
    protected function checkBrowserInternetExplorer() {

        // Test for v1 - v1.5 IE
        if( stripos($this->_agent,'microsoft internet explorer') !== false ) {
            $this->setBrowser(self::BROWSER_IE);
            $this->setVersion('1.0');
            $aresult = stristr($this->_agent, '/');
            if( preg_match('/308|425|426|474|0b1/i', $aresult) ) {
                $this->setVersion('1.5');
            }
            return true;
        }
        // Test for versions > 1.5
        else if( stripos($this->_agent,'msie') !== false && stripos($this->_agent,'opera') === false ) {
            // See if the browser is the odd MSN Explorer
            if( stripos($this->_agent,'msnb') !== false ) {
                $aresult = explode(' ',stristr(str_replace(';','; ',$this->_agent),'MSN'));
                $this->setBrowser( self::BROWSER_MSN );
                $this->setVersion(str_replace(array('(',')',';'),'',$aresult[1]));
                return true;
            }
            $aresult = explode(' ',stristr(str_replace(';','; ',$this->_agent),'msie'));
            $this->setBrowser( self::BROWSER_IE );
            $this->setVersion(str_replace(array('(',')',';'),'',$aresult[1]));
            return true;
        }
        // Test for Pocket IE
        else if( stripos($this->_agent,'mspie') !== false || stripos($this->_agent,'pocket') !== false ) {
            $aresult = explode(' ',stristr($this->_agent,'mspie'));
            $this->setPlatform( self::PLATFORM_WINDOWS_CE );
            $this->setBrowser( self::BROWSER_POCKET_IE );
            $this->setMobile(true);

            if( stripos($this->_agent,'mspie') !== false ) {
                $this->setVersion($aresult[1]);
            }
            else {
                $aversion = explode('/',$this->_agent);
                $this->setVersion($aversion[1]);
            }
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Opera or not (last updated 1.7)
     * @return boolean True if the browser is Opera otherwise false
     */
    protected function checkBrowserOpera() {
        if( stripos($this->_agent,'opera mini') !== false ) {
            $resultant = stristr($this->_agent, 'opera mini');
            if( preg_match('/\//',$resultant) ) {
                $aresult = explode('/',$resultant);
                $aversion = explode(' ',$aresult[1]);
                $this->setVersion($aversion[0]);
            }
            else {
                $aversion = explode(' ',stristr($resultant,'opera mini'));
                $this->setVersion($aversion[1]);
            }
            $this->_browser_name = self::BROWSER_OPERA_MINI;
            $this->setMobile(true);
            return true;
        }
        else if( stripos($this->_agent,'opera') !== false ) {
            $resultant = stristr($this->_agent, 'opera');
            if( preg_match('/Version\/(10.*)$/',$resultant,$matches) ) {
                $this->setVersion($matches[1]);
            }
            else if( preg_match('/\//',$resultant) ) {
                $aresult = explode('/',str_replace("("," ",$resultant));
                $aversion = explode(' ',$aresult[1]);
                $this->setVersion($aversion[0]);
            }
            else {
                $aversion = explode(' ',stristr($resultant,'opera'));
                $this->setVersion(isset($aversion[1])?$aversion[1]:"");
            }
            $this->_browser_name = self::BROWSER_OPERA;
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Chrome or not (last updated 1.7)
     * @return boolean True if the browser is Chrome otherwise false
     */
    protected function checkBrowserChrome() {
        if( stripos($this->_agent,'Chrome') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Chrome'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_CHROME);
            return true;
        }
        return false;
    }


    /**
     * Determine if the browser is WebTv or not (last updated 1.7)
     * @return boolean True if the browser is WebTv otherwise false
     */
    protected function checkBrowserWebTv() {
        if( stripos($this->_agent,'webtv') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'webtv'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_WEBTV);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is NetPositive or not (last updated 1.7)
     * @return boolean True if the browser is NetPositive otherwise false
     */
    protected function checkBrowserNetPositive() {
        if( stripos($this->_agent,'NetPositive') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'NetPositive'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion(str_replace(array('(',')',';'),'',$aversion[0]));
            $this->setBrowser(self::BROWSER_NETPOSITIVE);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Galeon or not (last updated 1.7)
     * @return boolean True if the browser is Galeon otherwise false
     */
    protected function checkBrowserGaleon() {
        if( stripos($this->_agent,'galeon') !== false ) {
            $aresult = explode(' ',stristr($this->_agent,'galeon'));
            $aversion = explode('/',$aresult[0]);
            $this->setVersion($aversion[1]);
            $this->setBrowser(self::BROWSER_GALEON);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Konqueror or not (last updated 1.7)
     * @return boolean True if the browser is Konqueror otherwise false
     */
    protected function checkBrowserKonqueror() {
        if( stripos($this->_agent,'Konqueror') !== false ) {
            $aresult = explode(' ',stristr($this->_agent,'Konqueror'));
            $aversion = explode('/',$aresult[0]);
            $this->setVersion($aversion[1]);
            $this->setBrowser(self::BROWSER_KONQUEROR);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is iCab or not (last updated 1.7)
     * @return boolean True if the browser is iCab otherwise false
     */
    protected function checkBrowserIcab() {
        if( stripos($this->_agent,'icab') !== false ) {
            $aversion = explode(' ',stristr(str_replace('/',' ',$this->_agent),'icab'));
            $this->setVersion($aversion[1]);
            $this->setBrowser(self::BROWSER_ICAB);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is OmniWeb or not (last updated 1.7)
     * @return boolean True if the browser is OmniWeb otherwise false
     */
    protected function checkBrowserOmniWeb() {
        if( stripos($this->_agent,'omniweb') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'omniweb'));
            $aversion = explode(' ',isset($aresult[1])?$aresult[1]:"");
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_OMNIWEB);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Phoenix or not (last updated 1.7)
     * @return boolean True if the browser is Phoenix otherwise false
     */
    protected function checkBrowserPhoenix() {
        if( stripos($this->_agent,'Phoenix') !== false ) {
            $aversion = explode('/',stristr($this->_agent,'Phoenix'));
            $this->setVersion($aversion[1]);
            $this->setBrowser(self::BROWSER_PHOENIX);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Firebird or not (last updated 1.7)
     * @return boolean True if the browser is Firebird otherwise false
     */
    protected function checkBrowserFirebird() {
        if( stripos($this->_agent,'Firebird') !== false ) {
            $aversion = explode('/',stristr($this->_agent,'Firebird'));
            $this->setVersion($aversion[1]);
            $this->setBrowser(self::BROWSER_FIREBIRD);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Netscape Navigator 9+ or not (last updated 1.7)
     * NOTE: (http://browser.netscape.com/ - Official support ended on March 1st, 2008)
     * @return boolean True if the browser is Netscape Navigator 9+ otherwise false
     */
    protected function checkBrowserNetscapeNavigator9Plus() {
        if( stripos($this->_agent,'Firefox') !== false && preg_match('/Navigator\/([^ ]*)/i',$this->_agent,$matches) ) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
            return true;
        }
        else if( stripos($this->_agent,'Firefox') === false && preg_match('/Netscape6?\/([^ ]*)/i',$this->_agent,$matches) ) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Shiretoko or not (https://wiki.mozilla.org/Projects/shiretoko) (last updated 1.7)
     * @return boolean True if the browser is Shiretoko otherwise false
     */
    protected function checkBrowserShiretoko() {
        if( stripos($this->_agent,'Mozilla') !== false && preg_match('/Shiretoko\/([^ ]*)/i',$this->_agent,$matches) ) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_SHIRETOKO);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Ice Cat or not (http://en.wikipedia.org/wiki/GNU_IceCat) (last updated 1.7)
     * @return boolean True if the browser is Ice Cat otherwise false
     */
    protected function checkBrowserIceCat() {
        if( stripos($this->_agent,'Mozilla') !== false && preg_match('/IceCat\/([^ ]*)/i',$this->_agent,$matches) ) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_ICECAT);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Nokia or not (last updated 1.7)
     * @return boolean True if the browser is Nokia otherwise false
     */
    protected function checkBrowserNokia() {
        if( preg_match("/Nokia([^\/]+)\/([^ SP]+)/i",$this->_agent,$matches) ) {
            $this->setVersion($matches[2]);
            if( stripos($this->_agent,'Series60') !== false || strpos($this->_agent,'S60') !== false ) {
                $this->setBrowser(self::BROWSER_NOKIA_S60);
            }
            else {
                $this->setBrowser( self::BROWSER_NOKIA );
            }
            $this->setMobile(true);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Firefox or not (last updated 1.7)
     * @return boolean True if the browser is Firefox otherwise false
     */
    protected function checkBrowserFirefox() {
        if( stripos($this->_agent,'safari') === false ) {
            if( preg_match("/Firefox[\/ \(]([^ ;\)]+)/i",$this->_agent,$matches) ) {
                $this->setVersion($matches[1]);
                $this->setBrowser(self::BROWSER_FIREFOX);
                return true;
            }
            else if( preg_match("/Firefox$/i",$this->_agent,$matches) ) {
                $this->setVersion("");
                $this->setBrowser(self::BROWSER_FIREFOX);
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if the browser is Firefox or not (last updated 1.7)
     * @return boolean True if the browser is Firefox otherwise false
     */
    protected function checkBrowserIceweasel() {
        if( stripos($this->_agent,'Iceweasel') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Iceweasel'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_ICEWEASEL);
            return true;
        }
        return false;
    }
    /**
     * Determine if the browser is Mozilla or not (last updated 1.7)
     * @return boolean True if the browser is Mozilla otherwise false
     */
    protected function checkBrowserMozilla() {
        if( stripos($this->_agent,'mozilla') !== false  && preg_match('/rv:[0-9].[0-9][a-b]?/i',$this->_agent) && stripos($this->_agent,'netscape') === false) {
            $aversion = explode(' ',stristr($this->_agent,'rv:'));
            preg_match('/rv:[0-9].[0-9][a-b]?/i',$this->_agent,$aversion);
            $this->setVersion(str_replace('rv:','',$aversion[0]));
            $this->setBrowser(self::BROWSER_MOZILLA);
            return true;
        }
        else if( stripos($this->_agent,'mozilla') !== false && preg_match('/rv:[0-9]\.[0-9]/i',$this->_agent) && stripos($this->_agent,'netscape') === false ) {
            $aversion = explode('',stristr($this->_agent,'rv:'));
            $this->setVersion(str_replace('rv:','',$aversion[0]));
            $this->setBrowser(self::BROWSER_MOZILLA);
            return true;
        }
        else if( stripos($this->_agent,'mozilla') !== false  && preg_match('/mozilla\/([^ ]*)/i',$this->_agent,$matches) && stripos($this->_agent,'netscape') === false ) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_MOZILLA);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Lynx or not (last updated 1.7)
     * @return boolean True if the browser is Lynx otherwise false
     */
    protected function checkBrowserLynx() {
        if( stripos($this->_agent,'lynx') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Lynx'));
            $aversion = explode(' ',(isset($aresult[1])?$aresult[1]:""));
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_LYNX);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Amaya or not (last updated 1.7)
     * @return boolean True if the browser is Amaya otherwise false
     */
    protected function checkBrowserAmaya() {
        if( stripos($this->_agent,'amaya') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Amaya'));
            $aversion = explode(' ',$aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_AMAYA);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Safari or not (last updated 1.7)
     * @return boolean True if the browser is Safari otherwise false
     */
    protected function checkBrowserSafari() {
        if( stripos($this->_agent,'Safari') !== false && stripos($this->_agent,'iPhone') === false && stripos($this->_agent,'iPod') === false ) {
            $aresult = explode('/',stristr($this->_agent,'Version'));
            if( isset($aresult[1]) ) {
                $aversion = explode(' ',$aresult[1]);
                $this->setVersion($aversion[0]);
            }
            else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_SAFARI);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is iPhone or not (last updated 1.7)
     * @return boolean True if the browser is iPhone otherwise false
     */
    protected function checkBrowseriPhone() {
        if( stripos($this->_agent,'iPhone') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Version'));
            if( isset($aresult[1]) ) {
                $aversion = explode(' ',$aresult[1]);
                $this->setVersion($aversion[0]);
            }
            else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setMobile(true);
            $this->setBrowser(self::BROWSER_IPHONE);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is iPod or not (last updated 1.7)
     * @return boolean True if the browser is iPod otherwise false
     */
    protected function checkBrowseriPad() {
        if( stripos($this->_agent,'iPad') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Version'));
            if( isset($aresult[1]) ) {
                $aversion = explode(' ',$aresult[1]);
                $this->setVersion($aversion[0]);
            }
            else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setMobile(true);
            $this->setBrowser(self::BROWSER_IPAD);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is iPod or not (last updated 1.7)
     * @return boolean True if the browser is iPod otherwise false
     */
    protected function checkBrowseriPod() {
        if( stripos($this->_agent,'iPod') !== false ) {
            $aresult = explode('/',stristr($this->_agent,'Version'));
            if( isset($aresult[1]) ) {
                $aversion = explode(' ',$aresult[1]);
                $this->setVersion($aversion[0]);
            }
            else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setMobile(true);
            $this->setBrowser(self::BROWSER_IPOD);
            return true;
        }
        return false;
    }

    /**
     * Determine if the browser is Android or not (last updated 1.7)
     * @return boolean True if the browser is Android otherwise false
     */
    protected function checkBrowserAndroid() {
        if( stripos($this->_agent,'Android') !== false ) {
            $aresult = explode(' ',stristr($this->_agent,'Android'));
            if( isset($aresult[1]) ) {
                $aversion = explode(' ',$aresult[1]);
                $this->setVersion($aversion[0]);
            }
            else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setMobile(true);
            $this->setBrowser(self::BROWSER_ANDROID);
            return true;
        }
        return false;
    }

    /**
     * Determine the user's platform (last updated 1.7)
     */
    protected function checkPlatform() {
        if( stripos($this->_agent, 'windows') !== false ) {
            $this->_platform = self::PLATFORM_WINDOWS;
        }
        else if( stripos($this->_agent, 'iPad') !== false ) {
            $this->_platform = self::PLATFORM_IPAD;
        }
        else if( stripos($this->_agent, 'iPod') !== false ) {
            $this->_platform = self::PLATFORM_IPOD;
        }
        else if( stripos($this->_agent, 'iPhone') !== false ) {
            $this->_platform = self::PLATFORM_IPHONE;
        }
        elseif( stripos($this->_agent, 'mac') !== false ) {
            $this->_platform = self::PLATFORM_APPLE;
        }
        elseif( stripos($this->_agent, 'android') !== false ) {
            $this->_platform = self::PLATFORM_ANDROID;
        }
        elseif( stripos($this->_agent, 'linux') !== false ) {
            $this->_platform = self::PLATFORM_LINUX;
        }
        else if( stripos($this->_agent, 'Nokia') !== false ) {
            $this->_platform = self::PLATFORM_NOKIA;
        }
        else if( stripos($this->_agent, 'BlackBerry') !== false ) {
            $this->_platform = self::PLATFORM_BLACKBERRY;
        }
        elseif( stripos($this->_agent,'FreeBSD') !== false ) {
            $this->_platform = self::PLATFORM_FREEBSD;
        }
        elseif( stripos($this->_agent,'OpenBSD') !== false ) {
            $this->_platform = self::PLATFORM_OPENBSD;
        }
        elseif( stripos($this->_agent,'NetBSD') !== false ) {
            $this->_platform = self::PLATFORM_NETBSD;
        }
        elseif( stripos($this->_agent, 'OpenSolaris') !== false ) {
            $this->_platform = self::PLATFORM_OPENSOLARIS;
        }
        elseif( stripos($this->_agent, 'SunOS') !== false ) {
            $this->_platform = self::PLATFORM_SUNOS;
        }
        elseif( stripos($this->_agent, 'OS\/2') !== false ) {
            $this->_platform = self::PLATFORM_OS2;
        }
        elseif( stripos($this->_agent, 'BeOS') !== false ) {
            $this->_platform = self::PLATFORM_BEOS;
        }
        elseif( stripos($this->_agent, 'win') !== false ) {
            $this->_platform = self::PLATFORM_WINDOWS;
        }

    }

    public function obsolete($strText, $strTemplate)
    {
        return preg_replace('/<body([^>]*)>/', '<body$1><p class="error"><strong>The extension browserdetection is obsolete from Contao 2.10. Please uninstall and check this <a href="http://www.contao.org/blog-reader/items/forget-about-browser-hacks-in-contao-210.html">new feature</a> in the Contao core.<br />Die Erweiterung browserdetection wird ab Contao 2.10 nicht mehr ben&ouml;tigt. Bitte nutzen Sie statt dessen folgendes <a href="http://www.contao.org/blog-leser/items/vergesst-browser-hacks-in-contao-210.html">neues Contao-Core-Feature</a>.</strong>', $strText);
    }
}















/**
 * MIT License
 * ===========
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 *
 * @author      Serban Ghita <serbanghita@gmail.com>
 *              Victor Stanciu <vic.stanciu@gmail.com> (until v. 1.0)
 * @license     MIT License https://github.com/serbanghita/Mobile-Detect/blob/master/LICENSE.txt
 * @link        Official page: http://mobiledetect.net
 *              GitHub Repository: https://github.com/serbanghita/Mobile-Detect
 *              Google Code Old Page: http://code.google.com/p/php-mobile-detect/
 * @version     2.6.3
 */

class Mobile_Detect {

    protected $scriptVersion = '2.6.3';

    // External info.
    protected $userAgent = null;
    protected $httpHeaders;

    // Arrays holding all detection rules.
    protected $mobileDetectionRules = null;
    protected $mobileDetectionRulesExtended = null;
    // Type of detection to use.
    protected $detectionType = 'mobile'; // mobile, extended @todo: refactor this.

    // List of mobile devices (phones)
    protected $phoneDevices = array(
        'iPhone'        => '\biPhone.*Mobile|\biPod', // |\biTunes
        'BlackBerry'    => 'BlackBerry|\bBB10\b|rim[0-9]+',
        'HTC'           => 'HTC|HTC.*(Sensation|Evo|Vision|Explorer|6800|8100|8900|A7272|S510e|C110e|Legend|Desire|T8282)|APX515CKT|Qtek9090|APA9292KT|HD_mini|Sensation.*Z710e|PG86100|Z715e|Desire.*(A8181|HD)|ADR6200|ADR6400L|ADR6425|001HT|Inspire 4G|Android.*\bEVO\b',
        'Nexus'         => 'Nexus One|Nexus S|Galaxy.*Nexus|Android.*Nexus.*Mobile',
        // @todo: Is 'Dell Streak' a tablet or a phone? ;)
        'Dell'          => 'Dell.*Streak|Dell.*Aero|Dell.*Venue|DELL.*Venue Pro|Dell Flash|Dell Smoke|Dell Mini 3iX|XCD28|XCD35|\b001DL\b|\b101DL\b|\bGS01\b',
        'Motorola'      => 'Motorola|\bDroid\b.*Build|DROIDX|Android.*Xoom|HRI39|MOT-|A1260|A1680|A555|A853|A855|A953|A955|A956|Motorola.*ELECTRIFY|Motorola.*i1|i867|i940|MB200|MB300|MB501|MB502|MB508|MB511|MB520|MB525|MB526|MB611|MB612|MB632|MB810|MB855|MB860|MB861|MB865|MB870|ME501|ME502|ME511|ME525|ME600|ME632|ME722|ME811|ME860|ME863|ME865|MT620|MT710|MT716|MT720|MT810|MT870|MT917|Motorola.*TITANIUM|WX435|WX445|XT300|XT301|XT311|XT316|XT317|XT319|XT320|XT390|XT502|XT530|XT531|XT532|XT535|XT603|XT610|XT611|XT615|XT681|XT701|XT702|XT711|XT720|XT800|XT806|XT860|XT862|XT875|XT882|XT883|XT894|XT909|XT910|XT912|XT928',
        'Samsung'       => 'Samsung|SGH-I337|BGT-S5230|GT-B2100|GT-B2700|GT-B2710|GT-B3210|GT-B3310|GT-B3410|GT-B3730|GT-B3740|GT-B5510|GT-B5512|GT-B5722|GT-B6520|GT-B7300|GT-B7320|GT-B7330|GT-B7350|GT-B7510|GT-B7722|GT-B7800|GT-C3010|GT-C3011|GT-C3060|GT-C3200|GT-C3212|GT-C3212I|GT-C3262|GT-C3222|GT-C3300|GT-C3300K|GT-C3303|GT-C3303K|GT-C3310|GT-C3322|GT-C3330|GT-C3350|GT-C3500|GT-C3510|GT-C3530|GT-C3630|GT-C3780|GT-C5010|GT-C5212|GT-C6620|GT-C6625|GT-C6712|GT-E1050|GT-E1070|GT-E1075|GT-E1080|GT-E1081|GT-E1085|GT-E1087|GT-E1100|GT-E1107|GT-E1110|GT-E1120|GT-E1125|GT-E1130|GT-E1160|GT-E1170|GT-E1175|GT-E1180|GT-E1182|GT-E1200|GT-E1210|GT-E1225|GT-E1230|GT-E1390|GT-E2100|GT-E2120|GT-E2121|GT-E2152|GT-E2220|GT-E2222|GT-E2230|GT-E2232|GT-E2250|GT-E2370|GT-E2550|GT-E2652|GT-E3210|GT-E3213|GT-I5500|GT-I5503|GT-I5700|GT-I5800|GT-I5801|GT-I6410|GT-I6420|GT-I7110|GT-I7410|GT-I7500|GT-I8000|GT-I8150|GT-I8160|GT-I8190|GT-I8320|GT-I8330|GT-I8350|GT-I8530|GT-I8700|GT-I8703|GT-I8910|GT-I9000|GT-I9001|GT-I9003|GT-I9010|GT-I9020|GT-I9023|GT-I9070|GT-I9100|GT-I9103|GT-I9220|GT-I9250|GT-I9300|GT-I9305|GT-I9500|GT-I9505|GT-M3510|GT-M5650|GT-M7500|GT-M7600|GT-M7603|GT-M8800|GT-M8910|GT-N7000|GT-S3110|GT-S3310|GT-S3350|GT-S3353|GT-S3370|GT-S3650|GT-S3653|GT-S3770|GT-S3850|GT-S5210|GT-S5220|GT-S5229|GT-S5230|GT-S5233|GT-S5250|GT-S5253|GT-S5260|GT-S5263|GT-S5270|GT-S5300|GT-S5330|GT-S5350|GT-S5360|GT-S5363|GT-S5369|GT-S5380|GT-S5380D|GT-S5560|GT-S5570|GT-S5600|GT-S5603|GT-S5610|GT-S5620|GT-S5660|GT-S5670|GT-S5690|GT-S5750|GT-S5780|GT-S5830|GT-S5839|GT-S6102|GT-S6500|GT-S7070|GT-S7200|GT-S7220|GT-S7230|GT-S7233|GT-S7250|GT-S7500|GT-S7530|GT-S7550|GT-S7562|GT-S8000|GT-S8003|GT-S8500|GT-S8530|GT-S8600|SCH-A310|SCH-A530|SCH-A570|SCH-A610|SCH-A630|SCH-A650|SCH-A790|SCH-A795|SCH-A850|SCH-A870|SCH-A890|SCH-A930|SCH-A950|SCH-A970|SCH-A990|SCH-I100|SCH-I110|SCH-I400|SCH-I405|SCH-I500|SCH-I510|SCH-I515|SCH-I600|SCH-I730|SCH-I760|SCH-I770|SCH-I830|SCH-I910|SCH-I920|SCH-LC11|SCH-N150|SCH-N300|SCH-R100|SCH-R300|SCH-R351|SCH-R400|SCH-R410|SCH-T300|SCH-U310|SCH-U320|SCH-U350|SCH-U360|SCH-U365|SCH-U370|SCH-U380|SCH-U410|SCH-U430|SCH-U450|SCH-U460|SCH-U470|SCH-U490|SCH-U540|SCH-U550|SCH-U620|SCH-U640|SCH-U650|SCH-U660|SCH-U700|SCH-U740|SCH-U750|SCH-U810|SCH-U820|SCH-U900|SCH-U940|SCH-U960|SCS-26UC|SGH-A107|SGH-A117|SGH-A127|SGH-A137|SGH-A157|SGH-A167|SGH-A177|SGH-A187|SGH-A197|SGH-A227|SGH-A237|SGH-A257|SGH-A437|SGH-A517|SGH-A597|SGH-A637|SGH-A657|SGH-A667|SGH-A687|SGH-A697|SGH-A707|SGH-A717|SGH-A727|SGH-A737|SGH-A747|SGH-A767|SGH-A777|SGH-A797|SGH-A817|SGH-A827|SGH-A837|SGH-A847|SGH-A867|SGH-A877|SGH-A887|SGH-A897|SGH-A927|SGH-B100|SGH-B130|SGH-B200|SGH-B220|SGH-C100|SGH-C110|SGH-C120|SGH-C130|SGH-C140|SGH-C160|SGH-C170|SGH-C180|SGH-C200|SGH-C207|SGH-C210|SGH-C225|SGH-C230|SGH-C417|SGH-C450|SGH-D307|SGH-D347|SGH-D357|SGH-D407|SGH-D415|SGH-D780|SGH-D807|SGH-D980|SGH-E105|SGH-E200|SGH-E315|SGH-E316|SGH-E317|SGH-E335|SGH-E590|SGH-E635|SGH-E715|SGH-E890|SGH-F300|SGH-F480|SGH-I200|SGH-I300|SGH-I320|SGH-I550|SGH-I577|SGH-I600|SGH-I607|SGH-I617|SGH-I627|SGH-I637|SGH-I677|SGH-I700|SGH-I717|SGH-I727|SGH-i747M|SGH-I777|SGH-I780|SGH-I827|SGH-I847|SGH-I857|SGH-I896|SGH-I897|SGH-I900|SGH-I907|SGH-I917|SGH-I927|SGH-I937|SGH-I997|SGH-J150|SGH-J200|SGH-L170|SGH-L700|SGH-M110|SGH-M150|SGH-M200|SGH-N105|SGH-N500|SGH-N600|SGH-N620|SGH-N625|SGH-N700|SGH-N710|SGH-P107|SGH-P207|SGH-P300|SGH-P310|SGH-P520|SGH-P735|SGH-P777|SGH-Q105|SGH-R210|SGH-R220|SGH-R225|SGH-S105|SGH-S307|SGH-T109|SGH-T119|SGH-T139|SGH-T209|SGH-T219|SGH-T229|SGH-T239|SGH-T249|SGH-T259|SGH-T309|SGH-T319|SGH-T329|SGH-T339|SGH-T349|SGH-T359|SGH-T369|SGH-T379|SGH-T409|SGH-T429|SGH-T439|SGH-T459|SGH-T469|SGH-T479|SGH-T499|SGH-T509|SGH-T519|SGH-T539|SGH-T559|SGH-T589|SGH-T609|SGH-T619|SGH-T629|SGH-T639|SGH-T659|SGH-T669|SGH-T679|SGH-T709|SGH-T719|SGH-T729|SGH-T739|SGH-T746|SGH-T749|SGH-T759|SGH-T769|SGH-T809|SGH-T819|SGH-T839|SGH-T919|SGH-T929|SGH-T939|SGH-T959|SGH-T989|SGH-U100|SGH-U200|SGH-U800|SGH-V205|SGH-V206|SGH-X100|SGH-X105|SGH-X120|SGH-X140|SGH-X426|SGH-X427|SGH-X475|SGH-X495|SGH-X497|SGH-X507|SGH-X600|SGH-X610|SGH-X620|SGH-X630|SGH-X700|SGH-X820|SGH-X890|SGH-Z130|SGH-Z150|SGH-Z170|SGH-ZX10|SGH-ZX20|SHW-M110|SPH-A120|SPH-A400|SPH-A420|SPH-A460|SPH-A500|SPH-A560|SPH-A600|SPH-A620|SPH-A660|SPH-A700|SPH-A740|SPH-A760|SPH-A790|SPH-A800|SPH-A820|SPH-A840|SPH-A880|SPH-A900|SPH-A940|SPH-A960|SPH-D600|SPH-D700|SPH-D710|SPH-D720|SPH-I300|SPH-I325|SPH-I330|SPH-I350|SPH-I500|SPH-I600|SPH-I700|SPH-L700|SPH-M100|SPH-M220|SPH-M240|SPH-M300|SPH-M305|SPH-M320|SPH-M330|SPH-M350|SPH-M360|SPH-M370|SPH-M380|SPH-M510|SPH-M540|SPH-M550|SPH-M560|SPH-M570|SPH-M580|SPH-M610|SPH-M620|SPH-M630|SPH-M800|SPH-M810|SPH-M850|SPH-M900|SPH-M910|SPH-M920|SPH-M930|SPH-N100|SPH-N200|SPH-N240|SPH-N300|SPH-N400|SPH-Z400|SWC-E100|SCH-i909|GT-N7100|SCH-I535',
        'LG'            => '\bLG\b;|(LG|LG-)?(C800|C900|E400|E610|E900|E-900|F160|F180K|F180L|F180S|730|855|L160|LS840|LS970|LU6200|MS690|MS695|MS770|MS840|MS870|MS910|P500|P700|P705|VM696|AS680|AS695|AX840|C729|E970|GS505|272|C395|E739BK|E960|L55C|L75C|LS696|LS860|P769BK|P350|P870|UN272|US730|VS840|VS950|LN272|LN510|LS670|LS855|LW690|MN270|MN510|P509|P769|P930|UN200|UN270|UN510|UN610|US670|US740|US760|UX265|UX840|VN271|VN530|VS660|VS700|VS740|VS750|VS910|VS920|VS930|VX9200|VX11000|AX840A|LW770|P506|P925|P999)',
        'Sony'          => 'sony|SonyEricsson|SonyEricssonLT15iv|LT18i|E10i',
        'Asus'          => 'Asus.*Galaxy',
        'Palm'          => 'PalmSource|Palm', // avantgo|blazer|elaine|hiptop|plucker|xiino ; @todo - complete the regex.
        'Vertu'         => 'Vertu|Vertu.*Ltd|Vertu.*Ascent|Vertu.*Ayxta|Vertu.*Constellation(F|Quest)?|Vertu.*Monika|Vertu.*Signature', // Just for fun ;)
        // @ref: http://www.pantech.co.kr/en/prod/prodList.do?gbrand=VEGA (PANTECH)
        // Most of the VEGA devices are legacy. PANTECH seem to be newer devices based on Android.
        'Pantech'       => 'PANTECH|IM-A850S|IM-A840S|IM-A830L|IM-A830K|IM-A830S|IM-A820L|IM-A810K|IM-A810S|IM-A800S|IM-T100K|IM-A725L|IM-A780L|IM-A775C|IM-A770K|IM-A760S|IM-A750K|IM-A740S|IM-A730S|IM-A720L|IM-A710K|IM-A690L|IM-A690S|IM-A650S|IM-A630K|IM-A600S|VEGA PTL21|PT003|P8010|ADR910L|P6030|P6020|P9070|P4100|P9060|P5000|CDM8992|TXT8045|ADR8995|IS11PT|P2030|P6010|P8000|PT002|IS06|CDM8999|P9050|PT001|TXT8040|P2020|P9020|P2000|P7040|P7000|C790',
        // @ref: http://www.fly-phone.com/devices/smartphones/ ; Included only smartphones.
        'Fly'           => 'IQ230|IQ444|IQ450|IQ440|IQ442|IQ441|IQ245|IQ256|IQ236|IQ255|IQ235|IQ245|IQ275|IQ240|IQ285|IQ280|IQ270|IQ260|IQ250',
        // Added simvalley mobile just for fun. They have some interesting devices.
        // @ref: http://www.simvalley.fr/telephonie---gps-_22_telephonie-mobile_telephones_.html
        'SimValley'     => '\b(SP-80|XT-930|SX-340|XT-930|SX-310|SP-360|SP60|SPT-800|SP-120|SPT-800|SP-140|SPX-5|SPX-8|SP-100|SPX-8|SPX-12)\b',
        // @Tapatalk is a mobile app; @ref: http://support.tapatalk.com/threads/smf-2-0-2-os-and-browser-detection-plugin-and-tapatalk.15565/#post-79039
        'GenericPhone'  => 'Tapatalk|PDA;|PPC;|SAGEM|mmp|pocket|psp|symbian|Smartphone|smartfon|treo|up.browser|up.link|vodafone|wap|nokia|Series40|Series60|S60|SonyEricsson|N900|MAUI.*WAP.*Browser|LG-P500'
    );
    // List of tablet devices.
    protected $tabletDevices = array(
        'iPad'              => 'iPad|iPad.*Mobile', // @todo: check for mobile friendly emails topic.
        'NexusTablet'       => '^.*Android.*Nexus(((?:(?!Mobile))|(?:(\s(7|10).+))).)*$',
        'SamsungTablet'     => 'SAMSUNG.*Tablet|Galaxy.*Tab|SC-01C|GT-P1000|GT-P1003|GT-P1010|GT-P3105|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3108|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P7501|GT-N5100|GT-N5110|SHV-E140K|SHV-E140L|SHV-E140S|SHV-E150S|SHV-E230K|SHV-E230L|SHV-E230S|SHW-M180K|SHW-M180L|SHW-M180S|SHW-M180W|SHW-M300W|SHW-M305W|SHW-M380K|SHW-M380S|SHW-M380W|SHW-M430W|SHW-M480K|SHW-M480S|SHW-M480W|SHW-M485W|SHW-M486W|SHW-M500W|GT-I9228|SCH-P739|SCH-I925',
        // @reference: http://www.labnol.org/software/kindle-user-agent-string/20378/
        'Kindle'            => 'Kindle|Silk.*Accelerated|Android.*\b(KFTT|KFOTE)\b',
        // Only the Surface tablets with Windows RT are considered mobile.
        // @ref: http://msdn.microsoft.com/en-us/library/ie/hh920767(v=vs.85).aspx
        'SurfaceTablet'     => 'Windows NT [0-9.]+; ARM;',
        'AsusTablet'        => 'Transformer|TF101',
        'BlackBerryTablet'  => 'PlayBook|RIM Tablet',
        'HTCtablet'         => 'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200',
        'MotorolaTablet'    => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
        'NookTablet'        => 'Android.*Nook|NookColor|nook browser|BNRV200|BNRV200A|BNTV250|BNTV250A|LogicPD Zoom2',
        // @ref: http://www.acer.ro/ac/ro/RO/content/drivers
        // @ref: http://www.packardbell.co.uk/pb/en/GB/content/download (Packard Bell is part of Acer)
        'AcerTablet'        => 'Android.*\b(A100|A101|A110|A200|A210|A211|A500|A501|A510|A511|A700|A701|W500|W500P|W501|W501P|W510|W511|W700|G100|G100W|B1-A71)\b',
        // @ref: http://eu.computers.toshiba-europe.com/innovation/family/Tablets/1098744/banner_id/tablet_footerlink/
        // @ref: http://us.toshiba.com/tablets/tablet-finder
        // @ref: http://www.toshiba.co.jp/regza/tablet/
        'ToshibaTablet'     => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)|TOSHIBA.*FOLIO',
        // @ref: http://www.nttdocomo.co.jp/english/service/developer/smart_phone/technical_info/spec/index.html
        'LGTablet'          => '\bL-06C|LG-V900|LG-V909\b',
        // Prestigio Tablets http://www.prestigio.com/support
        'PrestigioTablet'   => 'PMP3170B|PMP3270B|PMP3470B|PMP7170B|PMP3370B|PMP3570C|PMP5870C|PMP3670B|PMP5570C|PMP5770D|PMP3970B|PMP3870C|PMP5580C|PMP5880D|PMP5780D|PMP5588C|PMP7280C|PMP7280|PMP7880D|PMP5597D|PMP5597|PMP7100D|PER3464|PER3274|PER3574|PER3884|PER5274|PER5474',
        // @ref: http://support.lenovo.com/en_GB/downloads/default.page?#
        'LenovoTablet'      => 'IdeaTab|S2110|S6000|K3011|A3000|A1000|A2107|A2109|A1107',
        'YarvikTablet'      => 'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)',
        'MedionTablet'      => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
        'ArnovaTablet'      => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT',
        // @reference: http://wiki.archosfans.com/index.php?title=Main_Page
        'ArchosTablet'      => 'Android.*ARCHOS|\b101G9\b|\b80G9\b',
        // @reference: http://en.wikipedia.org/wiki/NOVO7
        'AinolTablet'       => 'NOVO7|Novo7Aurora|Novo7Basic|NOVO7PALADIN',
        // @todo: inspect http://esupport.sony.com/US/p/select-system.pl?DIRECTOR=DRIVER
        // @ref: Readers http://www.atsuhiro-me.net/ebook/sony-reader/sony-reader-web-browser
        // @ref: http://www.sony.jp/support/tablet/
        'SonyTablet'        => 'Sony.*Tablet|Xperia Tablet|Sony Tablet S|SO-03E|SGPT12|SGPT121|SGPT122|SGPT123|SGPT111|SGPT112|SGPT113|SGPT211|SGPT213|SGP311|SGP312|SGP321|EBRD1101|EBRD1102|EBRD1201',
        // @ref: db + http://www.cube-tablet.com/buy-products.html
        'CubeTablet'        => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)|CUBE U8GT',
        // @ref: http://www.cobyusa.com/?p=pcat&pcat_id=3001
        'CobyTablet'        => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7015|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
        // @ref: http://www.match.net.cn/products.asp
        'MIDTablet'         => 'M9701|M9000|M9100|M806|M1052|M806|T703|MID701|MID713|MID710|MID727|MID760|MID830|MID728|MID933|MID125|MID810|MID732|MID120|MID930|MID800|MID731|MID900|MID100|MID820|MID735|MID980|MID130|MID833|MID737|MID960|MID135|MID860|MID736|MID140|MID930|MID835|MID733',
        // @ref: http://pdadb.net/index.php?m=pdalist&list=SMiT (NoName Chinese Tablets)
        // @ref: http://www.imp3.net/14/show.php?itemid=20454
        'SMiTTablet'        => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
        // @ref: http://www.rock-chips.com/index.php?do=prod&pid=2
        'RockChipTablet'    => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
        // @ref: http://www.telstra.com.au/home-phone/thub-2/
        'TelstraTablet'     => 'T-Hub2',
        // @ref: http://www.fly-phone.com/devices/tablets/ ; http://www.fly-phone.com/service/
        'FlyTablet'         => 'IQ310|Fly Vision',
        // @ref: http://www.bqreaders.com/gb/tablets-prices-sale.html
        'bqTablet'          => 'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)',
        // @ref: http://www.huaweidevice.com/worldwide/productFamily.do?method=index&directoryId=5011&treeId=3290
        // @ref: http://www.huaweidevice.com/worldwide/downloadCenter.do?method=index&directoryId=3372&treeId=0&tb=1&type=software (including legacy tablets)
        'HuaweiTablet'      => 'MediaPad|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
        // Nec or Medias Tab
        'NecTablet'         => '\bN-06D|\bN-08D',
        // Pantech Tablets: http://www.pantechusa.com/phones/
        'PantechTablet'     => 'Pantech.*P4100',
        // Broncho Tablets: http://www.broncho.cn/ (hard to find)
        'BronchoTablet'     => 'Broncho.*(N701|N708|N802|a710)',
        // @ref: http://versusuk.com/support.html
        'VersusTablet'      => 'TOUCHPAD.*[78910]',
        // @ref: http://www.zync.in/index.php/our-products/tablet-phablets
        'ZyncTablet'        => 'z1000|Z99 2G|z99|z930|z999|z990|z909|Z919|z900',
        // @ref: http://www.positivoinformatica.com.br/www/pessoal/tablet-ypy/
        'PositivoTablet'    => 'TB07STA|TB10STA|TB07FTA|TB10FTA',
        // @ref: https://www.nabitablet.com/
        'NabiTablet'        => 'Android.*\bNabi',
        'KoboTablet'        => 'Kobo Touch|\bK080\b|\bVox\b Build|\bArc\b Build',
        // French Danew Tablets http://www.danew.com/produits-tablette.php
        'DanewTablet'       => 'DSlide.*\b(700|701R|702|703R|704|802|970|971|972|973|974|1010|1012)\b',
        // Texet Tablets and Readers http://www.texet.ru/tablet/
        'TexetTablet'       => 'NaviPad|TB-772A|TM-7045|TM-7055|TM-9750|TM-7016|TM-7024|TM-7026|TM-7041|TM-7043|TM-7047|TM-8041|TM-9741|TM-9747|TM-9748|TM-9751|TM-7022|TM-7021|TM-7020|TM-7011|TM-7010|TM-7023|TM-7025|TM-7037W|TM-7038W|TM-7027W|TM-9720|TM-9725|TM-9737W|TM-1020|TM-9738W|TM-9740|TM-9743W|TB-807A|TB-771A|TB-727A|TB-725A|TB-719A|TB-823A|TB-805A|TB-723A|TB-715A|TB-707A|TB-705A|TB-709A|TB-711A|TB-890HD|TB-880HD|TB-790HD|TB-780HD|TB-770HD|TB-721HD|TB-710HD|TB-434HD|TB-860HD|TB-840HD|TB-760HD|TB-750HD|TB-740HD|TB-730HD|TB-722HD|TB-720HD|TB-700HD|TB-500HD|TB-470HD|TB-431HD|TB-430HD|TB-506|TB-504|TB-446|TB-436|TB-416|TB-146SE|TB-126SE',
        // @note: Avoid detecting 'PLAYSTATION 3' as mobile.
        'PlaystationTablet' => 'Playstation.*(Portable|Vita)',
        // @ref: http://www.galapad.net/product.html
        'GalapadTablet'     => 'Android.*\bG1\b',
        'GenericTablet'     => 'Android.*\b97D\b|Tablet(?!.*PC)|ViewPad7|BNTV250A|MID-WCDMA|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|hp-tablet|rk30sdk',
    );
    // List of mobile Operating Systems.
    protected $operatingSystems = array(
        'AndroidOS'         => 'Android',
        'BlackBerryOS'      => 'blackberry|\bBB10\b|rim tablet os',
        'PalmOS'            => 'PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino',
        'SymbianOS'         => 'Symbian|SymbOS|Series60|Series40|SYB-[0-9]+|\bS60\b',
        // @reference: http://en.wikipedia.org/wiki/Windows_Mobile
        'WindowsMobileOS'   => 'Windows CE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window Mobile|Windows Phone [0-9.]+|WCE;',
        // @reference: http://en.wikipedia.org/wiki/Windows_Phone
        // http://wifeng.cn/?r=blog&a=view&id=106
        // http://nicksnettravels.builttoroam.com/post/2011/01/10/Bogus-Windows-Phone-7-User-Agent-String.aspx
        'WindowsPhoneOS'   => 'Windows Phone OS|XBLWP7|ZuneWP7',
        'iOS'               => '\biPhone.*Mobile|\biPod|\biPad',
        // http://en.wikipedia.org/wiki/MeeGo
        // @todo: research MeeGo in UAs
        'MeeGoOS'           => 'MeeGo',
        // http://en.wikipedia.org/wiki/Maemo
        // @todo: research Maemo in UAs
        'MaemoOS'           => 'Maemo',
        'JavaOS'            => 'J2ME/|Java/|\bMIDP\b|\bCLDC\b',
        'webOS'             => 'webOS|hpwOS',
        'badaOS'            => '\bBada\b',
        'BREWOS'            => 'BREW',
    );
    // List of mobile User Agents.
    protected $userAgents = array(
        // @reference: https://developers.google.com/chrome/mobile/docs/user-agent
        'Chrome'          => '\bCrMo\b|CriOS|Android.*Chrome/[.0-9]* (Mobile)?',
        'Dolfin'          => '\bDolfin\b',
        'Opera'           => 'Opera.*Mini|Opera.*Mobi|Android.*Opera|Mobile.*OPR/[0-9.]+',
        'Skyfire'         => 'Skyfire',
        'IE'              => 'IEMobile|MSIEMobile', // |Trident/[.0-9]+
        'Firefox'         => 'fennec|firefox.*maemo|(Mobile|Tablet).*Firefox|Firefox.*Mobile',
        'Bolt'            => 'bolt',
        'TeaShark'        => 'teashark',
        'Blazer'          => 'Blazer',
        // @reference: http://developer.apple.com/library/safari/#documentation/AppleApplications/Reference/SafariWebContent/OptimizingforSafarioniPhone/OptimizingforSafarioniPhone.html#//apple_ref/doc/uid/TP40006517-SW3
        'Safari'          => 'Version.*Mobile.*Safari|Safari.*Mobile',
        // @ref: http://en.wikipedia.org/wiki/Midori_(web_browser)
        //'Midori'          => 'midori',
        'Tizen'           => 'Tizen',
        'UCBrowser'       => 'UC.*Browser|UCWEB',
        // @ref: https://github.com/serbanghita/Mobile-Detect/issues/7
        'DiigoBrowser'    => 'DiigoBrowser',
        // http://www.puffinbrowser.com/index.php
        'Puffin'            => 'Puffin',
        // @ref: http://mercury-browser.com/index.html
        'Mercury'          => '\bMercury\b',
        // @reference: http://en.wikipedia.org/wiki/Minimo
        // http://en.wikipedia.org/wiki/Vision_Mobile_Browser
        'GenericBrowser'  => 'NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision'
    );
    // Utilities.
    protected $utilities = array(
        // Experimental. When a mobile device wants to switch to 'Desktop Mode'.
        // @ref: http://scottcate.com/technology/windows-phone-8-ie10-desktop-or-mobile/
        // @ref: https://github.com/serbanghita/Mobile-Detect/issues/57#issuecomment-15024011
        'DesktopMode'   => 'WPDesktop',
        'TV'            => 'SonyDTV115', // experimental
        'WebKit'        => '(webkit)[ /]([\w.]+)',
        'Bot'           => 'Googlebot|DoCoMo|YandexBot|bingbot|ia_archiver|AhrefsBot|Ezooms|GSLFbot|WBSearchBot|Twitterbot|TweetmemeBot|Twikle|PaperLiBot|Wotbox|UnwindFetchor|facebookexternalhit',
        'MobileBot'     => 'Googlebot-Mobile|DoCoMo|YahooSeeker/M1A1-R2D2',
    );
    // Properties list.
    // @reference: http://user-agent-string.info/list-of-ua#Mobile Browser
    const VER = '([\w._\+]+)';
    protected $properties = array(

        // Build
        'Mobile'        => 'Mobile/[VER]',
        'Build'         => 'Build/[VER]',
        'Version'       => 'Version/[VER]',
        'VendorID'      => 'VendorID/[VER]',

        // Devices
        'iPad'          => 'iPad.*CPU[a-z ]+[VER]',
        'iPhone'        => 'iPhone.*CPU[a-z ]+[VER]',
        'iPod'          => 'iPod.*CPU[a-z ]+[VER]',
        //'BlackBerry'    => array('BlackBerry[VER]', 'BlackBerry [VER];'),
        'Kindle'        => 'Kindle/[VER]',

        // Browser
        'Chrome'        => array('Chrome/[VER]', 'CriOS/[VER]', 'CrMo/[VER]'),
        'Dolfin'        => 'Dolfin/[VER]',
        // @reference: https://developer.mozilla.org/en-US/docs/User_Agent_Strings_Reference
        'Firefox'       => 'Firefox/[VER]',
        'Fennec'        => 'Fennec/[VER]',
        // @reference: http://msdn.microsoft.com/en-us/library/ms537503(v=vs.85).aspx
        'IE'      => array('IEMobile/[VER];', 'IEMobile [VER]', 'MSIE [VER];'),
        // http://en.wikipedia.org/wiki/NetFront
        'NetFront'      => 'NetFront/[VER]',
        'NokiaBrowser'  => 'NokiaBrowser/[VER]',
        'Opera'         => array( ' OPR/[VER]', 'Opera Mini/[VER]', 'Version/[VER]' ),
        'UC Browser'    => 'UC Browser[VER]',
        // @note: Safari 7534.48.3 is actually Version 5.1.
        // @note: On BlackBerry the Version is overwriten by the OS.
        'Safari'        => array( 'Version/[VER]', 'Safari/[VER]' ),
        'Skyfire'       => 'Skyfire/[VER]',
        'Tizen'         => 'Tizen/[VER]',
        'Webkit'        => 'webkit[ /][VER]',

        // Engine
        'Gecko'         => 'Gecko/[VER]',
        'Trident'       => 'Trident/[VER]',
        'Presto'        => 'Presto/[VER]',

        // OS
        'iOS'              => ' \bOS\b [VER] ',
        'Android'          => 'Android [VER]',
        'BlackBerry'       => array('BlackBerry[\w]+/[VER]', 'BlackBerry.*Version/[VER]', 'Version/[VER]'),
        'BREW'             => 'BREW [VER]',
        'Java'             => 'Java/[VER]',
        // @reference: http://windowsteamblog.com/windows_phone/b/wpdev/archive/2011/08/29/introducing-the-ie9-on-windows-phone-mango-user-agent-string.aspx
        // @reference: http://en.wikipedia.org/wiki/Windows_NT#Releases
        'Windows Phone OS' => array( 'Windows Phone OS [VER]', 'Windows Phone [VER]'),
        'Windows Phone'    => 'Windows Phone [VER]',
        'Windows CE'       => 'Windows CE/[VER]',
        // http://social.msdn.microsoft.com/Forums/en-US/windowsdeveloperpreviewgeneral/thread/6be392da-4d2f-41b4-8354-8dcee20c85cd
        'Windows NT'       => 'Windows NT [VER]',
        'Symbian'          => array('SymbianOS/[VER]', 'Symbian/[VER]'),
        'webOS'            => array('webOS/[VER]', 'hpwOS/[VER];'),


    );

    public function __construct(){

        $this->setHttpHeaders();
        $this->setUserAgent();

        $this->setMobileDetectionRules();
        $this->setMobileDetectionRulesExtended();

    }


    /**
     * Get the current script version.
     * This is useful for the demo.php file,
     * so people can check on what version they are testing
     * for mobile devices.
     */
    public function getScriptVersion(){

        return $this->scriptVersion;

    }

    public function setHttpHeaders($httpHeaders = null){

        if(!empty($httpHeaders)){
            $this->httpHeaders = $httpHeaders;
        } else {
            foreach($_SERVER as $key => $value){
                if(substr($key,0,5)=='HTTP_'){
                    $this->httpHeaders[$key] = $value;
                }
            }
        }

    }

    public function getHttpHeaders(){

        return $this->httpHeaders;

    }

    public function setUserAgent($userAgent = null){

        if(!empty($userAgent)){
            $this->userAgent = $userAgent;
        } else {
            $this->userAgent    = isset($this->httpHeaders['HTTP_USER_AGENT']) ? $this->httpHeaders['HTTP_USER_AGENT'] : null;

            if(empty($this->userAgent)){
                $this->userAgent = isset($this->httpHeaders['HTTP_X_DEVICE_USER_AGENT']) ? $this->httpHeaders['HTTP_X_DEVICE_USER_AGENT'] : null;
            }
            // Header can occur on devices using Opera Mini (can expose the real device type). Let's concatenate it (we need this extra info in the regexes).
            if(!empty($this->httpHeaders['HTTP_X_OPERAMINI_PHONE_UA'])){
                $this->userAgent .= ' '.$this->httpHeaders['HTTP_X_OPERAMINI_PHONE_UA'];
            }
        }

    }

    public function getUserAgent(){

        return $this->userAgent;

    }

    function setDetectionType($type = null){

        $this->detectionType = (!empty($type) ? $type : 'mobile');

    }

    public function getPhoneDevices(){

        return $this->phoneDevices;

    }

    public function getTabletDevices(){

        return $this->tabletDevices;

    }

    /**
     * Method sets the mobile detection rules.
     *
     * This method is used for the magic methods $detect->is*()
     */
    public function setMobileDetectionRules(){
        // Merge all rules together.
        $this->mobileDetectionRules = array_merge(
            $this->phoneDevices,
            $this->tabletDevices,
            $this->operatingSystems,
            $this->userAgents
        );

    }

    /**
     * Method sets the mobile detection rules + utilities.
     * The reason this is separate is because utilities rules
     * don't necessary imply mobile.
     *
     * This method is used inside the new $detect->is('stuff') method.
     *
     * @return bool
     */
    public function setMobileDetectionRulesExtended(){

        // Merge all rules together.
        $this->mobileDetectionRulesExtended = array_merge(
            $this->phoneDevices,
            $this->tabletDevices,
            $this->operatingSystems,
            $this->userAgents,
            $this->utilities
        );

    }

    /**
     * @return array
     */
    public function getRules()
    {

        if($this->detectionType=='extended'){
            return $this->mobileDetectionRulesExtended;
        } else {
            return $this->mobileDetectionRules;
        }

    }

    /**
     * Check the HTTP headers for signs of mobile.
     * This is the fastest mobile check possible; it's used
     * inside isMobile() method.
     * @return boolean
     */
    public function checkHttpHeadersForMobile(){

        if(
            isset($this->httpHeaders['HTTP_ACCEPT']) &&
            (strpos($this->httpHeaders['HTTP_ACCEPT'], 'application/x-obml2d') !== false || // Opera Mini; @reference: http://dev.opera.com/articles/view/opera-binary-markup-language/
            strpos($this->httpHeaders['HTTP_ACCEPT'], 'application/vnd.rim.html') !== false || // BlackBerry devices.
            strpos($this->httpHeaders['HTTP_ACCEPT'], 'text/vnd.wap.wml') !== false ||
            strpos($this->httpHeaders['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false) ||
            isset($this->httpHeaders['HTTP_X_WAP_PROFILE'])             || // @todo: validate
            isset($this->httpHeaders['HTTP_X_WAP_CLIENTID'])            ||
            isset($this->httpHeaders['HTTP_WAP_CONNECTION'])            ||
            isset($this->httpHeaders['HTTP_PROFILE'])                   ||
            isset($this->httpHeaders['HTTP_X_OPERAMINI_PHONE_UA'])      || // Reported by Nokia devices (eg. C3)
            isset($this->httpHeaders['HTTP_X_NOKIA_IPADDRESS'])         ||
            isset($this->httpHeaders['HTTP_X_NOKIA_GATEWAY_ID'])        ||
            isset($this->httpHeaders['HTTP_X_ORANGE_ID'])               ||
            isset($this->httpHeaders['HTTP_X_VODAFONE_3GPDPCONTEXT'])   ||
            isset($this->httpHeaders['HTTP_X_HUAWEI_USERID'])           ||
            isset($this->httpHeaders['HTTP_UA_OS'])                     || // Reported by Windows Smartphones.
            isset($this->httpHeaders['HTTP_X_MOBILE_GATEWAY'])          || // Reported by Verizon, Vodafone proxy system.
            isset($this->httpHeaders['HTTP_X_ATT_DEVICEID'])            || // Send this on HTC Sensation. @ref: SensationXE_Beats_Z715e
            //HTTP_X_NETWORK_TYPE = WIFI
            ( isset($this->httpHeaders['HTTP_UA_CPU']) &&
            $this->httpHeaders['HTTP_UA_CPU'] == 'ARM'          // Seen this on a HTC.
            )
        ){

            return true;

        }

        return false;

    }

    /**
     * Magic overloading method.
     *
     * @method boolean is[...]()
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {

        $this->setDetectionType('mobile');

        $key = substr($name, 2);
        return $this->matchUAAgainstKey($key);

    }

    /**
     * Find a detection rule that matches the current User-agent.
     *
     * @param null $userAgent deprecated
     * @return boolean
     */
    private function matchDetectionRulesAgainstUA($userAgent = null){

        // Begin general search.
        foreach($this->getRules() as $_regex){
            if(empty($_regex)){ continue; }
            if( $this->match($_regex, $userAgent) ){
                //var_dump( $_regex );
                return true;
            }
        }

        return false;

    }

    /**
     * Search for a certain key in the rules array.
     * If the key is found the try to match the corresponding
     * regex agains the User-agent.
     *
     * @param string $key
     * @param null $userAgent deprecated
     * @return mixed
     */
    private function matchUAAgainstKey($key, $userAgent = null){

        // Make the keys lowercase so we can match: isIphone(), isiPhone(), isiphone(), etc.
        $key = strtolower($key);
        $_rules = array_change_key_case($this->getRules());

        if(array_key_exists($key, $_rules)){
            if(empty($_rules[$key])){ return null; }
            return $this->match($_rules[$key], $userAgent);
        }

        return false;

    }

    /**
     * Check if the device is mobile.
     * Returns true if any type of mobile device detected, including special ones
     * @param null $userAgent deprecated
     * @param null $httpHeaders deprecated
     * @return bool
     */
    public function isMobile($userAgent = null, $httpHeaders = null) {

        if($httpHeaders){ $this->setHttpHeaders($httpHeaders); }
        if($userAgent){ $this->setUserAgent($userAgent); }

        $this->setDetectionType('mobile');

        if ($this->checkHttpHeadersForMobile()) {
            return true;
        } else {
            return $this->matchDetectionRulesAgainstUA();
        }

    }

    /**
     * Check if the device is a tablet.
     * Return true if any type of tablet device is detected.
     *
     * @param null $userAgent deprecated
     * @param null $httpHeaders deprecated
     * @return bool
     */
    public function isTablet($userAgent = null, $httpHeaders = null) {

        $this->setDetectionType('mobile');

        foreach($this->tabletDevices as $_regex){
            if($this->match($_regex, $userAgent)){
                return true;
            }
        }

        return false;

    }

    /**
     * This method checks for a certain property in the
     * userAgent.
     * @todo: The httpHeaders part is not yet used.
     *
     * @param $key
     * @param string $userAgent deprecated
     * @param string $httpHeaders deprecated
     * @return bool|int|null
     */
    public function is($key, $userAgent = null, $httpHeaders = null){


        // Set the UA and HTTP headers only if needed (eg. batch mode).
        if($httpHeaders) $this->setHttpHeaders($httpHeaders);
        if($userAgent) $this->setUserAgent($userAgent);

        $this->setDetectionType('extended');

        return $this->matchUAAgainstKey($key);

    }

    public function getOperatingSystems(){

        return $this->operatingSystems;

    }

    /**
     * Some detection rules are relative (not standard),
     * because of the diversity of devices, vendors and
     * their conventions in representing the User-Agent or
     * the HTTP headers.
     *
     * This method will be used to check custom regexes against
     * the User-Agent string.
     *
     * @param $regex
     * @param string $userAgent
     * @return bool
     *
     * @todo: search in the HTTP headers too.
     */
    function match($regex, $userAgent=null){

        // Escape the special character which is the delimiter.
        $regex = str_replace('/', '\/', $regex);

        return (bool)preg_match('/'.$regex.'/is', (!empty($userAgent) ? $userAgent : $this->userAgent));

    }

    /**
     * Get the properties array.
     * @return array
     */
    function getProperties(){

        return $this->properties;

    }

    /**
     * Prepare the version number.
     *
     * @param $ver
     * @return int
     */
    function prepareVersionNo($ver){

        $ver = str_replace(array('_', ' ', '/'), array('.', '.', '.'), $ver);
        $arrVer = explode('.', $ver, 2);
        if(isset($arrVer[1])){
            $arrVer[1] = @str_replace('.', '', $arrVer[1]); // @todo: treat strings versions.
        }
        $ver = (float)implode('.', $arrVer);

        return $ver;

    }

    /**
     * Check the version of the given property in the User-Agent.
     * Will return a float number. (eg. 2_0 will return 2.0, 4.3.1 will return 4.31)
     *
     * @param string $propertyName
     * @param string $type
     * @return mixed $version
     */
    function version($propertyName, $type = 'text'){

        if(empty($propertyName)){ return false; }
        if( !in_array($type, array('text', 'float')) ){ $type = 'text'; }

        $properties = $this->getProperties();

        // Check if the property exists in the properties array.
        if( array_key_exists($propertyName, $properties) ){

            // Prepare the pattern to be matched.
            // Make sure we always deal with an array (string is converted).
            $properties[$propertyName] = (array)$properties[$propertyName];

            foreach($properties[$propertyName] as $propertyMatchString){

                $propertyPattern = str_replace('[VER]', self::VER, $propertyMatchString);

                // Escape the special character which is the delimiter.
                $propertyPattern = str_replace('/', '\/', $propertyPattern);

                // Identify and extract the version.
                preg_match('/'.$propertyPattern.'/is', $this->userAgent, $match);

                if(!empty($match[1])){
                    $version = ( $type == 'float' ? $this->prepareVersionNo($match[1]) : $match[1] );
                    return $version;
                }

            }

        }

        return false;

    }

    function mobileGrade(){

        $isMobile = $this->isMobile();

        if(
            // Apple iOS 3.2-5.1 - Tested on the original iPad (4.3 / 5.0), iPad 2 (4.3), iPad 3 (5.1), original iPhone (3.1), iPhone 3 (3.2), 3GS (4.3), 4 (4.3 / 5.0), and 4S (5.1)
            $this->version('iPad')>=4.3 ||
            $this->version('iPhone')>=3.1 ||
            $this->version('iPod')>=3.1 ||

            // Android 2.1-2.3 - Tested on the HTC Incredible (2.2), original Droid (2.2), HTC Aria (2.1), Google Nexus S (2.3). Functional on 1.5 & 1.6 but performance may be sluggish, tested on Google G1 (1.5)
            // Android 3.1 (Honeycomb)  - Tested on the Samsung Galaxy Tab 10.1 and Motorola XOOM
            // Android 4.0 (ICS)  - Tested on a Galaxy Nexus. Note: transition performance can be poor on upgraded devices
            // Android 4.1 (Jelly Bean)  - Tested on a Galaxy Nexus and Galaxy 7
            ( $this->version('Android')>2.1 && $this->is('Webkit') ) ||

            // Windows Phone 7-7.5 - Tested on the HTC Surround (7.0) HTC Trophy (7.5), LG-E900 (7.5), Nokia Lumia 800
            $this->version('Windows Phone OS')>=7.0 ||

            // Blackberry 7 - Tested on BlackBerry® Torch 9810
            // Blackberry 6.0 - Tested on the Torch 9800 and Style 9670
            $this->version('BlackBerry')>=6.0 ||
            // Blackberry Playbook (1.0-2.0) - Tested on PlayBook
            $this->match('Playbook.*Tablet') ||

            // Palm WebOS (1.4-2.0) - Tested on the Palm Pixi (1.4), Pre (1.4), Pre 2 (2.0)
            ( $this->version('webOS')>=1.4 && $this->match('Palm|Pre|Pixi') ) ||
            // Palm WebOS 3.0  - Tested on HP TouchPad
            $this->match('hp.*TouchPad') ||

            // Firefox Mobile (12 Beta) - Tested on Android 2.3 device
            ( $this->is('Firefox') && $this->version('Firefox')>=12 ) ||

            // Chrome for Android - Tested on Android 4.0, 4.1 device
            ( $this->is('Chrome') && $this->is('AndroidOS') && $this->version('Android')>=4.0 ) ||

            // Skyfire 4.1 - Tested on Android 2.3 device
            ( $this->is('Skyfire') && $this->version('Skyfire')>=4.1 && $this->is('AndroidOS') && $this->version('Android')>=2.3 ) ||

            // Opera Mobile 11.5-12: Tested on Android 2.3
            ( $this->is('Opera') && $this->version('Opera Mobi')>11 && $this->is('AndroidOS') ) ||

            // Meego 1.2 - Tested on Nokia 950 and N9
            $this->is('MeeGoOS') ||

            // Tizen (pre-release) - Tested on early hardware
            $this->is('Tizen') ||

            // Samsung Bada 2.0 - Tested on a Samsung Wave 3, Dolphin browser
            // @todo: more tests here!
            $this->is('Dolfin') && $this->version('Bada')>=2.0 ||

            // UC Browser - Tested on Android 2.3 device
            ( ($this->is('UC Browser') || $this->is('Dolfin')) && $this->version('Android')>=2.3 ) ||

            // Kindle 3 and Fire  - Tested on the built-in WebKit browser for each
            ( $this->match('Kindle Fire') ||
            $this->is('Kindle') && $this->version('Kindle')>=3.0 ) ||

            // Nook Color 1.4.1 - Tested on original Nook Color, not Nook Tablet
            $this->is('AndroidOS') && $this->is('NookTablet') ||

            // Chrome Desktop 11-21 - Tested on OS X 10.7 and Windows 7
            $this->version('Chrome')>=11 && !$isMobile ||

            // Safari Desktop 4-5 - Tested on OS X 10.7 and Windows 7
            $this->version('Safari')>=5.0 && !$isMobile ||

            // Firefox Desktop 4-13 - Tested on OS X 10.7 and Windows 7
            $this->version('Firefox')>=4.0 && !$isMobile ||

            // Internet Explorer 7-9 - Tested on Windows XP, Vista and 7
            $this->version('MSIE')>=7.0 && !$isMobile ||

            // Opera Desktop 10-12 - Tested on OS X 10.7 and Windows 7
            // @reference: http://my.opera.com/community/openweb/idopera/
            $this->version('Opera')>=10 && !$isMobile


        ){
            return 'A';
        }

        if(
            // Blackberry 5.0: Tested on the Storm 2 9550, Bold 9770
            $this->version('BlackBerry')>=5 && $this->version('BlackBerry')<6 ||

            //Opera Mini (5.0-6.5) - Tested on iOS 3.2/4.3 and Android 2.3
            ( $this->version('Opera Mini')>=5.0 && $this->version('Opera Mini')<=6.5 &&
            ($this->version('Android')>=2.3 || $this->is('iOS')) ) ||

            // Nokia Symbian^3 - Tested on Nokia N8 (Symbian^3), C7 (Symbian^3), also works on N97 (Symbian^1)
            $this->match('NokiaN8|NokiaC7|N97.*Series60|Symbian/3') ||

            // @todo: report this (tested on Nokia N71)
            $this->version('Opera Mobi')>=11 && $this->is('SymbianOS')

        ){
            return 'B';
        }

        if(
            // Blackberry 4.x - Tested on the Curve 8330
            $this->version('BlackBerry')<5.0 ||
            // Windows Mobile - Tested on the HTC Leo (WinMo 5.2)
            $this->match('MSIEMobile|Windows CE.*Mobile') || $this->version('Windows Mobile')<=5.2


        ){

            return 'C';

        }

        // All older smartphone platforms and featurephones - Any device that doesn't support media queries will receive the basic, C grade experience
        return 'C';


    }
}
?>