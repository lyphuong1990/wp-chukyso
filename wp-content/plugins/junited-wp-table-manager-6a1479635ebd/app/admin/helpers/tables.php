<?php
/**
    * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

class WptmTablesHelper
{
    /**
     * Convert an object of files to a multidimentional array
     * @param array/object $tables
     */
    public static function categoryObject($tables){
        $ordered = array();
        foreach ($tables as $table){
            $ordered[$table->id_category][] = $table;
        }
        return $ordered;
    }
}
