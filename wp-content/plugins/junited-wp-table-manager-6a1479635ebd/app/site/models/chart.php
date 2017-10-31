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

class wptmModelChart extends Model {
  
   
    public function getChart($id) {
        global $wpdb;
        $query = 'SELECT c.*, t.datas As tData FROM '.$wpdb->prefix.'wptm_charts as c '
                . ' JOIN '.$wpdb->prefix.'wptm_tables As t On t.id = c.id_table '
                . ' WHERE c.id='.(int)$id;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_row($query,OBJECT));
    }
    
    
}