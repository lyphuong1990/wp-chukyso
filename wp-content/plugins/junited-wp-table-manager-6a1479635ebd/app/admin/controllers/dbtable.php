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

class wptmControllerDbtable extends Controller {
 
    public function changeTables($param) {
        $tables =  Utilities::getInput("tables","POST","none");
        $model = $this->getModel();
        $columns = $model->listMySQLColumns( $tables );
        
        $this->exit_status(true,array('columns'=> $columns));
    }            
    
    public function generateQueryAndPreviewdata() {
      
        $table_data =  Utilities::getInput("table_data","POST","none");
        $model = $this->getModel();
        $result = $model->generateQueryAndPreviewdata( $table_data );
        
        $this->exit_status(true, $result );
    }
    
    //create new table for selected mysql tables
    public function createTable($param) {
        $table_data =  Utilities::getInput("table_data","POST","none");
        $model = $this->getModel();
        $result = $model->createTable( $table_data );
        
        $this->exit_status(true, $result );
        
    }
    
    //update table with new change
    public function updateTable($param) {
        $table_data =  Utilities::getInput("table_data","POST","none");
        $id_table = (int)$table_data['id_table'];
        $model = $this->getModel();
        $buildQueryandData = $model->generateQueryAndPreviewdata( $table_data );
        $table_data['query'] = $buildQueryandData['query'];
        
        $result = $model->updateTable($id_table, $table_data );
        
        $this->exit_status(true, $result );
        
    }
    
}

?>
