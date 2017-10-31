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

class wptmModelTable extends Model {
          
    public function getItem($id) {
        global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_tables as c WHERE c.id='.(int)$id;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_row($query,OBJECT));
    }
    
    public function getTableFromChartId($id_chart) {
        global $wpdb;
        $query = 'SELECT t.* FROM '.$wpdb->prefix.'wptm_charts c LEFT JOIN '.$wpdb->prefix.'wptm_tables as t ON t.id=c.id_table WHERE c.id='.(int)$id_chart;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_row($query,OBJECT));
    }
    
}