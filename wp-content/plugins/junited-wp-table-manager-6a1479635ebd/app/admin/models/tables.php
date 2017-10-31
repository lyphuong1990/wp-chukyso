<?php
/**
    * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Model;

defined( 'ABSPATH' ) || die();

class wptmModelTables extends Model {
  

    public function getItems() {
        global $wpdb;
        $show_all = true;
        $user_id = get_current_user_id();
        if (!user_can($user_id, 'edit_others_posts')) {            
            $show_all = false;
        }       
        
        if($show_all) {
            $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_tables as c ORDER BY c.id_category ASC, c.position ASC ';
        }else {
            $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_tables as c WHERE c.author = '.$user_id.' ORDER BY c.id_category ASC, c.position ASC ';
        }
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_results($query,OBJECT));
    }
    
    
}