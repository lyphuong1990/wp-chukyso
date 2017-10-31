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

class wptmControllerChart extends Controller {
    
    public function save() {
        $id_chart = Utilities::getInt('id','POST');
        $datas  = Utilities::getInput('jform','POST','none');
        $model = $this->getModel();   
        if($model->save($id_chart, $datas)){
            $this->exit_status(true);
        }else{
            $this->exit_status( __('error while saving table','wptm') );
        }
    }
    
    public function add(){
        $id_table = Utilities::getInt('id_table');
        $datas = Utilities::getInput('datas','POST','string');
        $model = $this->getModel();        
        $id = $model->add($id_table,$datas);
        if($id){
            $chart = $model->getItem($id);
            $this->exit_status(true,array('id'=>$id, 'datas'=> $chart->datas, 'type'=> "Line", 'title'=>__('New chart','wptm') ));           
        }
        $this->exit_status( __('error while adding chart','wptm') );
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
        $id = $model->setTitle($id,$new_title);
        if($id){
            $this->exit_status(true);
        }
        $this->exit_status(__('An error occurred!','wptm')); 
    }
}