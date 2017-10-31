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

class wptmModelCharts extends Model {
  

    public function getCharts($id_table) {
         global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_charts as c WHERE id_table='.(int)$id_table;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_results($query,OBJECT));
    }
    
    public function getChartTypes() {
         global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_charttypes as c Order By ordering ASC';
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_results($query,OBJECT));
    }
    
   
}