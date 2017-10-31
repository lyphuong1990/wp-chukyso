<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Controller;
use Joomunited\WPFramework\v1_0_2\Utilities;
defined( 'ABSPATH' ) || die();

class wptmControllerCategory extends Controller {
    public function addCategory(){
      
        $model = $this->getModel();        
        $id = $model->addCategory(__('New category','wptm'));
        if($id){
            $this->exit_status(true,array('id_category'=> $id ,'title'=>__('New category','wptm')));
        }
        $this->exit_status( __('error while adding category','wptm') );
    }
    
    public function copy(){
        $id = Utilities::getInt('id');
        $model = $this->getModel();        
        $newItem = $model->copy($id);
        if($newItem){
            $table = $model->getItem($newItem);
            $this->exit_status(true,array('id'=>$table->id,'title'=>$table->title) );
        }
        $this->exit_status( __('error while adding table','wptm') );
    }
  
    
    public function setTitle(){
        $id = Utilities::getInt('id_category');
        $new_title = Utilities::getInput('title', 'GET', 'string');
        $model = $this->getModel();        
        $id = $model->setTitle($id,$new_title);
        if($id){
            $this->exit_status(true);
        }
        $this->exit_status(__('An error occurred!','wptm')); 
    }
    
}

?>
