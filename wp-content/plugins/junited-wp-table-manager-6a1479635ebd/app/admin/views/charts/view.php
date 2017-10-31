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

class wptmViewCharts extends View {
    public function render($tpl = null) {
        
        $id_table= Utilities::getInt('id_table');
        $model = $this->getModel('charts');
        $items = $model->getCharts($id_table);
        echo json_encode($items);
        die();    
    }
}