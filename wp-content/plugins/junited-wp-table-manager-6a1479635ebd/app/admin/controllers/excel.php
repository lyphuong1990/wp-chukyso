<?php

/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */
use Joomunited\WPFramework\v1_0_2\Controller;
use Joomunited\WPFramework\v1_0_2\Utilities;

defined('ABSPATH') || die();

class wptmControllerExcel extends Controller {

    private $error_message = '';
    private $allowed_ext = array('xls', 'xlsx');

    public function import() {
        $json = array();
        $file= Utilities::getInput('file', 'POST','string');
      
        $upload_dir = wp_upload_dir();
        $targetPath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'wptm' . DIRECTORY_SEPARATOR;
        if($file)  {
            $file = $targetPath . $file;
        }else {
            $file = $this->_uploadFile();
        }
        if ($file) {
            $id_table = Utilities::getInt('id_table', 'POST');
            $onlydata = Utilities::getInt('onlydata', 'POST');
            $ignoreCheck = Utilities::getInt('ignoreCheck', 'POST');
            
            $modelTable = $this->getModel('table');
            $tableContent = (array) $modelTable->getItem($id_table);
            $tblStyles = json_decode($tableContent['style'], true);

            $folder_admin = dirname(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin';
            require_once $folder_admin . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'phpexcel' . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'IOFactory.php';

            //only import data area
            $objPHPExcel = PHPExcel_IOFactory::load($file);
            $sheet = $objPHPExcel->getActiveSheet();
            $maxCell = $sheet->getHighestRowAndColumn();
            $datas = $sheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row'], "");
            $datas= $this->utf8_converter($datas);

			if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
				$tableContent['datas'] = json_encode($datas, JSON_UNESCAPED_UNICODE);
                //fix double quote in cell content
                $tableContent['datas'] = str_replace('\\"','\\\"', $tableContent['datas']);
			} else {
				$tableContent['datas'] = json_encode($datas);
			}

            if(empty($tableContent['datas'])) {
                switch (json_last_error()) {
                    case JSON_ERROR_NONE:
                        echo ' - No errors';
                        break;
                    case JSON_ERROR_DEPTH:
                        echo ' - Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        echo ' - Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        echo ' - Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        echo ' - Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        echo ' - Unknown error';
                        break;
                }
                die();
            }

            if(!$ignoreCheck && ( ($maxCell['column']>=500) || ($maxCell['row']>= 500)) ) {
                  
                 $this->exit_status(true, array('too_large'=>1,'msg'=> __("Note: The spreadsheet you're trying to import has more than 500 rows or columns. It may be impossible to run the import depending of your server memory limit", 'wptm') ,
                                    'onlydata'=> $onlydata, 'id'=> $id_table,'file'=> basename($file) ) );
            }
            if (!$onlydata) {
                $maxColIndex = PHPExcel_Cell::columnIndexFromString($maxCell['column']);

                $ci = 0;
                $tblStyles["cols"] = array();
                foreach ($sheet->getColumnDimensions() as $cd) {
                    $tblStyles["cols"][$ci][0] = $ci;
                    $tblStyles["cols"][$ci][1]["width"] = $cd->getWidth() * 10; //Excel unit: number of characters that can be displayed with the standard font
                    $ci++;
                }

                $ri = 0;
                $tblStyles["rows"] = array();
                foreach ($sheet->getRowDimensions() as $rd) {
                    $tblStyles["rows"][$ri][0] = $ri;
                    $tblStyles["rows"][$ri][1]["height"] = floor($rd->getRowHeight() * 1.333333); //1 point = 1.333333 px
                    $ri++;
                }

                $tblStyles["cells"] = array();
                for ($ri = 0; $ri < $maxCell['row']; $ri++) {
                    for ($ci = 0; $ci < $maxColIndex; $ci++) {
                        $tblStyles["cells"][$ri . '!' . $ci] = array($ri, $ci);

                        $cellStyle = $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($ci) . ($ri + 1));
                        $cellCss = $this->_createCSSStyle($cellStyle);
                        $tblStyles["cells"][$ri . '!' . $ci][2] = $cellCss;
                    }
                }

                $tblStyles["table"] = array();
                $tableContent['style'] = json_encode($tblStyles);
            }

            //Read Merged Cells info
            $mergeSettings = array();
            $mergeRanges = $objPHPExcel->getActiveSheet()->getMergeCells();
            if (count($mergeRanges)) {
                foreach ($mergeRanges as $mergeRange) {
                    list($tlCell, $rbCell) = explode(":", $mergeRange);

                    list($tl_cNb, $tl_rNb) = PHPExcel_Cell::coordinateFromString($tlCell);
                    list($br_cNb, $br_rNb) = PHPExcel_Cell::coordinateFromString($rbCell);
                    $tl_cNb = PHPExcel_Cell::columnIndexFromString($tl_cNb);
                    $br_cNb = PHPExcel_Cell::columnIndexFromString($br_cNb);

                    $mergeSetting = new stdClass();
                    $mergeSetting->row = $tl_rNb - 1;
                    $mergeSetting->col = $tl_cNb - 1;
                    $mergeSetting->rowspan = $br_rNb - $tl_rNb + 1;
                    $mergeSetting->colspan = $br_cNb - $tl_cNb + 1;
                    $mergeSettings[] = $mergeSetting;
                }
            }

            $tableContent['params'] = array("mergeSetting" => json_encode($mergeSettings));

            if (!$modelTable->save($id_table, $tableContent)) {
                $this->exit_status(__('error while saving table', 'wptm'));
            } else {
                $this->exit_status(true);
            }
        } else {
            $this->exit_status($this->error_message);
        }
    }

    function utf8_converter($array)
    {
        ini_set('mbstring.substitute_character', "none");
        array_walk_recursive($array, function(&$item, $key){
            if(!mb_detect_encoding($item, 'utf-8', true)){
                $item= mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });

        return $array;
    }

    /**
     * Create CSS style
     *
     * @param	PHPExcel_Style		$pStyle			PHPExcel_Style
     * @return	array
     */
    private function _createCSSStyle(PHPExcel_Style $pStyle) {
        // Construct CSS
        $css = '';

        // Create CSS
        $css = array_merge(
                $this->_createCSSStyleAlignment($pStyle->getAlignment())
                , $this->_createCSSStyleBorders($pStyle->getBorders())
                , $this->_createCSSStyleFont($pStyle->getFont())
                , $this->_createCSSStyleFill($pStyle->getFill())
        );

        // Return
        return $css;
    }

    /**
     * Create CSS style (PHPExcel_Style_Alignment)
     *
     * @param	PHPExcel_Style_Alignment		$pStyle			PHPExcel_Style_Alignment
     * @return	array
     */
    private function _createCSSStyleAlignment(PHPExcel_Style_Alignment $pStyle) {
        // Construct CSS
        $css = array();

        // Create CSS
        $css['cell_vertical_align'] = $this->_mapVAlign($pStyle->getVertical());
        if ($textAlign = $this->_mapHAlign($pStyle->getHorizontal())) {
            $css['cell_text_align'] = $textAlign;
            if (in_array($textAlign, array('left', 'right')))
                $css['cell_padding_' . $textAlign] = (string) ((int) $pStyle->getIndent() * 9) . 'px';
        }

        // Return
        return $css;
    }

    /**
     * Create CSS style (PHPExcel_Style_Font)
     *
     * @param	PHPExcel_Style_Font		$pStyle			PHPExcel_Style_Font
     * @return	array
     */
    private function _createCSSStyleFont(PHPExcel_Style_Font $pStyle) {
        // Construct CSS
        $css = array();

        // Create CSS
        if ($pStyle->getBold()) {
            $css['cell_font_bold'] = true;
        }
        if ($pStyle->getUnderline() != PHPExcel_Style_Font::UNDERLINE_NONE && $pStyle->getStrikethrough()) {
            $css['cell_font_underline'] = true;
        } else if ($pStyle->getUnderline() != PHPExcel_Style_Font::UNDERLINE_NONE) {
            $css['cell_font_underline'] = true;
        } else if ($pStyle->getStrikethrough()) {
            $css['cell_font_underline'] = false;
        }
        if ($pStyle->getItalic()) {
            $css['cell_font_italic'] = true;
        }

        $css['cell_font_color'] = '#' . $pStyle->getColor()->getRGB();
        $css['cell_font_family'] = $pStyle->getName();
        $css['cell_font_size'] = floor($pStyle->getSize() * 96 / 72); //points = pixels * 72 / 96
        // Return
        return $css;
    }

    /**
     * Create CSS style (PHPExcel_Style_Borders)
     *
     * @param	PHPExcel_Style_Borders		$pStyle			PHPExcel_Style_Borders
     * @return	array
     */
    private function _createCSSStyleBorders(PHPExcel_Style_Borders $pStyle) {
        // Construct CSS
        $css = array();

        // Create CSS
        $css['cell_border_bottom'] = $this->_createCSSStyleBorder($pStyle->getBottom());
        $css['cell_border_top'] = $this->_createCSSStyleBorder($pStyle->getTop());
        $css['cell_border_left'] = $this->_createCSSStyleBorder($pStyle->getLeft());
        $css['cell_border_right'] = $this->_createCSSStyleBorder($pStyle->getRight());

        // Return
        return $css;
    }

    /**
     * Create CSS style (PHPExcel_Style_Border)
     *
     * @param	PHPExcel_Style_Border		$pStyle			PHPExcel_Style_Border
     * @return	string
     */
    private function _createCSSStyleBorder(PHPExcel_Style_Border $pStyle) {
        // Create CSS
//		$css = $this->_mapBorderStyle($pStyle->getBorderStyle()) . ' #' . $pStyle->getColor()->getRGB();
        //	Create CSS - add !important to non-none border styles for merged cells
        $borderStyle = $this->_mapBorderStyle($pStyle->getBorderStyle());
        $css = $borderStyle . ' #' . $pStyle->getColor()->getRGB() . (($borderStyle == 'none') ? '' : ' !important');

        // Return
        return $css;
    }

    /**
     * Create CSS style (PHPExcel_Style_Fill)
     *
     * @param	PHPExcel_Style_Fill		$pStyle			PHPExcel_Style_Fill
     * @return	array
     */
    private function _createCSSStyleFill(PHPExcel_Style_Fill $pStyle) {
        // Construct HTML
        $css = array();

        // Create CSS
        $value = $pStyle->getFillType() == PHPExcel_Style_Fill::FILL_NONE ?
                '' : '#' . $pStyle->getStartColor()->getRGB();
        $css['cell_background_color'] = $value;

        // Return
        return $css;
    }

    /**
     * Map VAlign
     *
     * @param	string		$vAlign		Vertical alignment
     * @return string
     */
    private function _mapVAlign($vAlign) {
        switch ($vAlign) {
            case PHPExcel_Style_Alignment::VERTICAL_BOTTOM: return 'bottom';
            case PHPExcel_Style_Alignment::VERTICAL_TOP: return 'top';
            case PHPExcel_Style_Alignment::VERTICAL_CENTER:
            case PHPExcel_Style_Alignment::VERTICAL_JUSTIFY: return 'middle';
            default: return 'baseline';
        }
    }

    /**
     * Map HAlign
     *
     * @param	string		$hAlign		Horizontal alignment
     * @return string|false
     */
    private function _mapHAlign($hAlign) {
        switch ($hAlign) {
            case PHPExcel_Style_Alignment::HORIZONTAL_GENERAL: return false;
            case PHPExcel_Style_Alignment::HORIZONTAL_LEFT: return 'left';
            case PHPExcel_Style_Alignment::HORIZONTAL_RIGHT: return 'right';
            case PHPExcel_Style_Alignment::HORIZONTAL_CENTER:
            case PHPExcel_Style_Alignment::HORIZONTAL_CENTER_CONTINUOUS: return 'center';
            case PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY: return 'justify';
            default: return false;
        }
    }

    /**
     * Map border style
     *
     * @param	int		$borderStyle		Sheet index
     * @return	string
     */
    private function _mapBorderStyle($borderStyle) {
        switch ($borderStyle) {
            case PHPExcel_Style_Border::BORDER_NONE: return 'none';
            case PHPExcel_Style_Border::BORDER_DASHDOT: return '1px dashed';
            case PHPExcel_Style_Border::BORDER_DASHDOTDOT: return '1px dotted';
            case PHPExcel_Style_Border::BORDER_DASHED: return '1px dashed';
            case PHPExcel_Style_Border::BORDER_DOTTED: return '1px dotted';
            case PHPExcel_Style_Border::BORDER_DOUBLE: return '3px double';
            case PHPExcel_Style_Border::BORDER_HAIR: return '1px solid';
            case PHPExcel_Style_Border::BORDER_MEDIUM: return '2px solid';
            case PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT: return '2px dashed';
            case PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT: return '2px dotted';
            case PHPExcel_Style_Border::BORDER_MEDIUMDASHED: return '2px dashed';
            case PHPExcel_Style_Border::BORDER_SLANTDASHDOT: return '2px dashed';
            case PHPExcel_Style_Border::BORDER_THICK: return '3px solid';
            case PHPExcel_Style_Border::BORDER_THIN: return '1px solid';
            default: return '1px solid'; // map others to thin
        }
    }

    private function _uploadFile() {
        if (!empty($_FILES)) {
            $tempFile = $_FILES['file'];
            //check file extension
            $tempFile['name'] = html_entity_decode($tempFile['name']);
            $ext = strtolower(pathinfo($tempFile['name'], PATHINFO_EXTENSION));
            $newname = uniqid() . '.' . $ext;
            if (!in_array($ext, $this->allowed_ext)) {
                $this->error_message = __('Wrong file extension', 'wptm');
                return false;
            }

            $upload_dir = wp_upload_dir();
            $targetPath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'wptm' . DIRECTORY_SEPARATOR;
            if (!file_exists($targetPath)) {
                mkdir($targetPath, 0777, true);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                $file = fopen($targetPath . 'index.html', 'w');
                fwrite($file, $data);
                fclose($file);
            }

            $targetFile = $targetPath . $newname;
            if (!move_uploaded_file($tempFile['tmp_name'], $targetFile)) {
                $this->error_message = __('Error orcured when retrieving file to temporary folder', 'wptm');
                return false;
            } else {
                return $targetFile;
            }
        } else {
            $this->error_message = __('Please choose a file before submit!', 'wptm');
            return false;
        }
    }

    public function export() {
        if ($file = $this->_makeFile() and is_readable($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);

            if (file_exists($file)) {
                unlink($file);
            }
        } else {
            echo $this->error_message;
        }
        die();
    }

    private function _makeFile() {

        $stdFormat = array('xlsx' => 'Excel2007', 'xls' => 'Excel5');
        $format_excel = Utilities::getInput('format_excel');
        $id = Utilities::getInt('id', 'GET');
        $onlydata = Utilities::getInt('onlydata', 'GET');
        $modelTable = $this->getModel('table');
        $tableContent = $modelTable->getItem($id);
        $datas = json_decode($tableContent->datas, 1);

        if (!is_array($datas)) {
            $this->error_message = __('Table is not existed! Please choose one another.', 'wptm');
            return false;
        } else {

            $folder_admin = dirname(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin';
            require_once $folder_admin . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'phpexcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php';

            $objPHPExcel = new PHPExcel();
            $activeSheet = $objPHPExcel->getActiveSheet();
            $activeSheet->fromArray($datas);

            if (!$onlydata) {

                $tblStyles = json_decode($tableContent->style, true);
                if (isset($tblStyles['rows'])) {
                    $rowStyles = $tblStyles['rows'];
                    if (!empty($rowStyles)) {
                        $rI = 0;
                        foreach ($rowStyles as $row) {
                            $rI++;
                            if (isset($row[1]) && isset($row[1]['height']) && $row[1]['height']) {
                                $activeSheet->getRowDimension($rI)->setRowHeight(floor($row[1]['height'] / 1.333333)); //px 2 pt
                            }
                        }
                    }
                }

                if (isset($tblStyles['cols'])) {
                    $colStyles = $tblStyles['cols'];
                    if (!empty($colStyles)) {
                        $cI = 0;
                        foreach ($colStyles as $col) {
                            if (isset($col[1]) && isset($col[1]['width']) && $col[1]['width']) {
                                $activeSheet->getColumnDimensionByColumn($cI)->setWidth(floor($col[1]['width'] / 10)); //Excel unit: number of characters that can be displayed with the standard font
                            }
                            $cI++;
                        }
                    }
                }

                if (isset($tblStyles['cells'])) {
                    $cellStyles = $tblStyles['cells'];
                    if (!empty($cellStyles)) {
                        foreach ($cellStyles as $key => $cellCss) {
                            $activeSheet = $this->setCellStyle($activeSheet, $cellCss[1], $cellCss[0] + 1, $cellCss[2]);
                        }
                    }
                }

                //var_dump($tblStyles['cells'] ); die();

                $tableParams = json_decode($tableContent->params, true);
                if (isset($tableParams['mergeSetting'])) {
                    $mergeSettings = json_decode($tableParams['mergeSetting'], true);
                } else {
                    $mergeSettings = array();
                }
                if (!empty($mergeSettings)) {
                    foreach ($mergeSettings as $mergeSetting) {
                        $activeSheet->mergeCellsByColumnAndRow($mergeSetting['col'], $mergeSetting['row'] + 1, $mergeSetting['col'] + $mergeSetting['colspan'] - 1, $mergeSetting['row'] + $mergeSetting['rowspan']);
                    }
                }
            }

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $stdFormat[$format_excel]);

            $upload_dir = wp_upload_dir();
            $targetPath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'wptm' . DIRECTORY_SEPARATOR;
            if (!file_exists($targetPath)) {
                mkdir($targetPath, 0777, true);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                $file = fopen($targetPath . 'index.html', 'w');
                fwrite($file, $data);
                fclose($file);
            }

            $tableContent->title = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', $tableContent->title));
            $file = $targetPath . DIRECTORY_SEPARATOR . $tableContent->title . '_' . $id . '.' . $format_excel;

            try {
                @$objWriter->save($file);
            } catch (Exception $e) {
                $this->error_message = __('Error orcured when creating file to export! <br/>Please try agiain.', 'wptm');
            }

            if (!file_exists($file) or ! is_readable($file)) {
                $this->error_message = __('Error orcured when creating file to export! <br/>Please try agiain.', 'wptm');
                return false;
            }
        }

        return $file;
    }

    public function setCellStyle($activeSheet, $col, $row, $css) {
        //font
        if (isset($css['cell_font_bold']) && $css['cell_font_bold']) {
            $activeSheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
        }
        if (isset($css['cell_font_underline']) && $css['cell_font_underline']) {
            $activeSheet->getStyleByColumnAndRow($col, $row)->getFont()->setUnderline(true);
        }
        if (isset($css['cell_font_italic']) && $css['cell_font_italic']) {
            $activeSheet->getStyleByColumnAndRow($col, $row)->getFont()->setItalic(true);
        }
        if (isset($css['cell_font_color']) && $css['cell_font_color']) {
            $activeSheet->getStyleByColumnAndRow($col, $row)->getFont()->getColor()->setRGB(str_replace("#", "", $css['cell_font_color']));
        }
        if (isset($css['cell_font_family']) && $css['cell_font_family']) {
            $activeSheet->getStyleByColumnAndRow($col, $row)->getFont()->setName($css['cell_font_family']);
        }
        if (isset($css['cell_font_size']) && $css['cell_font_size']) {
            $activeSheet->getStyleByColumnAndRow($col, $row)->getFont()->setSize($css['cell_font_size'] * 72 / 96); //points = pixels * 72 / 96
        }

        //Alignment
        if (isset($css['cell_vertical_align']) && $css['cell_vertical_align']) {
            if ($css['cell_vertical_align'] == 'middle') {
                $vertical = 'center';
            } else {
                $vertical = $css['cell_vertical_align'];
            }
            $activeSheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setVertical($vertical);
        }
        if (isset($css['cell_text_align']) && $css['cell_text_align']) {
            $horizontal = $css['cell_text_align'];
            $activeSheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal($horizontal);
        }

        //Fill
        if (isset($css['cell_background_color']) && $css['cell_background_color']) {
            $fill_color = str_replace("#", "", $css['cell_background_color']);
            $activeSheet->getStyleByColumnAndRow($col, $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $activeSheet->getStyleByColumnAndRow($col, $row)->getFill()->getStartColor()->setRGB($fill_color);
        }

        //Border
        if (isset($css['cell_border_bottom']) && $css['cell_border_bottom']) {
            list($bWidth, $bStyle, $bColor) = explode(" ", $css['cell_border_bottom']);
            $borderStyle = $this->getBorderStyle($bWidth, $bStyle);
            $activeSheet->getStyleByColumnAndRow($col, $row)->getBorders()->getBottom()->setBorderStyle($borderStyle);
            $bColor = str_replace("#", "", trim($bColor));
            $activeSheet->getStyleByColumnAndRow($col, $row)->getBorders()->getBottom()->getColor()->setRGB($bColor);
        }

        if (isset($css['cell_border_top']) && $css['cell_border_top']) {
            list($bWidth, $bStyle, $bColor) = explode(" ", $css['cell_border_top']);
            $borderStyle = $this->getBorderStyle($bWidth, $bStyle);
            $activeSheet->getStyleByColumnAndRow($col, $row)->getBorders()->getTop()->setBorderStyle($borderStyle);
            $bColor = str_replace("#", "", trim($bColor));
            $activeSheet->getStyleByColumnAndRow($col, $row)->getBorders()->getTop()->getColor()->setRGB($bColor);
        }

        if (isset($css['cell_border_left']) && $css['cell_border_left']) {
            list($bWidth, $bStyle, $bColor) = explode(" ", $css['cell_border_left']);
            $borderStyle = $this->getBorderStyle($bWidth, $bStyle);
            $activeSheet->getStyleByColumnAndRow($col, $row)->getBorders()->getLeft()->setBorderStyle($borderStyle);
            $bColor = str_replace("#", "", trim($bColor));
            $activeSheet->getStyleByColumnAndRow($col, $row)->getBorders()->getLeft()->getColor()->setRGB($bColor);
        }

        if (isset($css['cell_border_right']) && $css['cell_border_right']) {
            list($bWidth, $bStyle, $bColor) = explode(" ", $css['cell_border_right']);
            $borderStyle = $this->getBorderStyle($bWidth, $bStyle);
            $activeSheet->getStyleByColumnAndRow($col, $row)->getBorders()->getRight()->setBorderStyle($borderStyle);
            $bColor = str_replace("#", "", trim($bColor));
            $activeSheet->getStyleByColumnAndRow($col, $row)->getBorders()->getRight()->getColor()->setRGB($bColor);
        }

        return $activeSheet;
    }

    function getBorderStyle($bWidth, $bStyle) {
        $borderStyle = PHPExcel_Style_Border::BORDER_NONE;
        $bStyle = trim($bStyle);
        $bWidth = (int) $bWidth;
        if ($bWidth > 1) {
            switch ($bStyle) {
                case 'solid':
                    $borderStyle = PHPExcel_Style_Border::BORDER_MEDIUM;
                    break;
                case 'dashed':
                    $borderStyle = PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT;
                    break;
                case 'dotted':
                    $borderStyle = PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT;
                    break;
            }
        } else if ((int) $bWidth == 1) {
            switch ($bStyle) {
                case 'solid':
                    $borderStyle = PHPExcel_Style_Border::BORDER_HAIR;
                    break;
                case 'dashed':
                    $borderStyle = PHPExcel_Style_Border::BORDER_DASHED;
                    break;
                case 'dotted':
                    $borderStyle = PHPExcel_Style_Border::BORDER_DOTTED;
                    break;
            }
        } else {
            $borderStyle = PHPExcel_Style_Border::BORDER_NONE;
        }

        return $borderStyle;
    }

    function syncSpreadsheet() {

        global $wpdb;
        $query = 'SELECT c.* FROM ' . $wpdb->prefix . 'wptm_tables as c ORDER BY c.id_category ASC, c.position ASC ';
        $result = $wpdb->query($query);
        if ($result === false) {
            return false;
        }
        $tables = stripslashes_deep($wpdb->get_results($query, OBJECT));

        $count = 0;
        foreach ($tables as $table) {
            $tblStyles = json_decode($table->style, true);

            if (isset($tblStyles['table']) && isset($tblStyles['table']['spreadsheet_url']) && $tblStyles['table']['spreadsheet_url']) {
                $spreadsheet_url = $tblStyles['table']['spreadsheet_url'];
                $auto_sync = (int)$tblStyles['table']['auto_sync'];
                if($auto_sync) {
                    if ($this->updateTableFromSpreadsheet($table->id, $spreadsheet_url)) {
                        $count++;
                    }
                }
            }
        }

        return $count; //number of table synced
    }

    function fetchSpreadsheet() {
        $id_table = Utilities::getInt('id', 'POST');
        $spreadsheet_url = Utilities::getInput('spreadsheet_url', 'POST', 'string');

        if ($id_table && $spreadsheet_url) {
            $update = $this->updateTableFromSpreadsheet($id_table, $spreadsheet_url);
            if (!$update) {
                $this->exit_status(__('error while saving table', 'wptm'));
            } else {
                $this->exit_status(true);
            }
        }

        $this->exit_status(true);
    }

    function updateTableFromSpreadsheet($id_table, $spreadsheet_url) {
        $modelTable = $this->getModel('table');
        $tableContent = (array) $modelTable->getItem($id_table);
        $doUpdate = false;

        if (strpos($spreadsheet_url, 'docs.google.com/spreadsheet') !== false) {
            //convert to url export csv
            $url_arr = explode('/', $spreadsheet_url);
            array_pop($url_arr);
            $csv_url =  implode("/",$url_arr). "/pub?hl=en_US&single=true&output=csv";

            $url_query = parse_url($spreadsheet_url, PHP_URL_QUERY);
            if (!empty($url_query)) {
                parse_str($url_query, $url_query_params);
                if (isset($url_query_params['gid'])) {
                    $csv_url .= '&gid=' . $url_query_params['gid'];
                }
            }

            $csv_array = $this->getCsvDataFromUrl($csv_url);

            $tableContent['datas'] = json_encode($csv_array, JSON_UNESCAPED_UNICODE);

            $tblParams = json_decode($tableContent['params'], true);
            //Read Merged Cells info
            //$mergeSettings =  array();
            //$tableContent['params'] = array("mergeSetting" => json_encode($mergeSettings) ) ;
            $tableContent['params'] = $tblParams;
            $doUpdate = true;
        } else {
            //download file
            $file = $this->_downloadFile($spreadsheet_url);
            if ($file) {

                $folder_admin = dirname(WPTM_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'admin';
                require_once $folder_admin . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'phpexcel' . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'IOFactory.php';

                //only import data area
                $objPHPExcel = PHPExcel_IOFactory::load($file);
                $sheet = $objPHPExcel->getActiveSheet();
                $maxCell = $sheet->getHighestRowAndColumn();
                $datas = $sheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row'], "");

                $tableContent['datas'] = json_encode($datas, JSON_UNESCAPED_UNICODE);

                //Read Merged Cells info
                $mergeSettings = array();
                $mergeRanges = $objPHPExcel->getActiveSheet()->getMergeCells();
                if (count($mergeRanges)) {
                    foreach ($mergeRanges as $mergeRange) {
                        list($tlCell, $rbCell) = explode(":", $mergeRange);

                        list($tl_cNb, $tl_rNb) = PHPExcel_Cell::coordinateFromString($tlCell);
                        list($br_cNb, $br_rNb) = PHPExcel_Cell::coordinateFromString($rbCell);
                        $tl_cNb = PHPExcel_Cell::columnIndexFromString($tl_cNb);
                        $br_cNb = PHPExcel_Cell::columnIndexFromString($br_cNb);

                        $mergeSetting = new stdClass();
                        $mergeSetting->row = $tl_rNb - 1;
                        $mergeSetting->col = $tl_cNb - 1;
                        $mergeSetting->rowspan = $br_rNb - $tl_rNb + 1;
                        $mergeSetting->colspan = $br_cNb - $tl_cNb + 1;
                        $mergeSettings[] = $mergeSetting;
                    }
                }

                $tableContent['params'] = array("mergeSetting" => json_encode($mergeSettings));
                $doUpdate = true;
                unlink($file);
            }
        }

        $updated = false;
        if ($doUpdate) {
            if ($modelTable->save($id_table, $tableContent)) {
                $updated = true;
            }
        }

        return $updated;
    }

    public function getCsvDataFromUrl($spreadsheet_url) {

        $csvFile = $this->_downloadFile($spreadsheet_url);
        $spreadsheet_data = array();
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $data = str_replace("\n","<br>", $data) ;
                $spreadsheet_data[] = $data;
            }
            fclose($handle);
            unlink($csvFile);
        }
        else {
            error_log("Problem reading csv");
        }

        return $spreadsheet_data;
    }

    public function _downloadFile($url) {

        //check file extension
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $newname = uniqid() . '.' . $ext;
        if (strpos($url, 'docs.google.com/spreadsheet') !== false) {
            $newname = uniqid() . '.csv' ;
        } else if ( !in_array($ext, $this->allowed_ext)) {
            $this->error_message = __('Wrong file extension', 'wptm');
            return false;
        }

        $upload_dir = wp_upload_dir();
        $targetPath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'wptm' . DIRECTORY_SEPARATOR;
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0777, true);
            $data = '<html><body bgcolor="#FFFFFF"></body></html>';
            $file = fopen($targetPath . 'index.html', 'w');
            fwrite($file, $data);
            fclose($file);
        }

        $targetFile = $targetPath . $newname;
        $data = $this->getDataFromUrl($url);
        $file = fopen($targetFile, 'w');
        fwrite($file, $data);
        fclose($file);

        return $targetFile;
    }

    public function getDataFromUrl($url) {
        $ch = curl_init();
        $timeout = 5;
        $agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_REFERER, site_url());
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new Exception($error);
        }
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] !== 404) {
            return $data;
        } else {
            return NULL;
        }
    }

    public function csvToArray($csv) {
        $arr = array();
        $lines = explode("\n", $csv);
        foreach ($lines as $row) {
            $row = str_replace('""','\\"',$row);
            $arr[] = str_getcsv($row, ",");
        }

        return $arr;
    }

}

?>