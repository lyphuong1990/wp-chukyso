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

class wptmControllerCategories extends Controller {
    
    public function delete(){
        $category = Utilities::getInt('id_category');
        $model = $this->getModel();       
        if($model->delete($category)){           
            $this->exit_status(true);
        }
        $this->exit_status(__('An error occurred!','wptm'));
    }
    
    public function order() {
        if(Utilities::getInput('position')=='after'){
            $position = 'after';
        }else{
            $position = 'first-child';
        }
        $pk = Utilities::getInt('pk');
        $ref = Utilities::getInt('ref');
        if($ref==0){
            $ref=1;
        }
        $model = $this->getModel();
        if($model->move($pk,$ref,$position)){
            $this->exit_status(true,$pk.' '.$position.' '.$ref);
        }
        $this->exit_status(__('An error occurred!','wptm')); 
    }
}