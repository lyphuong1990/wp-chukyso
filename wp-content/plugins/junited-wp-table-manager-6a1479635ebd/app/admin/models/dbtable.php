<?php
/* Based on some work of wp Data Tables plugin */
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */
use Joomunited\WPFramework\v1_0_2\Model;
use Joomunited\WPFramework\v1_0_2\Factory;

defined('ABSPATH') || die();

class wptmModelDbtable extends Model {

    /*     * * For the WP DB type query ** */ 
    private $_select_arr = array();
    private $_where_arr = array();
    private $_group_arr = array();
    private $_from_arr = array();
    private $_inner_join_arr = array();
    private $_left_join_arr = array();
    private $_has_groups = false;

    /** Query text * */
    private $_query = '';

    public function listMySQLTables() {

        $tables = array();
        global $wpdb;
        $result = $wpdb->get_results('SHOW TABLES', ARRAY_N);

        // Formatting the result to plain array
        foreach ($result as $row) {
            $tables[] = $row[0];
        }

        return $tables;
    }

    /**
     * Return a list of columns for the selected tables
     */
    public static function listMySQLColumns($tables) {
        $columns = array('all_columns' => array(), 'sorted_columns' => array());
        if (!empty($tables)) {

            global $wpdb;
            foreach ($tables as $table) {
                $columns['sorted_columns'][$table] = array();
                $table_columns = $wpdb->get_results("SHOW COLUMNS FROM {$table};", ARRAY_A);
                foreach ($table_columns as $table_column) {
                    $columns['sorted_columns'][$table][] = "{$table}.{$table_column['Field']}";
                    $columns['all_columns'][] = "{$table}.{$table_column['Field']}";
                }
            }
        }

        return $columns;
    }

    /**
     * Return a build query and preview table
     */
    public function generateQueryAndPreviewdata($table_data) {
        global $wpdb;
        $this->_table_data = apply_filters('wdt_before_generate_mysql_based_query', $table_data);

        if (!isset($this->_table_data['where_conditions'])) {
            $this->_table_data['where_conditions'] = array();
        }

        if (isset($this->_table_data['grouping_rules'])) {
            $this->_has_groups = true;
        }

        if (!isset($table_data['mysql_columns'])) {
            $table_data['mysql_columns'] = array();
        }

        // Initializing structure for the SELECT part of query
        $this->_prepareMySQLSelectBlock($table_data['mysql_columns']);

        // Initializing structure for the WHERE part of query
        $this->_prepareMySQLWhereBlock();

        // Prepare the GROUP BY block
        $this->_prepareMySQLGroupByBlock();

        // Prepare the join rules
        $this->_prepareMySQLJoinedQueryStructure();

        // Prepare the query itself
        $this->_query = $this->_buildMySQLQuery();
      
        if (isset($this->_table_data['default_ordering']) && $this->_table_data['default_ordering']) {
            $this->_query .= " Order by ". $this->_table_data['default_ordering']." " . $this->_table_data['default_ordering_dir'];
        }
        
        $result = array(
				'query' => $this->_query,
				'preview' => $this->getQueryPreview()
			);
        
        return $result;
        
    }

    /**
     * Generate the sample table with 5 rows from MySQL query
     */    
    public function getQueryPreview() {

        global $wpdb;
        $result = $wpdb->get_results($this->_query . ' LIMIT 10', ARRAY_A);

        if (!empty($result)) {
           
            $headers = $this->_table_data['custom_titles'];            
            ob_start();
            include( WPTM_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'dbtable' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'table_preview.inc.php' );
            $ret_val = ob_get_contents();
            ob_end_clean();
        } else {
            $ret_val = __('No results found', 'wptm');
        }
        return $ret_val;
    }

    /**
     * Helper function to generate the fields structure from MySQL tables
     */
    private function _prepareMySQLSelectBlock() {

        foreach ($this->_table_data['mysql_columns'] as $mysql_column) {

            $mysql_column_arr = explode('.', $mysql_column);
            if (!isset($this->_select_arr[$mysql_column_arr[0]])) {
                $this->_select_arr[$mysql_column_arr[0]] = array();
            }
            $this->_select_arr[$mysql_column_arr[0]][] = $mysql_column;

            if (!in_array($mysql_column_arr[0], $this->_from_arr)) {
                $this->_from_arr[] = $mysql_column_arr[0];
            }
        }
    }

     /**
     * Prepare a Where block for MySQL based
     */
    private function _prepareMySQLWhereBlock() {

        if (empty($this->_table_data['where_conditions'])) {
            return;
        }

        foreach ($this->_table_data['where_conditions'] as $where_condition) {

            $where_column_arr = explode('.', $where_condition['column']);

            if (!in_array($where_column_arr[0], $this->_from_arr)) {
                $this->_from_arr[] = $where_column_arr[0];
            }

            $this->_where_arr[$where_column_arr[0]][] = self::buildWhereCondition(
                            $where_condition['column'], $where_condition['operator'], $where_condition['value']
            );
        }
    }

    /**
     * Prepare a GROUP BY block for MySQL based
     */
    private function _prepareMySQLGroupByBlock() {
        if (!$this->_has_groups) {
            return;
        }

        foreach ($this->_table_data['grouping_rules'] as $grouping_rule) {
            if (empty($grouping_rule)) {
                continue;
            }
            $this->_group_arr[] = $grouping_rule;
        }
    }

    /**
     * Prepares the structure of the JOIN rules for MySQL based tables
     */
    private function _prepareMySQLJoinedQueryStructure() {
        if (!isset($this->_table_data['join_rules'])) {
            return;
        }

        foreach ($this->_table_data['join_rules'] as $join_rule) {
            if (empty($join_rule['initiator_column']) || empty($join_rule['connected_column'])) {
                continue;
            }

            $connected_column_arr = explode('.', $join_rule['connected_column']);

            if (in_array($connected_column_arr[0], $this->_from_arr) && count($this->_from_arr) > 1) {
                if ($join_rule['type'] == 'left') {
                    $this->_left_join_arr[$connected_column_arr[0]] = $connected_column_arr[0];
                } else {
                    $this->_inner_join_arr[$connected_column_arr[0]] = $connected_column_arr[0];
                }
                unset($this->_from_arr[array_search($connected_column_arr[0], $this->_from_arr)]);
            } else {
                if ($join_rule['type'] == 'left') {
                    $this->_left_join_arr[$connected_column_arr[0]] = $connected_column_arr[0];
                } else {
                    $this->_inner_join_arr[$connected_column_arr[0]] = $connected_column_arr[0];
                }
            }

            $this->_where_arr[$connected_column_arr[0]][] = self::buildWhereCondition(
                            $join_rule['initiator_table'] . '.' . $join_rule['initiator_column'], 'eq', $join_rule['connected_column'], false
            );
        }
    }

    /**
     * Prepares the query text for MySQL based table
     */
    private function _buildMySQLQuery() {

        // Build the final output
        $query = "SELECT ";
        $i = 0;
        foreach ($this->_select_arr as $table_alias => $select_block) {
            $query .= implode(",\n       ", $select_block);
            $i++;
            if ($i < count($this->_select_arr)) {
                $query .= ",\n       ";
            }
        }
        $query .= "\nFROM ";
        $query .= implode(', ', $this->_from_arr) . "\n";
        if (!empty($this->_inner_join_arr)) {
            $i = 0;
            foreach ($this->_inner_join_arr as $table_alias => $inner_join_block) {
                $query .= "  INNER JOIN " . $inner_join_block . "\n";
                if (!empty($this->_where_arr[$table_alias])) {
                    $query .= "     ON " . implode("\n     AND ", $this->_where_arr[$table_alias]) . "\n";
                    unset($this->_where_arr[$table_alias]);
                }
            }
        }
        if (!empty($this->_left_join_arr)) {

            foreach ($this->_left_join_arr as $table_alias => $left_join_block) {
                $query .= "  LEFT JOIN " . $left_join_block . "\n";
                if (!empty($this->_where_arr[$table_alias])) {
                    $query .= "     ON " . implode("\n     AND ", $this->_where_arr[$table_alias]) . "\n";
                    unset($this->_where_arr[$table_alias]);
                }
            }
        }
        if (!empty($this->_where_arr)) {
            $query .= "WHERE 1=1 \n   AND ";
            $i = 0;
            foreach ($this->_where_arr as $table_alias => $where_block) {
                $query .= implode("\n   AND ", $where_block);
                $i++;
                if ($i < count($this->_where_arr)) {
                    $query .= "\n   AND ";
                }
            }
        }
        if (!empty($this->_group_arr)) {
            $query .= "\nGROUP BY " . implode(', ', $this->_group_arr);
        }
        return $query;
    }

    
     /**
     * Prepares the structure of the WHERE rules for MySQL based tables
     */
    public static function buildWhereCondition($leftOperand, $operator, $rightOperand, $isValue = true) {
        $rightOperand = stripslashes_deep($rightOperand);
        $wrap = $isValue ? "'" : "";
        switch ($operator) {
            case 'eq':
                return "{$leftOperand} = {$wrap}{$rightOperand}{$wrap}";
            case 'neq':
                return "{$leftOperand} != {$wrap}{$rightOperand}{$wrap}";
            case 'gt':
                return "{$leftOperand} > {$wrap}{$rightOperand}{$wrap}";
            case 'gtoreq':
                return "{$leftOperand} >= {$wrap}{$rightOperand}{$wrap}";
            case 'lt':
                return "{$leftOperand} < {$wrap}{$rightOperand}{$wrap}";
            case 'ltoreq':
                return "{$leftOperand} <= {$wrap}{$rightOperand}{$wrap}";
            case 'in':
                return "{$leftOperand} IN ({$rightOperand})";
            case 'like':
                return "{$leftOperand} LIKE {$wrap}{$rightOperand}{$wrap}";
            case 'plikep':
                return "{$leftOperand} LIKE {$wrap}%{$rightOperand}%{$wrap}";
        }
    }

    //create new table for selected mysql tables
    public function createTable($table_data) {
        global $wpdb;
        $id_category = $this->getCategoryId();
        $query = 'SELECT MAX(c.position) AS lastPos FROM '.$wpdb->prefix.'wptm_tables as c WHERE c.id_category='.(int)$id_category;      
        $lastPos = (int)$wpdb->get_var($query); 
        $lastPos++;      
        $style =  json_decode('{   "table":{      "alternate_row_even_color":"#fafafa",      "use_sortable":"1"   },   "rows":{      "0":[         0,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_family":"Arial",            "cell_font_size":"13",            "cell_font_color":"#333333",            "cell_border_bottom":"2px solid #707070",            "cell_background_color":"#ffffff",            "cell_font_bold":true,            "cell_vertical_align":"middle"         }      ],      "1":[         1,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_color":"#333333",            "cell_border_bottom":"1px solid #d6d6d6",            "cell_vertical_align":"middle"         }      ],      "2":[         2,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_color":"#333333",            "cell_border_bottom":"1px solid #d6d6d6",            "cell_vertical_align":"middle"         }      ],      "3":[         3,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_color":"#333333",            "cell_border_bottom":"1px solid #d6d6d6",            "cell_vertical_align":"middle"         }      ],      "4":[         4,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_color":"#333333",            "cell_border_bottom":"1px solid #d6d6d6",            "cell_vertical_align":"middle"         }      ],      "5":[         5,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_color":"#333333",            "cell_border_bottom":"1px solid #d6d6d6",            "cell_vertical_align":"middle"         }      ],      "6":[         6,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_color":"#333333",            "cell_border_bottom":"1px solid #d6d6d6",            "cell_vertical_align":"middle"         }      ],      "7":[         7,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_color":"#333333",            "cell_border_bottom":"1px solid #d6d6d6",            "cell_vertical_align":"middle"         }      ],      "8":[         8,         {            "height":30,            "cell_padding_top":"3",            "cell_padding_right":"3",            "cell_padding_bottom":"3",            "cell_padding_left":"3",            "cell_font_color":"#333333",            "cell_border_bottom":"1px solid #d6d6d6",            "cell_vertical_align":"middle"         }      ]   },   "cols":{      "0":[         0,         {            "width":50,            "cell_text_align":"center",            "cell_font_bold":true         }      ],      "1":[         1,         {            "width":122,"cell_text_align":"center"         }      ],      "2":[         2,         {            "width":137,"cell_text_align":"center"         }      ],      "3":[         3,         {            "width":133,"cell_text_align":"center"         }      ],      "4":[         4,         {            "width":150,"cell_text_align":"center"         }      ],      "5":[         5,         {            "width":50,"cell_text_align":"center"         }      ]   },   "cells":{         }}');
        $style->table->enable_pagination = $table_data['enable_pagination'];
        $style->table->limit_rows = $table_data['limit_rows'];
                
        $params = $table_data;
        $params['table_type'] = 'mysql';
        $wpdb->query( $wpdb->prepare(
				"
                                    INSERT INTO ".$wpdb->prefix."wptm_tables (id_category, title, datas, style, params, created_time, modified_time, author, position) VALUES 
                                    ( %d,%s,%s,%s,%s,%s,%s,%d,%d)
				",
				$id_category,__('New table','wptm'),  $table_data['query'], json_encode($style), json_encode($params), date("Y-m-d H:i:s"),date("Y-m-d H:i:s"),get_current_user_id(), $lastPos
			) );
        
        return $wpdb->insert_id;
    }
    
     //update table with new change
    public function updateTable($id_table, $table_data) {
        global $wpdb;
    
        $params = $table_data;
        $params['table_type'] = 'mysql';
        $ret =  $wpdb->update($wpdb->prefix."wptm_tables", 
                                array('datas' =>  $table_data['query'],'params'=>json_encode($params), 'modified_time'=>date("Y-m-d H:i:s")),
                                array('id'=> $id_table) );        
        return $ret;
    }
    
    //getID of special category for database tables.
    public function getCategoryId() {
        
        $config_dbtable_cat = (int)get_option('_wptm_dbtable_cat'); 
        $modelCategory = Model::getInstance('category');
        if(!$config_dbtable_cat || !$modelCategory->isCategoryExist($config_dbtable_cat)) {           
           
            $cat_id = $modelCategory->addCategory('Table from Database');
            update_option('_wptm_dbtable_cat', $cat_id);
            return $cat_id;
        }
        
        return (int)$config_dbtable_cat;
    }
 

}
