<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */
//-- No direct access
defined('ABSPATH') || die();

class WptmHelper {

    public static function compileStyle($table, $style, $customCss) {

        $folder = plugin_dir_path(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR;
        $file = $folder . $table->id . '_' . $table->hash . '.css';
        if (!file_exists($file)) {
            $files = glob($folder . $table->id . '_*.css');
            foreach ($files as $f) {
                unlink($f);
            }
        } else {
            return true;
        }

        $folder_admin = dirname(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin';
        require_once $folder_admin . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'lessphp.php';
        require_once $folder_admin . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'csstidy' . DIRECTORY_SEPARATOR . 'class.csstidy.php';

        $less = new lessc;
        try {
            $content = $less->compile('#wptmtable' . $table->id . '.wptmtable {' . $style . '}');
        } catch (Exception $exc) {
            return false;
        }

        try {
            $customContent = $less->compile('#wptmtable' . $table->id . '.wptmtable table tbody {' . $customCss . '}');
        } catch (Exception $exc) {
            $customContent = "";
        }
        $content .= $customContent;
        $csstidy = new csstidy();
        $csstidy->parse($content);

        $less->setFormatter('compressed');
        try {
            $content = $less->compile($csstidy->print->plain());
        } catch (Exception $exc) {
            return false;
        }


        if (!file_put_contents($file, $content)) {
            echo 'error saving file!';
            return false;
        }
        return true;
    }

    public function styleRender($table) {

        $folder = plugin_dir_path(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR;
        $file = $folder . $table->id . '_' . $table->hash . '.css';
        if (!file_exists($file)) {
            $files = glob($folder . $table->id . '_*.css');
            foreach ($files as $f) {
                unlink($f);
            }
        } else {
            return true;
        }
        $style = json_decode($table->style);
        if ($style === null) {
            $content = '';
        } else {

            $content = 'table {';

            //render global table params
            if (isset($style->table->alternate_row_odd_color) && $style->table->alternate_row_odd_color) {
                $content .= "tr:nth-child(even) td {background-color: " . $style->table->alternate_row_odd_color . ";}";
            }
            if (isset($style->table->alternate_row_even_color) && $style->table->alternate_row_even_color) {
                $content .= "tr:nth-child(odd) td {background-color: " . $style->table->alternate_row_even_color . ";}";
            }
            if (isset($style->table->row_border) && $style->table->row_border) {
                $content .= "tr td {border-bottom: " . $style->table->row_border . ";}";
            }

            //render global rows
            foreach ($style->rows as $row) {
                if (isset($row[1]->height)) {
                    $content .= ".dtr" . (int) ($row[0]) . " {height: " . (int) $row[1]->height . "px;}";
                }
            }

            //render global cols
            foreach ($style->cols as $col) {
                if (isset($col[1]->width)) {
                    $content .= ".dtc" . (int) ($col[0]) . " {width: " . (int) $col[1]->width . "px; min-width: " . (int) $col[1]->width . "px;}";
                }
            }

            foreach ($style->cells as $cell) {
                $cell_style = "";
                if (isset($cell[2]->cell_background_color) && !empty($cell[2]->cell_background_color)) {
                    $cell_style .= "background-color: ".$cell[2]->cell_background_color. "; ";
                }
                if (isset($cell[2]->cell_border_top)) {
                    $cell_style .= " border-top: ".$cell[2]->cell_border_top.";";
                }
                if (isset($cell[2]->cell_border_right)) {
                    $cell_style .= " border-right: ".$cell[2]->cell_border_right.";";
                }
                if (isset($cell[2]->cell_border_bottom)) {
                    $cell_style .= " border-bottom: ".$cell[2]->cell_border_bottom.";";
                }
                if (isset($cell[2]->cell_border_left)) {
                    $cell_style .= " border-left: ".$cell[2]->cell_border_left.";";
                }
                if (isset($cell[2]->cell_font_family)) {
                    $cell_style .= " font-family: ".$cell[2]->cell_font_family.";";
                }
                if (isset($cell[2]->cell_font_size)) {
                    $cell_style .= " font-size: ".$cell[2]->cell_font_size."px;";
                }
                if (isset($cell[2]->cell_font_color) && $cell[2]->cell_font_color !== '') {
                    $cell_style .= " color: ".$cell[2]->cell_font_color.";";
                }
                if (isset($cell[2]->cell_font_bold) && $cell[2]->cell_font_bold === true) {
                    $cell_style .=" font-weight: bold;";
                }
                if (isset($cell[2]->cell_font_italic) && $cell[2]->cell_font_italic === true) {
                    $cell_style .=" font-style: italic;";
                }
                if (isset($cell[2]->cell_font_underline) && $cell[2]->cell_font_underline === true) {
                    $cell_style .= " text-decoration: underline;";
                }
                if (isset($cell[2]->cell_text_align)) {
                    $cell_style .= " text-align: ".$cell[2]->cell_text_align.";";
                }
                if (isset($cell[2]->cell_vertical_align)) {
                    $cell_style .= " vertical-align: ".$cell[2]->cell_vertical_align.";";
                }
                if (isset($cell[2]->cell_padding_left)) {
                    $cell_style .= " padding-left: ".$cell[2]->cell_padding_left."px;";
                }
                if (isset($cell[2]->cell_padding_top)) {
                    $cell_style .= " padding-top: ".$cell[2]->cell_padding_top."px;";
                }
                if (isset($cell[2]->cell_padding_right)) {
                    $cell_style .= " padding-right: ".$cell[2]->cell_padding_right."px;";
                }
                if (isset($cell[2]->cell_padding_bottom)) {
                    $cell_style .= " padding-bottom: ".$cell[2]->cell_padding_bottom."px;";
                }
                if (isset($cell[2]->cell_background_radius_left_top)) {
                    $cell_style .= " border-top-left-radius: ".$cell[2]->cell_background_radius_left_top."px;";
                }
                if (isset($cell[2]->cell_background_radius_right_top)) {
                    $cell_style .= " border-top-right-radius: ".$cell[2]->cell_background_radius_right_top."px;";
                }
                if (isset($cell[2]->cell_background_radius_right_bottom)) {
                    $cell_style .= " border-bottom-right-radius: ".$cell[2]->cell_background_radius_right_bottom."px;";
                }
                if (isset($cell[2]->cell_background_radius_left_bottom)) {
                    $cell_style .= " border-bottom-left-radius: ".$cell[2]->cell_background_radius_left_bottom."px;";
                }
                $content .= ".dtr".(int)($cell[0]).".dtc".(int)($cell[1])." {". $cell_style . "}";
                if (isset($cell[2]->tooltip_width) && !empty($cell[2]->tooltip_width) ) {                    
                    $content .= ".dtr" . (int) ($cell[0]) . ".dtc" . (int) ($cell[1]) . " .wptm_tooltipcontent_show {width: " . $cell[2]->tooltip_width . "px; }";
                }else {
                    //$content .= ".dtr" . (int) ($cell[0]) . ".dtc" . (int) ($cell[1]) . " .wptm_tooltipcontent_show
                    // {width: 200px; }";
                }
            }
            if (isset($style->table->width) && $style->table->width > 0) {
                $content .= "& {width : " . $style->table->width . "px;}";
            }

            if (!isset($style->table->table_align)) {
                $style->table->table_align = 'center';
            }
            switch ($style->table->table_align) {
                case 'left':
                    $content .= "& {margin : 0 auto 0 0 }";
                    break;
                case 'right':
                    $content .= "& {margin : 0 0 0 auto }";
                    break;
                case 'none':
                    break;
                case 'center':
                default:
                    $content .= "& {margin : 0 auto 0 auto }";
                    break;
            }

            $content .= '}';
        }

        require_once dirname(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'wptmHelper.php';
        WptmHelper::compileStyle($table, $content, $table->css);
    }

    public function htmlRender($table) {
        
        $table_hash = md5($table->datas. $table->style. $table->params);        
        $folder = plugin_dir_path(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'tables' . DIRECTORY_SEPARATOR;
        $file = $folder . $table->id . '_' . $table_hash . '.html';
        if (!file_exists($file)) {
            $files = glob($folder . $table->id . '_*.html');
            foreach ($files as $f) {
                unlink($f);
            }
        } else {
            return true;
        }
        
        $datas = json_decode($table->datas);
        $style = json_decode($table->style); 
        if(isset($style->table->responsive_type) && $style->table->responsive_type=="hideCols"){
            $responsive_type = "hideCols";
        }else {
            $responsive_type = "scroll";
        }
        if(!isset($style->table->freeze_col)) $style->table->freeze_col = 0;
        if(!isset($style->table->freeze_row)) $style->table->freeze_row = 0;
        if(!isset($style->table->enable_filters)) $style->table->enable_filters = 0;
        
        if (isset($style->table->use_sortable) && $style->table->use_sortable == 1) {
            $sortable = true;
            $heads = 1;
        } else {
            $sortable = false;
            $heads = 0;
            if($responsive_type == "scroll"){
                    if( ($style->table->freeze_row) || ($style->table->freeze_col) ){                      
                          $heads = 1;
                    }
             }
             
            if( $style->table->enable_filters) {
                $heads = 1;
            }          
        }
    
        if(is_string($table->params)) $table->params =  json_decode($table->params,true);
        if (isset($table->params['mergeSetting'])) {
            $mergeSetting = json_decode($table->params['mergeSetting']);
        } else {
            $mergeSetting = array();
        }
        
        if($responsive_type == "hideCols"){
                $hideCols = 1;
                $res_prioritys = array();
                foreach ($style->cols as $col) {
                    if(isset($col[1]->res_priority)){
                        $res_prioritys[(string)$col[0]] = ($col[1]->res_priority == "persistent") ? "persistent" : (int)$col[1]->res_priority;                          
                    }
                }               
                $priority = json_encode($res_prioritys,JSON_FORCE_OBJECT);
                
        }else{
                $hideCols = 0;    
                $priority="{}";
        }
        
        $content = '<div class="wptmresponsive  wptmtable" id="wptmtable' . (int) $table->id . '">';
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
        }
                
        $content .= '<table id="wptmTbl' . (int) $table->id . '" data-id="'.$table->id. '" data-hidecols="'.$hideCols.'" data-priority="'.$priority. '"'
                  . '  data-freeze-cols="'.(int)$style->table->freeze_col . '"  data-freeze-rows="'.(int)$style->table->freeze_row 
                  .  '" data-table-height="'.(int)$style->table->table_height . '" class="'. $tblCls  . '">';

        $rowNb = 0;
        foreach ($datas as $row) {
            if ($rowNb < $heads && $rowNb == 0) {
                $content .= '<thead>';
            } else {
                if ($rowNb == $heads) {
                    $content .= '<tbody>';
                }
            }
            $content .= '<tr>';
            $colNb = 0;
            foreach ($row as $col) {
                $mergeInfo = self::checkMergeInfo($rowNb, $colNb, $mergeSetting);

                if ($rowNb < $heads) {
                    $content .= '<th ' . $mergeInfo . ' class="dtr' . $rowNb . ' dtc' . $colNb . '">';
                } else {
                    $content .= '<td ' . $mergeInfo . ' class="dtr' . $rowNb . ' dtc' . $colNb . '">';
                }
                $cellHtml = "";
                if (isset($col[0]) && $col[0] === '=') {
                    $pattern = '@^=(SUM|COUNT|MIN|MAX|AVG|CONCAT|sum|count|min|max|avg|concat)\\((.*?)\\)$@';
                    if (preg_match($pattern, $col, $matches)) {
                        $cells = explode(";", $matches[2]);
                        $values = array();
                        foreach ($cells as $cell) {
                            $vals = explode(":", $cell);
                            $pattern2 = '@([a-zA-Z]+)([0-9]+)@';
                            if (sizeof($vals) === 1) { //single cell
                                preg_match($pattern2, $cell, $val1);
                                $d = $datas[$val1[2] - 1][self::convertAlpha($val1[1]) - 1];
                                $values[] = $d;
                            } else { //range          
                                preg_match($pattern2, $vals[0], $val1);
                                preg_match($pattern2, $vals[1], $val2);
                                for ($il = $val1[2] - 1; $il <= $val2[2] - 1; $il++) {
                                    for ($ik = self::convertAlpha($val1[1]) - 1; $ik <= self::convertAlpha($val2[1]) - 1; $ik++) {
                                        $values[] = $datas[$il][$ik];
                                    }
                                }
                            }
                        }
                        switch (strtoupper($matches[1])) {
                            case 'SUM':
                                $this->resultCalc = 0;
                                array_map(function($foo) {
                                    if (is_numeric($foo)) {
                                        $this->resultCalc += $foo;
                                    }
                                }, $values);
                                break;
                            case 'COUNT':
                                $this->resultCalc = 0;
                                array_map(function($foo) {
                                    if (is_numeric($foo)) {
                                        $this->resultCalc += 1;
                                    }
                                }, $values);
                                break;
                            case 'MIN':
                                $this->resultCalc = null;
                                array_map(function($foo) {
                                    if (is_numeric($foo)) {
                                        if ($this->resultCalc === null || $this->resultCalc > $foo) {
                                            $this->resultCalc = $foo;
                                        }
                                    }
                                }, $values);
                                break;
                            case 'MAX':
                                $this->resultCalc = 0;
                                array_map(function($foo) {
                                    if (is_numeric($foo)) {
                                        if ($this->resultCalc < $foo) {
                                            $this->resultCalc = $foo;
                                        }
                                    }
                                }, $values);
                                break;
                            case 'AVG':
                                $this->resultCalc = 0;
                                $this->n = 0;
                                array_map(function($foo) {
                                    if (is_numeric($foo)) {
                                        $this->resultCalc += $foo;
                                        $this->n++;
                                    }
                                }, $values);
                                if ($this->n > 0) {
                                    $this->resultCalc = $this->resultCalc / $this->n;
                                }
                                break;
                            case 'CONCAT':
                                $this->resultCalc = '';
                                array_map(function($foo) {
                                    if (isset($foo[0]) && (string) $foo[0] !== '=')
                                        $this->resultCalc .= (string) $foo;
                                }, $values);
                                break;
                        }
                    }
                    $cellHtml .= $this->resultCalc;
                }elseif (isset($style->cells->{$rowNb . '!' . $colNb}) && isset($style->cells->{$rowNb . '!' . $colNb}[2]->cell_type) && $style->cells->{$rowNb . '!' . $colNb}[2]->cell_type == 'html') {
                    $cellHtml .= $col;
                } else {
                    $cellHtml .= nl2br($col);
                }
                
                if (isset($style->cells->{$rowNb . '!' . $colNb}) && isset($style->cells->{$rowNb . '!' . $colNb}[2]->tooltip_content) && $style->cells->{$rowNb . '!' . $colNb}[2]->tooltip_content != '') {
                    $content .= '<span class="wptm_tooltip ">'.$cellHtml.'<span class="wptm_tooltipcontent">'.$style->cells->{$rowNb . '!' . $colNb}[2]->tooltip_content .'</span></span>';
                }else {
                    $content .= $cellHtml;
                }
                
                if ($rowNb < $heads) {
                    $content .= '</th>';
                } else {
                    $content .= '</td>';
                }
                $colNb++;
            }
            if ($rowNb < $heads) {
                if ($rowNb == $heads) {
                    $content .= '</thead>';
                }
                $content .= '</th>';
            } else {
                $content .= '</tr>';
            }
            $rowNb++;
        }

        $content .= '</tbody>';
        
        if( $style->table->enable_filters && count($datas)>10) {
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
                    <option selected="selected" value="10">10</option>
                    <option value="20">20</option>
                    <option value="30">30</option>
                    <option value="40">40</option>
                </select>
                <select class="pagenum input-mini" title="Select page number"></select>';
             $content .= '</th></tr></tfoot>';
        }
        
        $content .= '</table></div>';

        if (!file_put_contents($file, esc_html($content))) {
            echo 'error saving file!';
            return false;
        }
        return true;        
    }

    private static function checkMergeInfo($rowNb, $colNb, $mergeSettings) {
        $result = '';
        if (!is_array($mergeSettings) || count($mergeSettings) == 0) {
            return $result;
        }
        foreach ($mergeSettings as $ms) {
            if ($ms->row == $rowNb && $ms->col == $colNb) {
                $result = ' rowspan="' . $ms->rowspan . '" colspan="' . $ms->colspan . '" ';
            } else if ($ms->row <= $rowNb && $rowNb < $ms->row + $ms->rowspan && $ms->col <= $colNb && $colNb < $ms->col + $ms->colspan) {
                $result = ' style="display:none" ';
            }
        }

        return $result;
    }

    private static function convertAlpha($col) {
        $col = str_pad($col, 2, '0', STR_PAD_LEFT);
        $i = ($col{0} == '0') ? 0 : (ord($col{0}) - 64) * 26;
        $i += ord($col{1}) - 64;

        return $i;
    }

}