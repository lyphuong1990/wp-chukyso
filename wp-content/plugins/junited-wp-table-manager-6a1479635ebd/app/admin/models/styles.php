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

class wptmModelStyles extends Model {
  

    public function getStyles() {
         global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_styles as c ORDER BY c.ordering ASC';
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_results($query,OBJECT));
    }
    
    
}