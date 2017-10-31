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

class wptmViewTable extends View {
    public function render($tpl = null) {
        
        $id= Utilities::getInt('id');
        $model = $this->getModel('table');
        $item = $model->getItem($id);
        echo json_encode($item);
        die();    
    }
}