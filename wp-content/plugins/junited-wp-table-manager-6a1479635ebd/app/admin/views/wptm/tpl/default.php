<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0W
 */

use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Model;

// No direct access.
defined( 'ABSPATH' ) || die();

wp_localize_script('wptm-main','wptmText',array(                
                'Delete'=>__('Delete','wptm'),
                'Edit'=>__('Edit','wptm'),             
                'Cancel'=>__('Cancel','wptm'),
                'Ok'=>__('Ok','wptm'),
                'Confirm'=>__('Confirm','wptm'),
                'Save'=>__('Save','wptm'),                 
                'GOT_IT'=>__('Got it!','wptm'),
                'LAYOUT_WPTM_SELECT_ONE' => __('Please select a table a create a new one', 'wptm') ,
                'VIEW_WPTM_TABLE_ADD' =>  __('Add new table', 'wptm') ,
                'JS_WANT_DELETE' => __('Do you really want to delete ', 'wptm') ,
                'CHANGE_INVALID_CHART_DATA' => __('Invalid chart data', 'wptm') ,    
                'CHART_INVALID_DATA'=>__('Invalid data, please make a new data range selection with at least one row or column with only numeric data, thanks!','wptm'),
                'CHOOSE_EXCEL_FIE_TYPE' => __('Please choose a file with type of xls or xlsx.', 'wptm') ,  
                'WARNING_CHANGE_THEME' => __('Warning - all data and styles will be removed & replaced on theme switch', 'wptm') ,  
                'Your browser does not support HTML5 file uploads'=>__('Your browser does not support HTML5 file uploads','wptm'),
                'Too many files'=>__('Too many files','wptm'),
                'is too large'=>__('is too large','wptm'),
                'Only images are allowed'=>__('Only images are allowed','wptm'),
                'Do you want to delete &quot;'=>__('Do you want to delete &quot;','wptm'),
                'Select files'=>__('Select files','wptm'),
                'Image parameters'=>__('Image parameters','wptm'),    
                'notice_msg_table_syncable'=>__('This spreadsheet is currently sync with an external file, you may lose content in case of modification','wptm'),      
                'notice_msg_table_database'=>__('Table data are from database, only the 50 first rows are displayed for performance reason.','wptm'),      
                
        ));
wp_localize_script('wptm-bootbox','wptmCmd',array(                
                'Delete'=>__('Delete','wptm'),
                'Edit'=>__('Edit','wptm'),             
                'CANCEL'=>__('Cancel','wptm'),
                'OK'=>__('Ok','wptm'),
                'CONFIRM'=>__('Confirm','wptm'),
                'Save'=>__('Save','wptm'),                                       
        ));

if (isset($_GET['noheader'])){
    global $hook_suffix;
    _wp_admin_html_begin();
    do_action( 'admin_enqueue_scripts', $hook_suffix );
    do_action( "admin_print_scripts-$hook_suffix" );
    do_action( 'admin_print_scripts' );
}

$alone = '';

$editor_id = 'wptmditor';
$editor_args = array(
    'tabfocus_elements' => 'content-html,save-post',
    'quicktags' => true,
    'media_buttons' => false,
    'editor_height' => 400,
    'tinymce' => array(
		'resize' => true,             
		'wp_autoresize_on' => true,
		'add_unload_trigger' => false                
    )
);
wp_editor( '<p></p><p></p>', $editor_id,$editor_args );

   
$editor_args1 = $editor_args;
// $editor_args1['editor_height'] = '300' ;                        
$editor_args1['quicktags'] = false;
$editor_args1['tinymce'] = array(
    'setup' => 'function (ed) {                               
                               ed.on("keyup", function (e) {
                                  // ed.save();                                   
                                   //wptm_tooltipChange();
                                
                                });
                                ed.on("change", function(e) {
                                   // ed.save();
                                    //wptm_tooltipChange();                                   
                                });
                            }',
);
wp_editor('', 'wptm_tooltip', $editor_args1);
?>
<style>
 #wp-wptmditor-wrap, #wp-wptm_tooltip-wrap { display: none} 
</style>    
<script type="text/javascript">
    ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    wptm_ajaxurl = "<?php echo Factory::getApplication('wptm')->getAjaxUrl(); ?>";
    wptm_dir = "<?php echo Factory::getApplication('wptm')->getBaseUrl(); ?>";
<?php if(Utilities::getInput('caninsert','GET','bool')): ?>
    gcaninsert=true;
    <?php $alone = 'wptmalone wp-core-ui '; ?>
<?php else: ?>
    gcaninsert=false;
<?php endif; ?>
   
    var Wptm = {};
    if(typeof(addLoadEvent)==='undefined'){addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};};
</script>
<style>
    <?php if(Utilities::getInput('caninsert','GET','bool')): ?>
    html.wp-toolbar {padding-top: 0 !important}
    <?php endif; ?>
</style>
<div id="mybootstrap" class="<?php echo $alone; ?>">   
    
    <div id="mycategories">
        <div class="categories-toggle" id="cats-toggle"><span class="dashicons-before dashicons-arrow-left-alt"></span></div>
        <a id="newcategory" class="button button-primary button-big tooltip" title="<?php _e('','wptm'); ?>" href="">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php _e('New category','wptm'); ?></a>
        <div class="nested dd">
            <ol id="categorieslist" class="dd-list nav bs-docs-sidenav2 ">
                <?php if(!empty($this->categories)){    
                    $content = '';
                    $previouslevel = 1;
                    for ($index = 0; $index < count($this->categories); $index++) {
                        if($index+1!=count($this->categories)){
                            $nextlevel = $this->categories[$index+1]->level;
                        }else{
                            $nextlevel = 0;
                        }
                        $content .= openItem($this->categories[$index],$index);
                        $content .= '<ul class="wptm-tables-list">';
                        if($this->categories[$index]->id == $this->dbtable_cat) {
                                $tableType = 'mysql';
                        }else {
                                $tableType = '';
                        }
                        if(isset($this->tables[$this->categories[$index]->id])){                                              
                            foreach ($this->tables[$this->categories[$index]->id] as $table) {                                
                                $content .= '<li class="wptmtable" data-id-table="'.$table->id.'" data-table-type="'.$tableType.'">';
                                $content .= '<a href="#"><i class="icon-database"></i> <span class="title">'.$table->title.'</span></a>';				
				$content .= ' <a class="edit"><i class="icon-edit"></i></a>';								
				$content .= ' <a class="copy"><i class="icon-copy"></i></a>';							
				$content .= ' <a class="trash"><i class="icon-trash"></i></a>';				
                                $content .= '</li>';
                            }
                        }
                        if($tableType != 'mysql') {
                            $content .= '<li><a class="newTable" href="#"><i class="dashicons dashicons-plus-alt"></i> '. __('New table','wptm').'</a></li>';
                        }
                        $content .= '</ul>';
                        
                        if($nextlevel>$this->categories[$index]->level){
                            $content .= openlist($this->categories[$index]);
                        }elseif($nextlevel==$this->categories[$index]->level){
                            $content .= closeItem($this->categories[$index]);
                        }else{
                            $c = '';
                            $c .= closeItem($this->categories[$index]);
                            $c .= closeList($this->categories[$index]);
                            $content .= str_repeat($c,$this->categories[$index]->level-$nextlevel);
                        }
                        $previouslevel = $this->categories[$index]->level;                    
                    }
                }
                if (!isset($content))
                {
                    $content ='';
                }
                echo $content;
                ?>
            </ol>
            <input type="hidden" id="categoryToken" name="" /> 
        </div>
    </div>
    
    <div id="rightcol" class="">
        <?php if(Utilities::getInput('caninsert', 'GET', 'bool')): ?>            
            <a id="inserttable" class="button button-primary button-big" href="javascript:void(0)" onclick="if (window.parent) insertTable();"><?php _e('Insert this table','wptm'); ?></a>            
        <?php endif; ?>
         <?php if(isset($this->params['enable_autosave']) && $this->params['enable_autosave'] == '0'): ?>
                    <div class="control-group">
                        <label id="jform_saveTable-lbl">
                              <a id="saveTable" class="button button-primary button-big" title="<?php _e('Save modifications','wptm'); ?>" ><?php _e('Save modifications','wptm');?></a> 
                        </label>
                    </div>
                    <?php endif; ?>
                    
        <div>
            <ul class="nav nav-tabs" id="configTable">
              <li class="referTable active"><a data-toggle="tab" href="#table"><?php _e('Table','wptm'); ?></a></li>
              <li class="referCell"><a data-toggle="tab" href="#cell"><?php _e('Format','wptm');  ?></a></li>
              <li class="tableMore"><a data-toggle="tab" href="#css"><?php _e('More','wptm');   ?></a></li>
            </ul>
            <div class="tab-content" id="tableTabContent">
                <div id="table" class="tab-pane active">
                     <div class="control-group">
                        <div class="table-styles">
                            <ul>
                            <?php foreach ($this->styles as $style): ?>
                                <li><a href="#" data-id="<?php echo $style->id; ?>"><img src="<?php echo WP_TABLE_MANAGER_PLUGIN_URL.'app/admin/assets/images/styles/'.$style->image; ?>"/></a></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <hr/>
                    <div>
                        <div class="control-group">
                            <div class="control-label">
                                <label id="jform_alternate_row_even_color-lbl" for="jform_alternate_row_even_color">
                                    <?php  _e('Two frame line color 1','wptm'); ?> :
                                </label>
                            </div>
                            <div class="controls">
                                <input class="minicolors minicolors-input observeChanges"  data-position="left" data-control="hue" type="text" name="jform[alternate_row_even_color]" id="jform_alternate_row_even_color" value="" size="7" />
                            </div>
                        </div>    
                        <div class="control-group">
                            <div class="control-label">
                                <label id="jform_alternate_row_odd_color-lbl" for="jform_alternate_row_odd_color">
                                    <?php  _e('Two frame line color 2','wptm'); ?> :
                                </label>
                            </div>
                            <div class="controls">
                                <input class="minicolors minicolors-input observeChanges"  data-position="left" data-control="hue" type="text" name="jform[alternate_row_odd_color]" id="jform_alternate_row_odd_color" value="" size="7" />
                            </div>
                        </div>    
                        <hr/>
                        <div class="control-group">
                            <div class="control-label">
                                <label id="jform_use_sortable-lbl" for="jform_use_sortable">
                                    <?php  _e('Use sortable table','wptm'); ?> :                                 
                                </label>
                            </div>
                            <div class="controls">
                                <select class="chzn-select observeChanges" name="jform[jform_use_sortable]" id="jform_use_sortable">
                                    <option value="0"><?php _e('No','wptm') ; ?></option>
                                    <option value="1"><?php _e('Yes','wptm'); ?></option>
                                </select>
                            </div>
                        </div>    
                        <div class="control-group">
                            <div class="control-label">
                                <label id="jform_table_align-lbl" for="jform_table_align">
                                    <?php _e('Align table','wptm') ; ?> :
                                </label>
                            </div>
                            <div class="controls">
                                <select class="chzn-select observeChanges" name="jform[jform_table_align]" id="jform_table_align">
                                    <option value="center"><?php _e('Center','wptm')  ;?></option>
                                    <option value="right"><?php _e('Right','wptm') ;?></option>
                                    <option value="left"><?php _e('Left','wptm') ;?></option>
                                    <option value="none"><?php _e('None','wptm') ;?></option>
                                </select>
                            </div>
                        </div>    
                
                        <div class="control-group">
                           <div class="control-label">
                               <label id="jform_responsive_type-lbl" for="jform_responsive_type">
                                   <?php _e('Responsive Type','wptm'); ?> :
                               </label>
                           </div>
                           <div class="controls">
                               <select class="chzn-select observeChanges" name="jform[jform_responsive_type]" id="jform_responsive_type">
                                   <option value="scroll"><?php _e('Scrolling','wptm');?></option>
                                   <option value="hideCols"><?php _e('Hiding Cols','wptm');?></option>                         
                               </select>
                           </div>
                       </div>  
                        
                        <div id="freeze_options">
                            <div class="control-group">
                               <div class="control-label">
                                   <label id="jform_freeze_row-lbl" for="jform_freeze_row">                                              
                                       <?php _e('Freeze first ','wptm'); ?>                       
                                       <select class="chzn-select observeChanges" name="freeze_row" id="jform_freeze_row" style="width:auto">
                                           <?php for($i=0;$i<6;$i++) { ?>
                                            <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                           <?php } ?>                                                            
                                        </select>
                                       <?php _e('rows','wptm'); ?>                                    
                                   </label>
                               </div>                         
                            </div>  
                            
                            <div class="control-group" id="table_height_container">
                               <div class="control-label">
                                    <label id="jform_table_height-lbl" for="jform_table_height">
                                        <?php _e('Table height','wptm');?> 
                                        <div class="controls inline">
                                            <input class="observeChanges input-mini" type="text" name="jform[table_height]" id="jform_table_height" value="" size="7" />
                                        </div>
                                        <?php _e('px','wptm'); ?>    
                                    </label>
                                </div>
                                
                            </div>
                    
                            <div class="control-group">
                                     <div class="control-label">
                                         <label id="jform_freeze_col-lbl" for="jform_freeze_col">                                         
                                             <?php _e('Freeze first ','wptm'); ?>    
                                               <select class="chzn-select observeChanges" name="freeze_col" id="jform_freeze_col" style="width:auto">
                                                <?php for($i=0;$i<6;$i++) { ?>
                                                 <option value="<?php echo $i;?>"><?php echo $i;?></option>   
                                                <?php } ?>                                                            
                                             </select>
                                            <?php _e('cols','wptm'); ?>       
                                         </label>
                                     </div>
                                
                            </div>  
                        </div>
                        
                        <div class="control-group" style="margin-bottom: 10px">
                            <div class="control-label">
                                <label id="jform_enable_filters-lbl" for="jform_enable_filters">
                                    <?php  _e('Enable filters','wptm'); ?> :                                 
                                </label>
                            </div>
                            <div class="controls">
                                <select class="chzn-select observeChanges" name="jform[jform_enable_filters]" id="jform_enable_filters">
                                    <option value="0"><?php _e('No','wptm') ; ?></option>
                                    <option value="1"><?php _e('Yes','wptm'); ?></option>
                                </select>
                            </div>
                        </div>   
                        
                        <div style="display:none" class="dbtable_params">
                            <div class="control-group" style="margin-bottom: 10px">
                                <div class="control-label">
                                    <label id="jform_enable_pagination-lbl" for="jform_enable_pagination">
                                        <?php  _e('Enable Pagination','wptm'); ?> :                                 
                                    </label>
                                </div>
                                <div class="controls">
                                    <select class="chzn-select observeChanges" name="jform[enable_pagination]" id="jform_enable_pagination">                                    
                                        <option value="1"><?php _e('Yes','wptm'); ?></option>
                                        <option value="0"><?php _e('No','wptm') ; ?></option>
                                    </select>
                                </div>
                            </div>   
                        
                       
                           <div class="control-group" style="margin-bottom: 10px">
                                <div class="control-label">
                                    <label id="jform_limit_rows-lbl" for="jform_limit_rows">
                                        <?php  _e('Number rows per page','wptm'); ?> :                                 
                                    </label>
                                </div>
                                <div class="controls">
                                    <select class="chzn-select observeChanges" name="jform[limit_rows]" id="jform_limit_rows">
                                        <option value="0"><?php _e('Show All','wptm') ; ?></option>
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="40">40</option>                                       
                                    </select>
                                </div>
                            </div>   
                        </div>
                        
                        <div class="control-group spreadsheet_sync" >
                            <div class="control-label">
                                <label id="spreadsheet_url-lbl" for="jform_spreadsheet_url">
                                    <?php  _e('Spreadsheet link','wptm'); ?> :                                 
                                </label>
                            </div>
                            <div class="controls">
                                <input type="text" class="observeChanges" name="jform[jform_spreadsheet_url]" id="jform_spreadsheet_url" value="" />
                                <a class="button button-primary" id="fetch_spreadsheet" href="" ><?php _e('Fecth data','wptm'); ?></a>
                                <a href="index.php?page=wptm-foldertree&TB_iframe=true&width=600&height=550"  class="thickbox button button-primary"><?php _e('Browse','wptm'); ?></a>        
                            </div>
                        </div>
                        
                        <div class="control-group spreadsheet_sync" >
                            <div class="control-label">
                                <label id="auto_sync-lbl" for="jform_auto_sync">
                                    <?php  _e('Auto Sync','wptm'); ?> :                                 
                                </label>
                            </div>
                            <div class="controls">
                                <select name="auto_sync" id="jform_auto_sync" class="chzn-select observeChanges">
                                    <option value="1" ><?php  _e('Yes','wptm'); ?></option>
                                    <option value="0"><?php  _e('No','wptm'); ?></option>                                   
                                </select>       
                            </div>
                        </div>
                        
                        <div class="control-group" style="margin-bottom: 50px"></div>
                    </div>                
                </div>
                
           <!-- Cell  -->
                <div id="cell" class="tab-pane ">
                   
                    <div class="control-group">
                    <div class="control-label">
                        <label id="jform_cell_type-lbl" for="jform_cell_type">
                            <?php _e('Cell type','wptm');?> :
                        </label>
                    </div>
                    <div class="controls">
                        <select class="chzn-select observeChanges" name="jform[jform_cell_type]" id="jform_cell_type">
                            <option value=""><?php _e('Default','wptm');?></option>
                            <option value="html"><?php _e('Html','wptm');?></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_cell_background_color-lbl" for="jform_cell_background_color">
                            <?php _e('Cell background color','wptm');?> :
                        </label>
                    </div>
                    <div class="controls">
                        <input class="minicolors minicolors-input observeChanges"  data-position="left" data-control="hue" type="text" name="jform[jform_cell_background_color]" id="jform_cell_background_color" value="" size="7" />
                    </div>
                </div>
                <hr/>
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_cell_border_type-lbl" for="jform_cell_border_type">
                            <?php _e('Borders','wptm');?> :
                        </label>
                    </div>
                    <div class="clr"></div>
                    <div class="controls">
                        <div>
                            <select class="chzn-select" name="jform[jform_cell_border_type]" id="jform_cell_border_type">
                                <option value="solid"><?php _e('Solid','wptm');?></option>
                                <option value="dashed"><?php _e('Dashed','wptm');?></option>
                                <option value="dotted"><?php _e('Dotted','wptm');?></option>
                                <option value="none"><?php _e('No border','wptm');?></option>
                            </select>

			    <div class="form-inline">
				<div class="input-append">
				    <input type="text" name="jform[jform_cell_border_width]" id="jform_cell_border_width" value="1"/>
				    <button class="btn" type="button" id="cell_border_width_incr">+</button>
				    <button class="btn" type="button" id="cell_border_width_decr">-</button>
				</div>				
			    </div>
                            <input class="minicolors minicolors-input observeChanges"  data-position="left" data-control="hue" type="text" name="jform[jform_cell_border_color]" id="jform_cell_border_color" value="#CCCCCC" size="7" />
                        </div>
                        <div class="aglobuttons">
                            <button class="btn observeChanges" name="jform[jform_cell_border_top]" type="button"><span class="sprite sprite_border_top"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_right]" type="button"><span class="sprite sprite_border_right"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_bottom]" type="button"><span class="sprite sprite_border_bottom"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_left]" type="button"><span class="sprite sprite_border_left"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_all]" type="button"><span class="sprite sprite_border_all"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_inside]" type="button"><span class="sprite sprite_border_inside"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_outline]" type="button"><span class="sprite sprite_border_outline"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_horizontal]" type="button"><span class="sprite sprite_border_horizontal"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_vertical]" type="button"><span class="sprite sprite_border_vertical"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_border_remove]" type="button"><span class="sprite sprite_border_remove"></span></button>
                        </div>                        
                    </div>
                </div>    
		<hr/>
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_cell_font_family-lbl" for="jform_cell_font_family">
			    <?php _e('Font','wptm');?> :
                        </label>
                    </div>
                    <div class="controls">

                        <select class="chzn-select observeChanges" name="jform[jform_cell_font_family]" id="jform_cell_font_family">
                            <option value="Arial">Arial</option>
                            <option value="Arial Black">Arial Black</option>
                            <option value="Comic Sans MS">Comic Sans MS</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Impact">Impact</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Trebuchet MS">Trebuchet MS</option>
                            <option value="Verdana">Verdana</option>
                        </select>
                        
			<div class="form-inline">
			    <div class="input-append">
				<input class="observeChanges"  type="text" name="jform[jform_cell_font_size]" id="jform_cell_font_size" value="13"/>
				<button class="btn" type="button" id="cell_font_size_incr">+</button>
				<button class="btn" type="button" id="cell_font_size_decr">-</button>
			    </div>			    
                        </div>
                        <input class="minicolors minicolors-input observeChanges"  data-position="left" data-control="hue" type="text" name="jform[jform_cell_font_color]" id="jform_cell_font_color" value="#000000" size="7" />
                        <div class="aglobuttons">
                            <button class="btn observeChanges" name="jform[jform_cell_font_bold]" type="button"><span class="sprite sprite_text_bold"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_font_underline]" type="button"><span class="sprite sprite_text_underline"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_font_italic]" type="button"><span class="sprite sprite_text_italic"></span></button>
			    <br/>
                            <button class="btn observeChanges" name="jform[jform_cell_align_left]" type="button"><span class="sprite sprite_text_align_left"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_align_center]" type="button"><span class="sprite sprite_text_align_center"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_align_right]" type="button"><span class="sprite sprite_text_align_right"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_align_justify]" type="button"><span class="sprite sprite_text_align_justify"></span></button>
                            <br/>
			    <button class="btn observeChanges" name="jform[jform_cell_vertical_align_top]" type="button"><span class="sprite sprite_vertical_align_top"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_vertical_align_middle]" type="button"><span class="sprite sprite_vertical_align_middle"></span></button>
                            <button class="btn observeChanges" name="jform[jform_cell_vertical_align_bottom]" type="button"><span class="sprite sprite_vertical_align_bottom"></span></button>
                        </div>                        
                    </div>
                </div>
		<hr/>
		
		<div class="control-group">
		    <div class="control-label">
                        <label id="jform_row_height-lbl" for="jform_row_height">
                            <?php _e('Row height','wptm');?> :
                        </label>
                    </div>
                    <div class="controls">
                        <input class="observeChanges input-mini" type="text" name="jform[jform_row_height]" id="jform_row_height" value="" size="7" />
                    </div>
		    <div class="control-label">
                        <label id="jform_col_width-lbl" for="jform_col_width">
                            <?php _e('Column width','wptm');?> :
                        </label>
                    </div>
                    <div class="controls">
                        <input class="observeChanges input-mini" type="text" name="jform[jform_col_width]" id="jform_col_width" value="" size="7" />
                    </div>
		</div>   
                
                <?php if(isset($this->params['enable_tooltip']) && $this->params['enable_tooltip'] == '1'): ?>
                <div class="control-group">                    
			<label id="jform_tooltip_content-lbl" for="jform_tooltip_content">
                              <a id="editToolTip" class="button button-primary button-big" title="<?php _e('Edit','wptm'); ?>" href="#wptm_editToolTip"><?php _e('Edit Tooltip','wptm');?></a> 
                        </label>
                        
                        <div id="wptm_editToolTip" style="display:none">
                            <div id="tooltip_editor">
                                <textarea id="tooltip_content" name="tooltip_content" class="observeChanges"></textarea>
                                <a id="saveToolTipbtn" class="button button-primary button-large" title="<?php _e('Save','wptm'); ?>" href="javascript:void(0)"><?php _e('Save','wptm');?></a>
                                <a id="cancelToolTipbtn" class="button button-large" title="<?php _e('Cancel','wptm'); ?>" href="javascript:void(0)"><?php _e('Cancel','wptm');?></a>
                            </div>
                        </div>                    
                    
                        <div class="control-label">
                          <label id="jform_tooltip_width-lbl" for="jform_tooltip_width">
                              <?php _e('Tooltip width (in px)','wptm');?> :
                          </label>
                        </div>
                        <div class="controls">
                            <input class="observeChanges input-mini" type="text" name="jform[jform_tooltip_width]" id="jform_tooltip_width" value="" size="7" />
                        </div>                   
		 </div>	
                 <?php endif ?>	
                </div>
           <!-- More tab -->
                <div id="css" class="tab-pane ">
                    
		<div class="control-group spreadsheet_sync">
				<?php if($this->params['enable_import_excel'] == '1'): ?>
				  <div class="progress progress-striped active" role="progressbar" style="display: none;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
					<div class="bar progress-bar-success data-dz-uploadprogress" style="width:0%;" data-dz-uploadprogress></div>
				  </div>
                    <div class="controls">
                         <div class="control-label">
                            <label id="import_style-lbl" for="import_style">                            
                                <?php _e('Import/Export xls','wptm'); ?> :
                            </label>
                        </div>
                        <div class="controls" style="margin-bottom: 10px;">
                            <select class="chzn-select" name="jform[import_style]" id="jform_import_style">
                                <option value="1"><?php  _e('Data + styles','wptm') ;?></option>
                                <option value=""><?php _e('Data only','wptm') ;?></option>
                            </select>			
                        </div>
                    </div>
                    
                    <div class="controls pull-left">
                    	<form action="admin-ajax.php?action=wptm&task=excel.import" id="proc-excel" class="dropzone pull-left" accept-charset="utf-8">
				<a href="javascript:void(0);" class="dz-btn nephritis-flat-button"><?php _e('Import Excel','wptm') ?></a><br/>                              
			</form>
                    </div>
                    <div class="controls pull-right" style="margin-right:10px">
                    	<a href="javascript:void(0);" class="carrot-flat-button" id="export-excel" data-format-excel="<?php echo $this->params['export_excel_format'] ?>">
                            <?php _e('Export Excel','wptm') ?></a><br/>                       
                    </div>
				<?php endif ?>	
		 </div>	
                 
                <div class="control-group" style="clear:left;">
                    <div class="control-label">
                        <label id="jform_responsive_col-lbl" for="jform_shortcode">                            
                            <?php _e('Shortcode','wptm'); ?> :
                        </label>
                    </div>
                    <div class="controls">
                        <p><?php _e('Table','wptm'); ?> <input readonly="readonly" id="shortcode_table" value="" type="text"> </p>
                        <div id="shortcode_charts"></div>
                    </div>
                </div>   
                    
                 <div class="control-group" style="clear:left;">
                    <div class="control-label">
                        <label id="jform_responsive_col-lbl" for="jform_responsive_col">
                            <?php _e('Column','wptm'); ?> :
                        </label>
                    </div>
                    <div class="controls">
			<select class="chzn-select observeChangesCol" name="jform[jform_responsive_col]" id="jform_responsive_col">                                                 
                        </select>
                    </div>
                </div>   
                    
                 <div class="control-group" style="clear:left;">
                    <div class="control-label">
                        <label id="jform_responsive_priority-lbl" for="jform_responsive_priority">
                            <?php _e('Responsive Priority','wptm'); ?> :
                        </label>
                    </div>
                    <div class="controls">
			<select class="chzn-select observeChanges" name="jform[jform_responsive_priority]" id="jform_responsive_priority">                                                  
                        </select>
                    </div>
                </div>   
                                                                              
               <div class="control-group">
                    <div class="control-label" >
                        <label id="jform_cell_padding-lbl" for="jform_cell_padding">
                            <?php _e('Paddings','wptm');?> :
                        </label>
                    </div>
                    <div class="controls">
                        <div style="height: 170px; width: 210px; border: 1px solid #AAA; margin: 0 auto; position: relative;">
                            <div style="height: 80px; width: 80px; border: 1px dashed #CCC; margin: 45px auto; text-align: center; line-height: 80px;font-size:12px;">Lorem Ipsum</div>
                            <div style="position: absolute; top: 70px; left: 3px;">
                                <input style="width: 30px;" type="text" name="jform[jform_cell_padding_left]" id="jform_cell_padding_left" class="observeChanges" value="0">px
                            </div>
                            <div style="position: absolute; top: 9px; left: 85px;">
                                <input style="width: 30px;" type="text" name="jform[jform_cell_padding_top]" id="jform_cell_padding_top" class="observeChanges" value="0">px
                            </div>
                            <div style="position: absolute; top: 70px; right: 3px;">
                                <input style="width: 30px;" type="text" name="jform[jform_cell_padding_right]" id="jform_cell_padding_right" class="observeChanges" value="0">px
                            </div>
                            <div style="position: absolute; bottom: 0px; left: 85px;">
                                <input style="width: 30px;" type="text" name="jform[jform_cell_padding_bottom]" id="jform_cell_padding_bottom" class="observeChanges" value="0">px
                            </div>
                        </div>
                    </div>
                </div>
		
		<div class="control-group">
                    <div class="control-label">
                        <label id="jform_cell_background_radius-lbl" for="jform_cell_background_radius">
                            <?php _e('Cell background radius','wptm');?> :
                        </label>
                    </div>
                    <div class="controls">
                        <div style="height: 170px; width: 210px; border: 1px solid #FFF; margin: 0 auto; position: relative;">
                            <div style="height: 80px; width: 80px; margin: 45px auto; text-align: center; line-height: 80px; border-radius: 5px; background-color: #CCC;font-size:12px;">Lorem Ipsum</div>
                            <div style="position: absolute; top: 15px; left: 15px;">
                                <input style="width: 30px;" type="text" name="jform[jform_cell_background_radius_left_top]" id="jform_cell_background_radius_left_top" class="observeChanges" value="0">px
                            </div>
                            <div style="position: absolute; top: 15px; right: 3px;">
                                <input style="width: 30px;" type="text" name="jform[jform_cell_background_radius_right_top]" id="jform_cell_background_radius_right_top" class="observeChanges" value="0">px
                            </div>
                            <div style="position: absolute; bottom: 15px; right: 3px;">
                                <input style="width: 30px;" type="text" name="jform[jform_cell_background_radius_right_bottom]" id="jform_cell_background_radius_right_bottom" class="observeChanges" value="0">px
                            </div>
                            <div style="position: absolute; bottom: 15px; left: 15px;">
                                <input style="width: 30px;" type="text" name="jform[jform_cell_background_radius_left_bottom]" id="jform_cell_background_radius_left_bottom" class="observeChanges" value="0">px
                            </div>
                        </div>
                    </div>
                </div>
		
		<div class="control-group">
                    <div class="control-label">
                        <label id="jform_css-lbl" for="jform_css">
                            <?php _e('Custom css for this table','wptm');?> :
                        </label>
                        <a id="customCssbtn" class="button button-primary button-big" title="<?php _e('Custom Css','wptm'); ?>" href="#wptm_customCSS"><?php _e('Edit css','wptm');?></a>                        
                    </div>
		</div>
                </div>                
            </div>
                       
        </div>
    </div>
    <div id="wptm_customCSS" style="display:none">
        <textarea rows="10" cols="50" style="width:400px" name="jform[jform_css]" id="jform_css"></textarea>        
        <a id="saveCssbtn" class="button button-primary button-large" title="<?php _e('Save','wptm'); ?>" href="javascript:void(0)"><?php _e('Save','wptm');?></a>
        <a id="cancelCssbtn" class="button button-large" title="<?php _e('Cancel','wptm'); ?>" href="javascript:void(0)"><?php _e('Cancel','wptm');?></a>
    </div>
<!-- Chart parameter -->    
    <div id="rightcol2" style="display: none">        
        <?php if(Utilities::getInput('caninsert', 'GET', 'bool')): ?>     
            <a id="insertChart" class="button button-primary button-big" href="javascript:void(0);" onclick="if (window.parent) {insertChart();}"><?php _e('Insert this chart','wptm') ; ?></a>
        <?php endif; ?>
            
        <div class="">

            <ul class="nav nav-tabs" id="configChart">
              <li class="active"><a data-toggle="tab" href="#chart"><?php _e('Chart','wptm') ; ?></a></li>            
            </ul>
         
            <div class="tab-content" id="chartTabContent">
              <div id="chart" class="tab-pane active">
                <div class="control-group">
                    <div class="chart-styles">
                        <ul>
                        <?php foreach ($this->chartTypes as $chartType): ?>
                            <li><a href="#" title="<?php echo $chartType->name;?>" data-id="<?php echo $chartType->id; ?>"><img alt="<?php echo $chartType->name;?>" src="<?php  echo WP_TABLE_MANAGER_PLUGIN_URL.'app/admin/assets/images/charts/'.$chartType->image; ?>"/></a></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
               <hr/>
               
               <div class="control-group">
                    <div class="control-label">
                        <label id="jform_dataSelected-lbl" for="jform_dataSelected">
                            <?php _e('Selected Range','wptm') ; ?> :  <span class="cellRangeLabel"></span>
                        </label>                        
                    </div>
                    <div class="controls">
                       
                    </div>
                   
                    <div class="control-label">
                        <label id="jform_dataUsing-lbl" for="jform_dataUsing">
                            <?php _e('Switch Row/Column','wptm') ; ?> :
                        </label>
                    </div>
                    <div class="controls">
                        <select class="chzn-select observeChanges2" name="jform[dataUsing]" id="jform_dataUsing">
                            <option value="row"><?php  _e('Row','wptm') ;?></option>
                            <option value="column"><?php _e('Column','wptm') ;?></option>
                        </select>
                    </div>
                   
                     <div class="control-label">
                        <label id="jform_useFirstRowAsLabels-lbl" for="jform_useFirstRowAsLabels">
                            <?php  _e('Use first row/column as labels','wptm'); ?> :
                        </label>
                    </div>
                    <div class="controls">                        
                        <select class="chzn-select observeChanges2" name="jform[useFirstRowAsLabels]" id="jform_useFirstRowAsLabels">
                            <option value="yes"><?php _e('Yes','wptm'); ?></option>
                            <option value="no"><?php  _e('No','wptm'); ?></option>
                        </select>
                    </div>                   
                </div>
                 
               <div class="control-group">
                   <div class="control-label">
                        <label id="jform_chart_width-lbl" for="jform_chart_width">
                            <?php _e('Chart width','wptm'); ?> :
                        </label>
                    </div>
                    <div class="controls">
                        <div class="form-inline">
                            <input class="observeChanges2 input-mini" type="text" name="jform[chart_width]" id="jform_chart_width" value="" size="7" />
                        </div>
                    </div>
                   
		    <div class="control-label">
                        <label id="jform_chart_height-lbl" for="jform_chart_height">
                            <?php  _e('Chart height','wptm'); ?> :
                        </label>
                    </div>
                    <div class="controls">
                        <div class="form-inline">
                            <input class="observeChanges2 input-mini" type="text" name="jform[chart_height]" id="jform_chart_height" value="" size="7" />
                        </div>
                    </div>
		 
		</div>
                
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_table_align-lbl" for="jform_chart_align">
                            <?php  _e('Align Chart','wptm'); ?> :
                        </label>
                    </div>
                    <div class="controls">
			<select class="chzn-select observeChanges2" name="jform[chart_align]" id="jform_chart_align">
                            <option value="center"><?php  _e('Center','wptm'); ?></option>
                            <option value="right"><?php  _e('Right','wptm'); ?></option>
                            <option value="left"><?php  _e('Left','wptm');?></option>
                            <option value="none"><?php  _e('None','wptm'); ?></option>
                        </select>
                    </div>
                </div> 
               <hr/>
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_table_align-lbl" for="jform_dataset_select">
                            <?php  _e('Dataset','wptm');  ?> :
                        </label>
                    </div>
                    <div class="controls">
			<select class="chzn-select observeChanges3" name="jform[dataset_select]" id="jform_dataset_select">                         
                        </select>
                    </div>
                </div> 
                
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_dataset_color-lbl" for="jform_dataset_color">
                            <?php  _e('Color','wptm'); ?> :
                        </label>
                    </div>
                    <div class="controls">
                        <input class="minicolors minicolors-input observeChanges2"  data-position="left" data-control="hue" type="text" name="jform[dataset_color]" id="jform_dataset_color" value="" size="7" />
                    </div>
                </div>    
               
                  <br/><br/>
            </div></div>
    </div></div>
    
    <div id="pwrapper">
        <div id="wpreview">
            <div id="preview">
                <span id="savedInfo" style="display:none;"><?php _e('All modifications were saved','wptm'); ?></span>
                <ul class="nav nav-tabs" id="mainTable">
                    <li class="active"><a data-toggle="tab" href="#dataTable"><?php _e('Table','wptm'); ?></a></li>
                    <li class=""><a class="btn_addGraph nephritis-flat-button"  href="#">
                            <i class="dashicons dashicons-plus-alt"></i>
                        <?php _e('Add a new chart','wptm');  ?></a></li>
                   
                </ul>
                <div id="mainTabContent" class="tab-content">
                    <div id="dataTable" class="tab-pane active">
                        <div>
                            <h3 id="tableTitle"></h3>
                            <div class="clearfix"></div>
                            <div id="tableContainer" style="overflow:scroll;"></div>
                      </div>
                    </div>
                </div>
                  
            </div>        
        </div>
        <input type="hidden" name="id_category" value="" />
    </div>
</div>

<script>
var wptm_isAdmin = <?php echo (int)current_user_can( 'manage_options' ); ?>;
jQuery(document).ready(function($) {
    var myOptions = {
       width: 220,
        // a callback to fire whenever the color changes to a valid color
       change: function(event, ui){          
           
           var hexcolor = $( this ).wpColorPicker( 'color' ); 
           $(event.target).val(hexcolor);
           $(event.target).trigger('change');
       }
   }
       
    $('.minicolors').wpColorPicker(myOptions);       
    
})

var wptmChangeWait;
function wptm_tooltipChange() {    
      clearTimeout(wptmChangeWait);
        wptmChangeWait = setTimeout(function() {            
            jQuery("#tooltip_content").trigger("change");            
        }, 1000);
}
var enable_autosave = true;
 <?php if(isset($this->params['enable_autosave']) && $this->params['enable_autosave'] == '0'): ?>
enable_autosave = false;
<?php endif;?>
    
 <?php
  $id_table = Utilities::getInt('id_table'); ?>  
  var idTable = <?php echo $id_table;?> ;  
</script>    

<?php
function openItem($category,$key){
    return '<li class="dd-item dd3-item '.($key?'':'active').'" data-id-category="'.$category->id.'">
        <div class="dd-handle dd3-handle"></div>
        <div class="dd-content dd3-content">
            <a class="edit"><i class="icon-edit"></i></a>
            <a class="trash"><i class="icon-trash"></i></a>
            <a href="" class="t">
                <span class="title">'.$category->title.'</span>
            </a>
        </div>';
}

function closeItem($category){
    return '</li>';
}

function itemContent($category){
    return '<div class="dd-handle dd3-handle"></div>
    <div class="dd-content dd3-content"
        <i class="icon-chevron-right"></i>
        <a class="edit"><i class="icon-edit"></i></a>
        <a href="" class="t">
            <span class="title">'.$category->title.'</span>
        </a>
    </div>';
}

function openlist($category){
    return '<ol class="dd-list">';
}

function closelist($category){
    return '</ol>';
}
?>