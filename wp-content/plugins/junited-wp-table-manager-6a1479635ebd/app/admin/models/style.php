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

class wptmModelStyle extends Model {
    
    public function getItem($id) {
        global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_styles as c WHERE c.id='.(int)$id;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_row($query,OBJECT));
    }
}

