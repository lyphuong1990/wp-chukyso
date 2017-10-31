<?php
/*
    Plugin Name: WP Contact Slider
	Plugin URI:	http://www.wpexperts.io/
    Description: Simple Contact Slider to display Contact Form 7, Gravity Forms, some other shortcodes and dispaly random Text or HTML.
    Author: wpexpertsio
	Author URI: http://www.wpexperts.io/
    Version: 1.9.7
*/

// Integration
if(is_admin())
 require_once( 'inc/wpcs_freemius.php' );

if( ( isset($_GET['post_type']) && $_GET['post_type'] == 'wpcs' ) || ( isset($_GET['post'])  && get_post_type( $_GET['post'] ) == 'wpcs'  ) || ( isset($_POST['wpcs_lable_text_color']) || ( isset($_GET['action']) && $_GET['action'] == 'trash' ) ) ){ // checkiing

 // For meta-box support
 if( is_admin() && ! class_exists( 'RW_Meta_Box' ) )
  require_once( 'inc/meta-box/meta-box.php' );

 // Declaring Meta fields
  require_once( 'inc/wpcs_meta_fields.php' );

}

// For admin functions support

require_once( 'inc/wpcs_admin_functions.php' );

// To call front end functions

require_once( 'inc/wpcs_frontend_functions.php' );

// Get CSS

require_once( 'inc/wpcs_enque_styles.php' );

// Get Scripts

require_once( 'inc/wpcs_enque_scripts.php' );
