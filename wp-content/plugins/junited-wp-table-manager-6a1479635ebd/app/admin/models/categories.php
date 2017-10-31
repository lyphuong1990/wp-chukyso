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

class wptmModelCategories extends Model {
  

    public function getCategories() {
         global $wpdb;
        $query = 'SELECT c.* FROM '.$wpdb->prefix.'wptm_categories as c WHERE c.level >0 ORDER BY c.lft ASC';
        $result = $wpdb->query($query);
        if($result===false){
            return false;
        }
        return stripslashes_deep($wpdb->get_results($query,OBJECT));
    }
    
      public function delete($nodeId){
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        if(!$this->_delete($nodeId)){
            $wpdb->query('ROLLBACK');
            return false;
        }
        $wpdb->query('COMMIT');
        return true;
    }

    private function _delete($nodeId){
        global $wpdb;
        $query = 'SELECT rgt,lft,level FROM '.$wpdb->prefix.'wptm_categories WHERE id='.(int)$nodeId;
        $nodeInfos = $wpdb->get_row($query);
        if($nodeInfos===false){
            return false;
        }
        
        $width = $nodeInfos->rgt - $nodeInfos->lft + 1;
        
        //Delete node
        $query = 'DELETE FROM '.$wpdb->prefix.'wptm_categories WHERE 
                            lft >= '.$nodeInfos->lft.' AND
                            rgt <= '.$nodeInfos->rgt;
        if($wpdb->query($query)===false){
            return false;
        }
        
        //Update right brothers
        $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET 
                            lft = lft - '.$width.',
                            rgt = rgt - '.$width.'
                  WHERE 
                            lft > '.$nodeInfos->rgt.' AND
                            rgt > '.$nodeInfos->rgt;
        if($wpdb->query($query)===false){
            return false;
        }
        
        //Update parents
        $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET 

                            rgt = rgt - '.$width.'
                  WHERE 
                            lft < '.$nodeInfos->lft.' AND
                            rgt > '.$nodeInfos->rgt;
        if($wpdb->query($query)===false){
            return false;
        }
        
        return true;
    }
    
    /**
     * 
     * @global type $wpdb
     * @param int $nodeTo id to the reference node
     * @param array $nodes array of nodes to insert ordered by left asc
     * @param type $where
     * @return boolean
     */
    private function _insert($nodeTo,$nodes,$where='first-child',$refInfos=null){
        global $wpdb;
        $query = 'SELECT rgt,lft,level,parent_id FROM '.$wpdb->prefix.'wptm_categories WHERE id='.(int)$nodeTo;
        $nodeToInfos = $wpdb->get_row($query);
        if($nodeToInfos===false){
            return false;
        }
        
        //Get the node width
        $maxRgt = 0;
        $minLft = $nodes[0]->lft;
        foreach ($nodes as $node) {
            $minLft = min($node->lft, $minLft);
            $maxRgt = max($node->rgt, $maxRgt);
        }
        $width = $maxRgt - $minLft +1;
        
        //Update parents
        if($where=='first-child'){
            //Update right brothers
            $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET 
                                lft = lft + '.$width.',
                                rgt = rgt + '.$width.'
                      WHERE 
                                lft > '.$nodeToInfos->lft.' AND
                                rgt > '.$nodeToInfos->lft;
            if($wpdb->query($query)===false){
                return false;
            }
            
            //insert at first position
            $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET 
                            
                            rgt = rgt + '.$width.'
                  WHERE 
                            lft <= '.$nodeToInfos->lft.' AND
                            rgt >= '.$nodeToInfos->rgt;
            if($wpdb->query($query)===false){
                return false;
            }
            
            //new position left
            $leftTo = $nodeToInfos->lft+1;
            $diffLevel = $nodeToInfos->level + 1 - $nodes[0]->level;
        }else{
            //Update right brothers
            $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET 
                                lft = lft + '.$width.',
                                rgt = rgt + '.$width.'
                      WHERE 
                                lft > '.$nodeToInfos->rgt.' AND
                                rgt > '.$nodeToInfos->rgt;
            if($wpdb->query($query)===false){
                return false;
            }
            
            //insert after element
            $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET 
                            
                            rgt = rgt + '.($width).'
                  WHERE 
                            lft <= '.$nodeToInfos->lft.' AND
                            rgt > '.$nodeToInfos->rgt;
            if($wpdb->query($query)===false){
                return false;
            }
            //new position left
            $leftTo = $nodeToInfos->rgt+1;
            $diffLevel = $nodeToInfos->level - $nodes[0]->level;
        }
        
        $diff = $minLft;
        //prepare and insert old nodes
        foreach ($nodes as $NodeElement){
            $NodeElement->lft += $leftTo-$diff;
            $NodeElement->rgt += $leftTo-$diff;
            $NodeElement->level += $diffLevel;
            $keys = array();
            $values = array();
            foreach ($NodeElement as $key => $value){
                $keys[] = '`'.$key.'`';
                $values[] = '"'.addslashes($value).'"';
            }
            $query = 'INSERT INTO '.$wpdb->prefix.'wptm_categories ('.implode(',', $keys).') VALUES ('.implode(',',$values).')';
            if($wpdb->query($query)===false){
                return false;
            }
        }
        
        //update parent value
        if($where=='first-child'){
            $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET 
                            parent_id = '.$refInfos->id.'
                  WHERE 
                            id = '.(int)$nodes[0]->id;
            if($wpdb->query($query)===false){
                return false;
            }
        }else{
            $query = 'UPDATE '.$wpdb->prefix.'wptm_categories SET 
                            parent_id = '.$refInfos->parent_id.'
                  WHERE 
                            id = '.(int)$nodes[0]->id;
            if($wpdb->query($query)===false){
                return false;
            }
        }
        
        return true;
    }
    
    public function move($node,$ref,$position='first-child'){
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        if(!$this->_move($node,$ref,$position)){
            $wpdb->query('ROLLBACK');
            return false;
        }
        $wpdb->query('COMMIT');
        return true;
    }
    
    /**
     * Move a node $node to the $position of $ref element
     * @param int $node
     * @param int $ref
     * @param string $position
     */
    private function _move($node,$ref,$position='first-child'){
        global $wpdb;
        
        $query = 'SELECT rgt,lft,level FROM '.$wpdb->prefix.'wptm_categories WHERE id='.(int)$node;
        
        $nodeInfos = $wpdb->get_row($query);
        if($nodeInfos===false){
            return false;
        }
        
        $query = 'SELECT id,rgt,lft,level,parent_id FROM '.$wpdb->prefix.'wptm_categories WHERE ';
        if($ref=='0'){
            //case ROOT
            $query .= 'level=0 LIMIT 0,1';
        }else{
            $query .= 'id='.(int)$ref;
        }
        $refInfos = $wpdb->get_row($query);
        if($refInfos===false){
            return false;
        }
        
        $query = 'SELECT * FROM '.$wpdb->prefix.'wptm_categories WHERE lft >= '.(int)$nodeInfos->lft.' AND rgt <= '.(int)$nodeInfos->rgt.' ORDER BY lft ASC';
        $nodes = $wpdb->get_results($query);
        if($nodes===false){
            return false;
        }
        
        if(!$this->_delete($node)){
            return false;
        }
        if(!$this->_insert($ref,$nodes,$position,$refInfos)){
            return false;
        }
        
        return true;
    }
}