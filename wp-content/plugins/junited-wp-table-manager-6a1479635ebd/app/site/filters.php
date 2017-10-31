<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Filter;
use Joomunited\WPFramework\v1_0_2\Model;
use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Application;
defined( 'ABSPATH' ) || die();

class wptmFilter extends Filter {

    public function load(){
        add_filter('the_content', array($this,'wptm_replace'));
        add_filter('themify_builder_module_content', array($this, 'themify_module_content'));
        /** Register our shortcode **/
        add_shortcode('wptm', array($this, 'applyShortcode'));
    }
    
    /**
	 * Returns content of our shortcode
	 * 
     */
    public function applyShortcode( $args = array() ) {

            $html = '';		
             $app = Application::getInstance('wptm');      
            if( isset( $args['id']) &&  ! empty($args['id']) ) {
                $id_table = $args['id'];    
                $model = Model::getInstance('table');
                $table = $model->getItem($id_table); 
                if(!$table)   return $html;
                $html = $this->replaceTable($id_table,$table);
            }else if( isset($args['id-chart']) &&  ! empty( $args['id-chart'] ) ) {
                  $id_chart = $args['id-chart'];                                    
                  $model = Model::getInstance('table');
                  $table = $model->getTableFromChartId($id_chart); 
                  $html = $this->replaceChart($id_chart,$table);
            }
            
            return $html;
    }
    
    public function themify_module_content( $content ) {
        $content = $this->wptm_replace($content);
        return $content;
    }
    
    public function wptm_replace($content){
        $content = preg_replace_callback('@<img[^>]*?data\-wptmtable="([0-9]+)".*?/>@', array($this,'replace'), $content);

        return $content;
    }

    private function replace($match){
        $app = Application::getInstance('wptm');
        $content= "";
        $id_table= $match[1];
        $model = Model::getInstance('table');
        $table = $model->getItem($id_table); 
        if(!$table)   return $content;
        
        $exp = '@<img.*data\-wptm\-chart="([0-9]+)".*?>@';
        preg_match($exp, $match[0], $matches);
        if(count($matches) > 0 ) { //is a chart
            $id_chart = $matches[1];
            $content = $this->replaceChart($id_chart,$table);                      
        }else {  //is a table
           $content = $this->replaceTable($id_table,$table);
        }                            
        
        return $content;
    }

    private function replaceTable($id_table,$table) {
        $datas = json_decode($table->datas);
        $style = json_decode($table->style);                  
        $params = json_decode($table->params);
        $table_type = 'html'; 
        if(is_object($params) && isset($params->table_type) && $params->table_type=='mysql') {
            $table_type = 'mysql';
            $datas = $table->datas;
        }
        
        if(!isset($style->table->freeze_col)) $style->table->freeze_col = 0;
        if(!isset($style->table->freeze_row)) $style->table->freeze_row = 0;
        if(!isset($style->table->enable_filters)) $style->table->enable_filters = 0;
        if(isset($style->table->use_sortable) && $style->table->use_sortable==1){
                $sortable = true;
        }else{
                $sortable = false;
        }
        if(isset($style->table->responsive_type) && $style->table->responsive_type=="hideCols"){
            $responsive_type = "hideCols";
        }else {
            $responsive_type = "scroll";
        }
        if(!isset($style->table->enable_filters)) $style->table->enable_filters = false;
        if($responsive_type == "hideCols"){
                $hideCols = true;
                $res_prioritys = array();
                foreach ($style->cols as $col) {
                    if(isset($col[1]->res_priority)){
                        $res_prioritys[(string)$col[0]] = ($col[1]->res_priority == "persistent") ? "persistent" : (int)$col[1]->res_priority;                          
                    }
                }               
                $priority = json_encode($res_prioritys,JSON_FORCE_OBJECT);
                
            }else{
                $hideCols = false;    
                $priority="{}";
            }

            $content = '';
            if($datas!==null && !empty($datas)){
                
                wp_enqueue_script('jquery');
                if($sortable && !$style->table->enable_filters ) {
                    wp_enqueue_script('wptm_sorttable', plugins_url( 'assets/js/sorttable.js' , __FILE__ ),array(),WPTM_VERSION);               
                }
                wp_enqueue_script('wptm_restable', plugins_url( 'assets/restable/js/restable.js' , __FILE__ ),array(),WPTM_VERSION);          
                                         
                wp_enqueue_style('wptm-theme-table', plugins_url( 'assets/restable/css/restable.css' , __FILE__ ),array(),WPTM_VERSION);                
               
                if($responsive_type == "scroll"){
                    if( (isset($style->table->freeze_row) && $style->table->freeze_row) || (isset($style->table->freeze_col) && $style->table->freeze_col) ){                      
                        wp_enqueue_script('wptm_fixedtable', plugins_url( 'assets/js/fixed_table_rc.js' , __FILE__ ),array(),WPTM_VERSION);          
                        wp_enqueue_style('wptm_fixedtable', plugins_url( 'assets/css/fixed_table_rc.css' , __FILE__ ),array(),WPTM_VERSION);                
                    }
                }
                if( $style->table->enable_filters || (isset($style->table->enable_pagination) && $style->table->enable_pagination)) {
                    wp_enqueue_script('wptm_tablesorter', plugins_url( 'assets/tablesorter/jquery.tablesorter.js' , __FILE__ ),array(),WPTM_VERSION);                              
                    wp_enqueue_script('wptm_tablesorter_widgets', plugins_url( 'assets/tablesorter/jquery.tablesorter.widgets.js' , __FILE__ ),array(),WPTM_VERSION);    
                    wp_enqueue_script('wptm_tablesorter_pager', plugins_url( 'assets/tablesorter/jquery.tablesorter.pager.js' , __FILE__ ),array(),WPTM_VERSION);      
                    
                    wp_enqueue_style('wptm_tablesorter_jui', plugins_url( 'assets/tablesorter/theme.jui.css' , __FILE__ ),array(),WPTM_VERSION);                
                    wp_enqueue_style('wptm_tablesorter_bootstrap', plugins_url( 'assets/tablesorter/theme.bootstrap.css' , __FILE__ ),array(),WPTM_VERSION);                
                    wp_enqueue_style('wptm_tablesorter_pager', plugins_url( 'assets/tablesorter/jquery.tablesorter.pager.css' , __FILE__ ),array(),WPTM_VERSION);                
                }
               
                wp_enqueue_script('wptm_table'.$id_table, plugins_url( 'assets/js/wptm_front.js' , __FILE__ ),array(),WPTM_VERSION);                               
                if($table_type=='html') {
                    $table_params = $table->params;
                    require_once dirname(WPTM_PLUGIN_FILE).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR. 'wptmHelper.php';
                    $wptmHelper = new WptmHelper();
                    $wptmHelper->htmlRender($table) ;  
                    $table_hash = md5($table->datas. $table->style. $table_params);        
                    $folder = plugin_dir_path(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR;
                    $file = $folder . $table->id . '_' . $table_hash . '.html';
                    $content .= file_get_contents($file);
                    $content = html_entity_decode($content);
                  
                }else {
                   
                    $limit = isset($style->table->limit_rows)? (int)$style->table->limit_rows: 0;
                    $enable_pagination = isset($style->table->enable_pagination)? (int)$style->table->enable_pagination: 0;
                  
                    global $wpdb;
                    if(!$enable_pagination && $limit) {
                        $result = $wpdb->get_results($table->datas . ' LIMIT '.$limit, ARRAY_A);                                       
                    }else {
                        $result = $wpdb->get_results($table->datas , ARRAY_A);                                       
                    }
                    if (!empty($result)) {
                        $content = '<div class="wptmresponsive  wptmtable wptm_dbtable" id="wptmtable' . (int) $table->id . '">';
                        $tblCls = ($sortable ? 'sortable' : '');      

                        if(!$hideCols ) {
                            if( ($style->table->freeze_row) || ($style->table->freeze_col) ){                      
                                $tblCls .= ' fxdHdrCol';
                            }
                        }            
                        if(!isset($style->table->table_height) ) {
                            $style->table->table_height = 0;
                        }
                        if( $style->table->enable_filters) {
                             $tblCls .= ' filterable';
                        }else {
                            if($enable_pagination && $limit) {
                                 $tblCls .= ' enablePager';
                            }
                            
                        }
                        if(!$enable_pagination || !$limit) {
                            $tblCls .= ' disablePager';
                        }
                        
                        $content .= '<table id="wptmTbl' . (int) $table->id . '" data-id="'.$table->id. '" data-hidecols="'.$hideCols.'" data-priority="'.$priority. '"'
                                  . '  data-freeze-cols="'.(int)$style->table->freeze_col . '"  data-freeze-rows="'.(int)$style->table->freeze_row 
                                  .  '" data-table-height="'.(int)$style->table->table_height . '" class="'. $tblCls  . '" data-pagesize="'.$limit.'">';
                        $table_params  = json_decode( $table->params) ;                        
                        $headers = $table_params->custom_titles;
                        ob_start();
                        include( WPTM_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'table_template.inc.php' );
                        $ret_val = ob_get_contents();
                        ob_end_clean();
                        
                        $content .= $ret_val;
                        
                       if($enable_pagination && $limit) {
                           $colNb = count($result[0]);
                           //pager
                            $content .= '<tfoot><tr>';
                            $content .= '<th colspan="'.$colNb.'" class="ts-pager form-horizontal" >';
                            $content .= '<button type="button" class="btn first"><i class="icon-step-backward glyphicon glyphicon-step-backward"></i>
                               </button>
                               <button type="button" class="btn prev"><i class="icon-arrow-left glyphicon glyphicon-backward"></i>
                               </button>	<span class="pagedisplay"></span> 
                               <!-- this can be any element, including an input -->
                               <button type="button" class="btn next"><i class="icon-arrow-right glyphicon glyphicon-forward"></i>
                               </button>
                               <button type="button" class="btn last"><i class="icon-step-forward glyphicon glyphicon-step-forward"></i>
                               </button>
                               <select class="pagesize input-mini" title="Select page size">
                                   <option ';                         
                              if($limit==10) { $content .= ' selected="selected"'; }                             
                             $content .= ' value="10">10</option>
                                   <option ';
                              if($limit==20) { $content .= ' selected="selected"'; }                             
                             $content .= ' value="20">20</option>
                                   <option ';
                              if($limit==40) { $content .= ' selected="selected"'; }                             
                             $content .= ' value="40">40</option>'
                                     . '<option ';                               
                             if($limit==0) { $content .= ' selected="selected"'; }                             
                             $content .= ' value="99999">All</option>';
                            $content .= '
                               </select>
                               <select class="pagenum input-mini" title="Select page number"></select>';
                            $content .= '</th></tr></tfoot>';
                       }
                        $content .= '</table></div>';
                    } else {
                        $content = __('table is empty', 'wptm');
                    }
                    
                }
                
                $this->styleRender($table);
             
        }
                                      
        return $content;
    }
   
    private function replaceChart($id_chart,$table) {
        $content = '';
        $modelChart =  Model::getInstance('chart'); 
        $chart = $modelChart->getChart($id_chart);           
        if($chart) {
                
                $chartData = $this->getChartData($chart->datas, $chart->tData);         
                $chartConfig = json_decode($chart->config);                             
                $jsChartData = $this->buildJsChartData($chartData, $chart->type, $chartConfig);
                if(!$chartConfig) {
                    $chartConfig = new stdClass();                   
                }
                $chartConfig->width = isset($chartConfig->width) ? $chartConfig->width : 500;
                $chartConfig->height = isset($chartConfig->height) ? $chartConfig->height : 375;
                $chartConfig->chart_align = isset($chartConfig->chart_align) ? $chartConfig->chart_align : "center";
                
                $js = 'var DropChart = {};' . "\n";            
                $js .= 'DropChart.id = "'.$chart->id .'" ; ' . "\n";
                $js .= 'DropChart.type = "'.$chart->type .'" ; ' . "\n";
                $js .= 'DropChart.data = '.$jsChartData .'; ' . "\n";
                if($chart->config) {
                    $js .= 'DropChart.config = '. $chart->config .'; ';                
                }else {
                    $js .= 'DropChart.config = {} ; ';                
                }
                $js .= ' if(typeof DropCharts == "undefined") { var DropCharts = []; } ; ';    
                
                $js .= ' DropCharts.push(DropChart) ; ';    
                
                wp_enqueue_script('jquery');
                wp_enqueue_script('wptm_chart', plugins_url( 'assets/js/chart.min.js' , __FILE__ ),array(),WPTM_VERSION);
                wp_enqueue_script('wptm_dropchart', plugins_url( 'assets/js/dropchart.js' , __FILE__ ),array(),WPTM_VERSION);
                 
                $content = '<div class="chartContainer" id="chartContainer'. $chart->id. '">';
                
                $align = "";
                switch ($chartConfig->chart_align){
                    case 'left':
                        $align = " margin : 0 auto 0 0; ";
                        break;
                    case 'right':
                        $align = " margin : 0 0 0 auto ";
                        break;
                    case 'none':
                        break;
                    case 'center':
                    default:
                        $align = " margin : 0 auto 0 auto ";
                        break;
                }
        
                $content .= '<div class="canvasWraper" style="max-height:'.$chartConfig->height 
                                . 'px; max-width:'. $chartConfig->width.'px;'.$align.'" >';
                $content .= '<canvas class="canvas"  height="' . $chartConfig->height . '" width="' . $chartConfig->width  . '"></canvas>';                                 
                $content .= '</div></div>';    
                $content .= '<script>'.$js.'</script>';
        }
        
        return $content; 
    }             
    
    private function styleRender($table){
     
       require_once dirname(WPTM_PLUGIN_FILE).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR. 'wptmHelper.php';
       $wptmHelper = new WptmHelper();
       $wptmHelper->styleRender($table);
                
        wp_enqueue_style('wptm-table-'.$table->id, plugins_url( 'assets/tables/'.$table->id.'_'.$table->hash.'.css' , __FILE__ ),array(),WPTM_VERSION);
        wp_enqueue_style('wptm-front', plugins_url( 'assets/css/front.css' , __FILE__ ),array(),WPTM_VERSION);
    }

    
   private function buildJsChartData($data, $type, $config) {
       $result = '';
      
       if(!$config || !is_object($config)) {
           $config = new stdClass();
           $config->pieColors = "";
           $config->colors = "";
       }
       $config->dataUsing = isset($config->dataUsing) ? $config->dataUsing : "row";
       $config->useFirstRowAsLabels = isset($config->useFirstRowAsLabels) ? $config->useFirstRowAsLabels : false;
       $dataSets = $this->getDataSets($data, $config->dataUsing);
       if(!isset($dataSets->data) || (count($dataSets->data)==0) ) {
           return $result;
       }
       
       switch ($type) {
           case 'PolarArea':
           case 'Pie':
           case 'Doughnut':
               $chartData = $this->convertForPie($dataSets->data[0], $dataSets->axisLabels, $config->pieColors );
               break;
           
           case 'Bar':
           case 'Radar':
           case 'Line':
           default:
               $chartData = $this->convertForLine($dataSets,$config->useFirstRowAsLabels, $config->colors);
               break;
       }
       $result  = json_encode($chartData);
       return $result;
   }
         
    private function getDataSets($cellsData, $dataUsing) {
        $result = new stdClass();
        $result->data = array();
        $result->graphLabel = array();
        $result->axisLabels = array();
        
        $axisLabels = array();
        $grapLabels = array();
        
         $rValid = $this->hasNumbericRow($cellsData);
         $rCellsData = $this->transposeArr($cellsData);
         $cValid = $this->hasNumbericRow($rCellsData);
                
        if (!$rValid && !$cValid) { //remove first row and column
             $axisLabels = array_shift($cellsData);
             array_shift($axisLabels);
        
            $rcellsData = $this->transposeArr($cellsData);    
            $grapLabels = array_shift($rcellsData);
            $cellsData = $this->transposeArr($rcellsData);            
        
        }else if(!$rValid && $cValid) { // remove first column
             $rcellsData = $this->transposeArr($cellsData); 
             $grapLabels = array_shift($rcellsData);
             $cellsData = $this->transposeArr($rcellsData);  
             $axisLabels = $cellsData[0];
        }else if(!$cValid && $rValid) { //remove first row
            $axisLabels = array_shift($cellsData);
			$rcellsData = $this->transposeArr($cellsData); 
            $grapLabels = $rcellsData[0];            
        }else {
            //do nothing yet
            $axisLabels = $cellsData[0];
            $rcellsData = $this->transposeArr($cellsData); 
            $grapLabels = $rcellsData[0];
        }
        
         //switch cell data and label
        if($dataUsing != "row") {
            $cellsData = $this->transposeArr($cellsData);  
            $temp = $axisLabels;
            $axisLabels = $grapLabels;
            $grapLabels = $temp;
        }
        
        $result->axisLabels = $axisLabels;
        $j = 0;
        for ($i = 0; $i < count($cellsData); $i++) {
                
            if ($this->isNumbericArray($cellsData[$i])) {               
                $result->data[$j] = $cellsData[$i];      
                $result->graphLabel[$j] =  $grapLabels[$i];     
                $j++;
            }
        }
        
        if ( count($result->data) == 0) {
            $cellsData = $this->transposeArr($cellsData);
            for ($i = 0; $i < count($cellsData); $i++) {
                
                if ($this->isNumbericArray($cellsData[$i])) {               
                    $result->data[$j] = $cellsData[$i];      
                    $result->graphLabel[$j] =  $grapLabels[$i];      
                    $j++;
                }
            }
        }
        
         return $result;   
    
    }
    
    private function convertForLine($dataSets, $useFirstRowAsLabels, $colors) {
        $result = new stdClass();
        $result->labels = array();
        $result->datasets = array();
        if(!is_array($dataSets->data) || (count($dataSets->data)==0) ) {
            return $result;
        }
        if($useFirstRowAsLabels) {
            for($i=0;$i< count($dataSets->data[0]); $i++) {
                $result->labels[$i] = $dataSets->axisLabels[$i];
            }
        } else {
            for($i=0;$i< count($dataSets->data[0]); $i++) {
                $result->labels[$i] = "";
            }
        }
        
       
         for($i=0;$i< count($dataSets->data); $i++) {
             $dataSet = new stdClass();
             $dataSet->data = $dataSets->data[$i];
             $dataSet->label = $dataSets->graphLabel[$i];
             $styleSet  = $this->getStyleSet($i, $colors); 
             $dataSet = (object) array_merge((array) $dataSet, (array)$styleSet );
             $result->datasets[$i] = $dataSet;
         }
         
         return $result;
    }
    
    private function convertForPie($data, $axisLabels, $pieColors) {
      
        $datas = array();
      
        $defaultColors = array("#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360");
        $highlights = array("#FF5A5E", "#5AD3D1", "#FFC870", "#A8B3C5", "#616774");
        
        if(!$pieColors) {
            $colors = $defaultColors;      
        }else {                        
            $colors = explode(",",$pieColors) ; 
        }
        
        for ( $i = 0; $i < count($data); $i++) {
            
            $dataSet = new stdClass();
            $dataSet->value = (float)$data[$i];
            $dataSet->label = (string)$axisLabels[$i];
            if(isset($colors[$i])) {
                $dataSet->color = $colors[$i];
                $dataSet->highlight = $this->alter_brightness($colors[$i],0.3);
            }else {
                $dataSet->color = $defaultColors[$i % 5];
                $dataSet->highlight = $highlights[$i % 5];
            }
                        
            $datas[$i] = $dataSet;
        }
        
        return $datas;
    }

    function alter_brightness($colourstr, $steps) {
        $colourstr = str_replace('#','',$colourstr);
        $rhex = substr($colourstr,0,2);
        $ghex = substr($colourstr,2,2);
        $bhex = substr($colourstr,4,2);

        $r = hexdec($rhex);
        $g = hexdec($ghex);
        $b = hexdec($bhex);

        $r = max(0,min(255,$r + $r*$steps));
        $g = max(0,min(255,$g + $g *$steps));  
        $b = max(0,min(255,$b + $b*$steps));

        return '#'.dechex($r).dechex($g).dechex($b);
    }

    private function getStyleSet($i, $colors) {
        $result =  null;
        $defaultColors = array("#DCDCDC","#97BBCD","#4C839E");
        
        if(!$colors) {
            $arrColors = $defaultColors;
        }else {
            $arrColors = explode(",", $colors);     
        }
                
        if( count($arrColors) && isset($arrColors[$i]) ) {
            $color = $arrColors[$i];            
        }else {
            $color = $defaultColors[$i % 3];
        }
        $result = new ChartStyleSet($color);
        
        return $result;
    }
    
 
    private function isNumbericArray($arr) { 
        $valid = true;
        for ($c = 0; $c < count($arr); $c++) {
            if (!is_numeric($arr[$c]) ) {
                $valid = false;
            }
        }

        return $valid;
    }
    
    private function hasNumbericRow($cells) {
        $rValid = true;
        $rNaN = 0;
        for ($r = 0; $r < count($cells); $r++) {

            $valid = true;
            for ($c = 0; $c < count($cells[$r]); $c++) {
                if (!is_numeric($cells[$r][$c])) {
                    $valid = false;
                }
            }

            if (!$valid) {
                $rNaN++;
            }
        }
        if ($rNaN == count($cells)) {
            $rValid = false;
        }

        return $rValid;
    }
    
    private function transposeArr($array) {
        $transposed_array = array();
        if ($array) {
            foreach ($array as $row_key => $row) {
                if (is_array($row) && !empty($row)) { //check to see if there is a second dimension
                    foreach ($row as $column_key => $element) {
                        $transposed_array[$column_key][$row_key] = $element;
                    }
                } else {
                    $transposed_array[0][$row_key] = $row;
                }
            }
            return $transposed_array;
        }
    }

    
    private function getChartData($cellRange, $tData) {
        $result =  array();
        $arr_cellRanges = json_decode($cellRange) ;
        $tableData = json_decode($tData) ;
        //var_dump($tableData);
        for($i=0; $i < count($arr_cellRanges); $i++) {
            $row = $arr_cellRanges[$i];
            for($j=0 ; $j< count($row); $j++) {
                $result[$i][$j] = $this->getCellData($row[$j], $tableData);                
            }
        }
        
        return $result;
    }
    
    private function getCellData($cellPos, $tableData) {
        $result = '';
        //var_dump($cellPos);
        list($r,$c) = explode(':', $cellPos);
        $result = $tableData[$r][$c];
        return $result;
    }
        
}

class ChartStyleSet {
    
      public  $fillColor ;
      public  $strokeColor ;
      public  $pointColor ;
      public  $pointStrokeColor ;
      public  $pointHighlightFill ;
      public  $pointHighlightStroke ;
      
      public function __construct($color) {
           $this->fillColor = $this->hex2rgba($color, 0.2);
           $this->strokeColor = $this->hex2rgba($color, 0.5);
           $this->pointColor = $this->hex2rgba($color, 1);
           $this->pointStrokeColor = "#fff";
           $this->pointHighlightFill = "#fff";
           $this->pointHighlightStroke = $this->hex2rgba($color, 1);
           
      }
      
      function hex2rgba($color, $opacity = false) {

	$default = 'rgb(0,0,0)';

	//Return default if no color provided
	if(empty($color))
          return $default; 

	//Sanitize $color if "#" is provided 
        if ($color[0] == '#' ) {
        	$color = substr( $color, 1 );
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }

        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if($opacity){
        	if(abs($opacity) > 1)
        		$opacity = 1.0;
        	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
        	$output = 'rgb('.implode(",",$rgb).')';
        }

        //Return rgb(a) color string
        return $output;
    }

     
}  