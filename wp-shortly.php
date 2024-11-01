<?php
/*
	Plugin Name: WP Shortly
	Description: URL Shortener for WordPress
	Version: 0.2
	Author: Florian Girardey
	Author URI: http://www.florian.girardey.net
	Text Domain: shortly
	Domain Path: /languages

	License: The MIT License (MIT)


The MIT License (MIT)

Copyright (c) 2013 Florian Girardey

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
          
*/

defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

// Social NetForum Defines
define( 'WP_SHORTLY_VERSION'          , '0.2' );
define( 'WP_SHORTLY_SLUG'             , 'shortly_settings' );
define( 'WP_SHORTLY_DOMAIN'           , 'shortly' );
define( 'WP_SHORTLY_FILE'             , __FILE__ );
define( 'WP_SHORTLY_PATH'             , realpath( plugin_dir_path( WP_SHORTLY_FILE ) ).'/' );
define( 'WP_SHORTLY_INC_PATH'         , realpath ( WP_SHORTLY_PATH . 'inc/' ) .'/' );
define( 'WP_SHORTLY_INC_CLASS_PATH'   , realpath ( WP_SHORTLY_INC_PATH . 'class/' ) .'/' );
define( 'WP_SHORTLY_LIB_PATH'         , realpath ( WP_SHORTLY_PATH . 'lib/' ) .'/' );
define( 'WP_SHORTLY_ADMIN_PATH'       , realpath ( WP_SHORTLY_INC_PATH . 'admin/' ) .'/' );
define( 'WP_SHORTLY_URL'              , plugin_dir_url( WP_SHORTLY_FILE ) );
define( 'WP_SHORTLY_INC_URL'          , WP_SHORTLY_URL . 'inc/' );
define( 'WP_SHORTLY_FRONT_URL'        , WP_SHORTLY_INC_URL . 'front/' );
define( 'WP_SHORTLY_ADMIN_URL'        , WP_SHORTLY_INC_URL . 'admin/' );
define( 'WP_SHORTLY_ADMIN_JS_URL'     , WP_SHORTLY_ADMIN_URL . 'js/' );
define( 'WP_SHORTLY_ADMIN_CSS_URL'    , WP_SHORTLY_ADMIN_URL . 'css/' );
define( 'WP_SHORTLY_ADMIN_IMG_URL'    , WP_SHORTLY_ADMIN_URL . 'img/' );
define( 'WP_SHORTLY_WIDGET_PATH'      , realpath ( WP_SHORTLY_INC_PATH . 'widget/' ) .'/' );

require  WP_SHORTLY_ADMIN_PATH . 'main.php';

class Shortly
{

    /**
     * $api_key
     *
     * @var string
     *
     * @access private
     */
	private $access_token = '';


    /**
     * $bitly_oauth_api
     *
     * @var string
     *
     * @access private
     */
	private $bitly_oauth_api = 'https://api-ssl.bit.ly/v3/';





    /**
     * __construct
     * 
     * @param mixed $my_api_key Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
	public function __construct( $my_access_token ) {
		$this->access_token = $my_access_token;
	}


    /**
     * shorten
     * 
     * @param mixed $url Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
	public function shorten( $long_url, $domain = '', $x_login = '', $x_api_key = '' ) {
		$result = array();
		$url = $this->bitly_oauth_api . 'shorten?access_token=' . $this->access_token . '&longUrl=' . urlencode( $long_url );
		if ($domain != '') {
			$url .= "&domain=" . $domain;
		}
		if ($x_login != '' && $x_api_key != '') {
			$url .= "&x_login=" . $x_login . "&x_apiKey=" . $x_api_key;
		}
		$output = json_decode( $this->shortly_get_curl( $url ) );
		if ( isset( $output->{'data'}->{'hash'})  ) {
			$result['url'] = $output->{'data'}->{'url'};
			$result['hash'] = $output->{'data'}->{'hash'};
			$result['global_hash'] = $output->{'data'}->{'global_hash'};
			$result['long_url'] = $output->{'data'}->{'long_url'};
			$result['new_hash'] = $output->{'data'}->{'new_hash'};
		}
		return $result;
	}


    /**
     * shortly_get_curl
     * 
     * @param mixed $uri Description.
     *
     * @access private
     *
     * @return mixed Value.
     */
	private function shortly_get_curl( $uri ) {
		$output = "";
		try {
			$ch = curl_init($uri);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec($ch);
		}
		catch (Exception $e) {}
		return $output;
	}

}



if( !function_exists( 'shortly_get_shortlink' ) ):

    /**
     * shortly_get_shortlink
     * 
     * @param mixed $id          Description.
     * @param mixed $context     Description.
     * @param mixed $allow_slugs Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
	add_filter('pre_get_shortlink', 'shortly_get_shortlink', 10, 3);
	function shortly_get_shortlink( $id, $context, $allow_slugs ) {

		$permalink = get_permalink( $id );
        $access_token = (string) shortly_get_option( 'bitly_access_token' );

		$shortly = new Shortly( $access_token );
		$permalink = $shortly->shorten($permalink, 'bit.ly');
		
		return isset( $permalink['url'] ) ? $permalink['url'] : false;

	}

endif; // shortly_get_shortlink