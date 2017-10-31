<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

// no direct access
defined('ABSPATH') or die();

class wptmBase {

    
    public static function loadValue($var,$value,$default=''){
        if(is_object($var) && isset($var->$value)){
            return $var->$value;
        }elseif(is_array($var) && isset($var[$value])){
            return $var[$value];
        }
        return $default;
    }
    
    /**
     * method to retrieve the path to the component image directory
     * @param type $id_category 
     * @return string directory path
     */
    public static function getFilesPath($id_category=null){
        $upload_dir = wp_upload_dir();
        if($id_category===null){
            return $upload_dir['basedir'].DIRECTORY_SEPARATOR.'com_wptm'.DIRECTORY_SEPARATOR;
        }
        return $upload_dir['basedir'].DIRECTORY_SEPARATOR.'com_wptm'.DIRECTORY_SEPARATOR.$id_category.DIRECTORY_SEPARATOR;
    }
    
}

?>
