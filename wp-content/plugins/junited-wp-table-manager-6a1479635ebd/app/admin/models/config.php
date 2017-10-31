<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Model;
use Joomunited\WPFramework\v1_0_2\Factory;

defined( 'ABSPATH' ) || die();

class wptmModelConfig extends Model {  
    
    public function getConfig(){
        
        $defaultConfig = array('enable_import_excel'=>1,'export_excel_format'=>'xlsx','enable_tooltip'=> 0, 'enable_autosave'=> 1, 'sync_periodicity' => 0 ,'enable_frontend'=>0);  
        $config = (array)get_option('_wptm_global_config', $defaultConfig);
        $config = array_merge($defaultConfig, $config);
        
        return $config;      
    }

    public function save($datas){
        $config = get_option('_wptm_global_config');
        foreach ($datas as $key => $value) {
            $config[$key]= $value;
        }
        
        $result = update_option('_wptm_global_config', $config);        
        return $result;
    }
    


}