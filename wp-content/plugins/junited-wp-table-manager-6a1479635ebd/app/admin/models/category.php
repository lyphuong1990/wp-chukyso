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

class wptmModelCategory extends Model {
  
    public function addCategory($title) {
         global $wpdb;
        $wpdb->query('START TRANSACTION');
        $result = $this->_addCategory($title);
        if(!$result){
            $wpdb->query('ROLLBACK');
            return false;
        }
        $wpdb->query('COMMIT');
        return $result;
    }
    
    private function _addCategory($title){
        global $wpdb;
        $title = trim(sanitize_text_field($title));
        if($title==''){
            return false;
        }
        $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET rgt=rgt+2 WHERE level=0';
        if($wpdb->query($query)===false){
            return false;
        }
        
        $query = 'INSERT INTO '.$wpdb->prefix.'wptm_categories (title,level,lft,rgt,parent_id) VALUES ("'.$title.'",1,((SELECT c.rgt FROM '.$wpdb->prefix.'wptm_categories as c WHERE level=0 ORDER BY c.lft ASC LIMIT 0,1)-2),((SELECT c.rgt FROM '.$wpdb->prefix.'wptm_categories as c WHERE level=0 ORDER BY c.lft ASC LIMIT 0,1)-1),(SELECT id FROM '.$wpdb->prefix.'wptm_categories as c WHERE level=0 ORDER BY c.lft ASC LIMIT 0,1))';
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return $wpdb->insert_id;
    }
    
    function isCategoryExist($id_category) {
        global $wpdb;
        $query = 'SELECT c.id FROM '.$wpdb->prefix.'wptm_categories as c WHERE c.id='.(int)$id_category;
        $result = $wpdb->query($query);
        if(!$result){
            return false;
        }
        return true;
    }
    
    public function setTitle($id_category,$title){
        global $wpdb;
        $query = $wpdb->prepare('UPDATE '.$wpdb->prefix.'wptm_categories SET title = %s WHERE id=%d',
                $title,$id_category);
        
        if($wpdb->query($query)===false){
            return false;
        }
        return true;
    }
}