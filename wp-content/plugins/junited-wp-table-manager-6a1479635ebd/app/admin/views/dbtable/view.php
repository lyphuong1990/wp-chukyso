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
use Joomunited\WPFramework\v1_0_2\Utilities;

defined( 'ABSPATH' ) || die();

class wptmViewDbtable extends View {
    public function render($tpl = null) {
        $model = $this->getModel('dbtable');
        $id_table= Utilities::getInt('id_table');
        $this->selected_tables = array();
        $this->availableColumns = array();
        $this->selected_columns = array();
        $this->join_rules = array();
        $this->params = new stdClass();
        $this->id_table = $id_table;
        $this->default_ordering_dir  = 'asc';
        if($id_table) {
            $modelTable = $this->getModel('table');
            $item = $modelTable->getItem($id_table);
            $params = json_decode(  $item->params); 
            $this->params =  $params;
            $this->selected_tables = $params->tables;
            $columns = $model->listMySQLColumns($this->selected_tables );
          
            $this->availableColumns = $columns['all_columns'] ;
            $this->selected_columns =  $params->mysql_columns;
            $this->sorted_columns = $columns['sorted_columns'] ;
            $this->join_rules = $params->join_rules;
            $this->default_ordering_column =  $params->default_ordering;
            $this->default_ordering_dir =  $params->default_ordering_dir;
            $this->custom_titles =  $params->custom_titles; 
        }
        $this->mysql_tables = $model->listMySQLTables();
       
        
        parent::render($tpl);
    }
}