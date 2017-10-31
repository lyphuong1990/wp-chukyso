<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Model;
defined( 'ABSPATH' ) || die();

$app = Application::getInstance('wptm');

load_plugin_textdomain( 'wptm', null, dirname( plugin_basename( WPTM_PLUGIN_FILE ) ).DIRECTORY_SEPARATOR. 'app' .DIRECTORY_SEPARATOR.'languages');

add_action( 'media_buttons_context', 'wptm_button');

function wptm_button($context){
    $app = Application::getInstance('wptm'); 
    $modelConfig = Model::getInstance('config');
    $config = $modelConfig->getConfig();
    if ($config['enable_frontend'] == 1) {
        wp_enqueue_style('wptm-modal',plugins_url('app/admin/assets/css/leanmodal.css',WPTM_PLUGIN_FILE));
        wp_enqueue_script('wptm-modal',plugins_url('app/admin/assets/js/jquery.leanModal.min.js',WPTM_PLUGIN_FILE));
        wp_enqueue_script('wptm-modal-init',plugins_url('app/site/assets/js/leanmodal.init.js',WPTM_PLUGIN_FILE));
        wp_localize_script('wptm-modal-init','wptmVars',array('adminurl' => admin_url()));
         
        $context .= "<a href='#wptmmodal' class='button wptmlaunch' id='wptmlaunch' title='WP Table Manager'>"
                    . " <span class='dashicons dashicons-screenoptions' style='line-height: inherit;'></span>WP Table Manager</a>";
         $context .= "
            <script type='text/javascript'>
                jQuery(document).ready(function($){
            
                   jQuery('.wptmlaunch').wptm_leanModal({ top : 20, beforeShow: function(){jQuery('#wptmmodal').css('height','90%');jQuery('#wptmmodalframe').hide();jQuery('#wptmmodalframe').attr('src',jQuery('#wptmmodalframe').attr('src'));jQuery('#wptm_loader').show(); } });
                   return false;
                });
            </script>
        ";
    
    }
    
    return $context;
}