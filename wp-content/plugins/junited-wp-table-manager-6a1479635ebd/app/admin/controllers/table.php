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

class wptmControllerTable extends Controller {
    
    public function save() {
        $id_table = Utilities::getInt('id','POST');
        $datas  = Utilities::getInput('jform','POST','none');
        $model = $this->getModel();   
        if($model->save($id_table, $datas)){
            $this->exit_status(true);
        }else{
            $this->exit_status( __('error while saving table','wptm') );
        }
    }
    
    public function add(){
        $id_category = Utilities::getInt('id_category');
        $model = $this->getModel();        
        $id = $model->add($id_category);
        if($id){
            $this->exit_status(true,array('id'=> $id ,'title'=>__('New table','wptm')));
        }
        $this->exit_status( __('error while adding table','wptm') );
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
    
    public function delete(){
        $id = Utilities::getInt('id');
        $model = $this->getModel();        
        $result = $model->delete($id);
        if($result){
            $this->exit_status(true);
        }
        $this->exit_status(__('An error occurred!','wptm')); 
    }
    
    public function setTitle(){
        $id = Utilities::getInt('id');
        $new_title = Utilities::getInput('title', 'GET', 'string');
        $model = $this->getModel();        
        $result = $model->setTitle($id,$new_title);
        if($result !== false){
            $this->exit_status(true);
        }
        $this->exit_status(__('An error occurred!','wptm')); 
    }
 
      public function order(){
      
        $tables = Utilities::getInput('data', 'GET', 'string'); 
        $model = $this->getModel();        
        $result = $model->setPosition($tables);
        if($result !== false){
            $this->exit_status(true);
        }
        $this->exit_status(__('An error occurred!','wptm')); 
    }
    
    
    public function changeCategory(){
      
        $id_table = Utilities::getInt('id') ; 
        $category = Utilities::getInt('category') ; 
        $model = $this->getModel();        
        $result = $model->setCategory($id_table,$category);
        if($result !== false){
            $this->exit_status(true);
        }else {
            $this->exit_status(__('An error occurred!','wptm')); 
        }
    }
    
}

?>
