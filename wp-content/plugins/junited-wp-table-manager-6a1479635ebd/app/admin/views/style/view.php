<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\View;
use Joomunited\WPFramework\v1_0_2\Utilities;

defined( 'ABSPATH' ) || die();

class wptmViewStyle extends View {
    public function render($tpl = null) {
        
        $id= Utilities::getInt('id');
        $model = $this->getModel('style');
        $item = $model->getItem($id);
        header("Content-Type: application/json; charset=utf-8", true);
        echo json_encode($item);
        die();    
    }
}