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
  
    public function save($id_table,$datas) {
         global $wpdb;
         $hash = md5($datas['style'].$datas['css']);
       
         if(empty($datas['datas']) ) {
             $result = $wpdb->update( $wpdb->prefix."wptm_tables" , 
                        array( 'style'=>$datas['style'],'css'=>$datas['css'],'hash'=> $hash ),
                        array( 'ID' => (int)$id_table ) );
         }else {
              $result = $wpdb->update( $wpdb->prefix."wptm_tables" , 
                        array( 'datas' => $datas['datas'],'style'=>$datas['style'],'css'=>$datas['css'],'hash'=> $hash, 'params'=>json_encode($datas['params']) ),
                        array( 'ID' => (int)$id_table ) );
         }
        
        
        if($result===false) {          
            echo $wpdb->last_query ;
            exit();
        }
        if($result==0) {
            $result = $id_table;
        }
          
        return $result;
    }
    
    public function add($id_category) {
        global $wpdb;
        
        $query = 'SELECT MAX(c.position) AS lastPos FROM '.$wpdb->prefix.'wptm_tables as c WHERE c.id_category='.(int)$id_category;      
        $lastPos = (int)$wpdb->get_var($query); 
        $lastPos++;        
        $wpdb->query( $wpdb->prepare(
				"
                                    INSERT INTO ".$wpdb->prefix."wptm_tables (id_category, title, created_time, modified_time, author, position) VALUES 
                                    ( %d,%s,%s,%s,%d,%d)
				",
				$id_category,__('New table','wptm'),date("Y-m-d H:i:s"),date("Y-m-d H:i:s"),get_current_user_id(), $lastPos
			) );
        return $wpdb->insert_id;
    }
     public function copy($id_table) {
        global $wpdb;
         
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_tables as c WHERE c.id='.(int)$id_table;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        $table = $wpdb->get_row($query,OBJECT);        
        $wpdb->query( $wpdb->prepare(
				"
                                    INSERT INTO ".$wpdb->prefix."wptm_tables (id_category, title,datas,style,css,hash,params,created_time, modified_time, author, position) VALUES 
                                    ( %d,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d)
				",
				$table->id_category,$table->title. __(" (copy)","wptm"),$table->datas,$table->style,$table->css,$table->hash,$table->params,date("Y-m-d H:i:s"),date("Y-m-d H:i:s"),get_current_user_id(),$table->position
			) );
        return $wpdb->insert_id;
    }
    public function delete($id) {
        global $wpdb;
        $result = $wpdb->delete( $wpdb->prefix."wptm_tables" , array( 'id' => (int)$id ) );
       
        return $result;
    }
    
    public function setTitle($id, $title) {
        global $wpdb;
        $result = $wpdb->update( $wpdb->prefix."wptm_tables" , array( 'title' => $title ), array( 'id' => (int)$id ) );
       
        return $result;
    }
    
    public function getItem($id) {
        global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_tables as c WHERE c.id='.(int)$id;
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        $item = $wpdb->get_row($query,OBJECT);
       
        $item->params = str_replace(array("\n\r","\r\n", "\n", "\r", "&#10;"), " ",$item->params );
        $params = json_decode(  $item->params);
        if (!isset($params->query)){
            if (!is_object($params)) {
                $params = new stdClass();
            }
            $params->query = '';
        }
        $item->params = $params;
        $params->query =  str_replace(array("\n\r","\r\n", "\n", "\r", "&#10;"), " ",$params->query ); 
        if( $params->query) {
            $tableData = $this->getTableData($params->query . " Limit 50");
            $cols = array_keys($tableData[0] );
            $headerCols  = array();
            $i= 0;
            foreach ($cols as $col) {
                $headerCols[$col] = $params->custom_titles[$i];
                $i++;
            }
            array_unshift($tableData, $headerCols);
            $item->datas = json_encode($tableData);
            $item->query =  $params->query;
        }
        return stripslashes_deep($item);
    }
    
     public function setPosition($tables) {
        global $wpdb;
        $i = 1;
        $ids = explode(",",$tables);
        foreach ($ids as $id) {            
            $result = $wpdb->update( $wpdb->prefix."wptm_tables" , array( 'position' => $i ), array( 'id' => (int)$id ) );            
            $i++;
        }
              
        return $result;
    }
    
     public function setCategory($id,$category) {
        global $wpdb;
        $result = $wpdb->update( $wpdb->prefix."wptm_tables" , array( 'id_category' => $category, 'position' => 0 ), array( 'id' => (int)$id ) );            
                      
        return $result;
    }
    
    // get result data of build query
    public function getTableData($query) {
        global $wpdb;       
        
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return $wpdb->get_results($query, ARRAY_A);
    }
}