/*
    Name: YouTubePopUp | Special Version for WordPress
    Description: jQuery plugin to display YouTube or Vimeo video in PopUp, responsive and retina, easy to use.
    Version: 1.0.8
    Plugin URL: https://wp-time.com/youtube-popup-jquery-plugin/
    Written By: Qassim Hassan
    Twitter: @QQQHZ
    Websites: wp-time.com | qass.im | wp-plugins.in
    Dual licensed under the MIT and GPL licenses:
        http://www.opensource.org/licenses/mit-license.php
        http://www.gnu.org/licenses/gpl.html
    Copyright (c) 2018 - Qassim Hassan
*/

jQuery(function(){
    jQuery("a.vp-a").YouTubePopUp();
    jQuery("a.vp-s").YouTubePopUp( { autoplay: 0 } ); // Disable autoplay
});