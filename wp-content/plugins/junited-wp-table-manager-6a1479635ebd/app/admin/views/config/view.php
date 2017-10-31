<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\View;
use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Form;

defined( 'ABSPATH' ) || die();

class wptmViewConfig extends View {
    public function render($tpl = null) {
         $modelConf = $this->getModel('config');
        $this->config = $modelConf->getConfig(); 
        $form = new Form();
        if($form->load('config',$this->config)){
            $this->configform = $form->render();
        }
        
        parent::render($tpl);
    }
}