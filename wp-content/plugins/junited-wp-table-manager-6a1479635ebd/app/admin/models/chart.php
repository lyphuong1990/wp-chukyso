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
  
     public function save($id,$datas) {
         global $wpdb;      
         $result = $wpdb->update( $wpdb->prefix."wptm_charts" , 
                        array( 'type' => $datas['type'],'config'=>$datas['config'] ),
                        array( 'id' => (int)$id ) );
        
        if($result===false) {          
            echo $wpdb->last_query ;
            exit();
        }
        if($result==0) {
            $result = $id;
        }
          
        return $result;
    }
    
  function add($id_table,$datas) {
       global $wpdb;
        $wpdb->query( $wpdb->prepare(
				"
                                    INSERT INTO ".$wpdb->prefix."wptm_charts (id_table, title, datas, type, created_time, modified_time, author) VALUES 
                                    ( %d,%s,%s,%s,%s,%s,%d)
				",
				$id_table,__('New chart','wptm'),$datas,'Line',date("Y-m-d H:i:s"),date("Y-m-d H:i:s"),get_current_user_id()
			) );
        return $wpdb->insert_id;
  }  
      
    public function delete($id) {
        global $wpdb;
        $result = $wpdb->delete( $wpdb->prefix."wptm_charts" , array( 'id' => (int)$id ) );
       
        return $result;
    }
    
    public function setTitle($id, $title) {
        global $wpdb;
        $result = $wpdb->update( $wpdb->prefix."wptm_charts" , array( 'title' => $title ), array( 'id' => (int)$id ) );
       
        return $result;
    }
    
    public function getItem($id) {
        global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_charts as c WHERE c.id='.(int)$id;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_row($query,OBJECT));
    }
    
    public function getChartType($id) {
        global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_charttypes as c WHERE c.id='.(int)$id;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_row($query,OBJECT));
    }
    
}