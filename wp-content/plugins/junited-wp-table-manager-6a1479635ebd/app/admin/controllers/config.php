<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Controller;
use Joomunited\WPFramework\v1_0_2\Form;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Factory;

defined( 'ABSPATH' ) || die();

class wptmControllerConfig extends Controller {
 
    public function saveconfig(){
        $model = $this->getModel();

        $form = new Form();
        if(!$form->load('config')){
            $this->redirect('admin.php?page=wptm-config&error=1');
        }
        if(!$form->validate()){
            $this->redirect('admin.php?page=wptm-config&error=2');
        }
        $datas = $form->sanitize();
        if(!$model->save($datas)){
            $this->redirect('admin.php?page=wptm-config&error=3');
        }
        $this->redirect('admin.php?page=wptm-config');
    }
    
    
}

?>
