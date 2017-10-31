<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */


use Joomunited\WPFramework\v1_0_2\Factory;

?>
<div class="wrap wptm-dbtable">
    <div id="icon-options-general" class="icon32"></div>
    <?php if(empty($this->id_table)) : ?>
    <h2><?php _e("Table Creation Wizard", 'wptm'); ?></h2>
    <?php endif ?>
    <div id="wptm-dbtable-wrap">
        <h3><?php _e('Please choose the MySQL data which will be used to create a table', 'wptm'); ?></h3>
        <table>
            <tr>
                <td style="width: 250px">
                    <label for="wptm_mysql_tables"><span><?php _e('Database tables selection', 'wptm'); ?></span></label>
                </td>
                <td>
                    <div class="uploader">

                        <select id="wptm_mysql_tables" multiple="true">
                            <?php foreach ($this->mysql_tables as $mysql_table) { ?>
                            <option value="<?php echo $mysql_table ?>" <?php if(in_array($mysql_table, $this->selected_tables)) { echo 'selected'; };?>><?php echo $mysql_table ?></option>
                            <?php } ?>
                        </select>

                    </div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <label for="wptm_mysql_tables_columns"><span><?php _e('Database columns selection', 'wptm'); ?></span></label>
                </td>
                <td>
                    <div class="uploader">

                        <select id="wptm_mysql_tables_columns" multiple="true">
                            <?php if(count($this->availableColumns)) {
                                  foreach ($this->availableColumns as $column) { ?>
                                    <option value="<?php echo $column ?>" <?php if(in_array($column, $this->selected_columns)) { echo 'selected'; };?>><?php echo $column ?></option>
                                  <?php }
                            }else { ?>
                                 <option value=""><?php _e('Please select the tables first', 'wptm'); ?></option>
                            <?php
                            } ?>
                                                    
                        </select>

                    </div>                   
                </td>
                <td style="width: 520px">
                     <div class="columnsTitleContainer">
                    <?php
                    if(count($this->selected_columns)) {
                        $i = 0;
                        foreach ($this->selected_columns as $column) { $column_id  = str_replace(".", "_", $column) ?>
                          <div class="wptm_row column_title" id="wptm_row_<?php echo $column_id;?>"><label><?php echo $column;?> custom title: </label><input type="text" name="" id="wptm_column_<?php echo $column_id;?>" class="" value="<?php echo $this->custom_titles[$i];?>"  /></div>                                  
                        <?php  $i++; }                       
                    }?>
                    </div>
                </td>
            </tr>
           
            <tr>
                <td>
                    <label for="wptm_mysql_default_ordering_column"><span><?php _e('Data default ordering', 'wptm'); ?></span></label>
                </td>
                <td>
                    <select id="wptm_mysql_default_ordering_column" >
                        <?php if(count($this->selected_columns)) {
                                  foreach ($this->selected_columns as $column) { ?>
                                    <option value="<?php echo $column ?>" <?php if($column == $this->default_ordering_column) { echo 'selected'; };?>><?php echo $column ?></option>
                                  <?php }
                        } ?>
                    </select>
                    <select id="wptm_mysql_default_ordering_dir" >
                        <option value="asc" <?php if('asc' == $this->default_ordering_dir) { echo 'selected'; };?>>Ascending</option>
                        <option value="desc" <?php if('desc' == $this->default_ordering_dir) { echo 'selected'; };?>>Descending</option>
                    </select>
                </td>
                <td>
                 
                </td>                
            </tr>
         <?php if(!$this->id_table) { ?>
            <tr>
                <td>
                    <label for="wptm_mysql_table_pagination"><span><?php _e('Activate pagination', 'wptm'); ?></span></label>
                </td>
                <td>
                    <select id="wptm_mysql_table_pagination" >
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </td>
                <td>
                   
                </td>                
            </tr>
            
            <tr>
                <td>
                    <label for="wptm_mysql_number_of_rows"><span><?php _e('Number of rows', 'wptm'); ?></span></label>
                </td>
                <td>
                    <select id="wptm_mysql_number_of_rows" >
                        <option value="10">10 rows</option>
                        <option value="20" selected>20 rows</option>
                        <option value="40">40 rows</option>
                        <option value="0">All rows</option>
                    </select>
                </td>
                <td>                   
                </td>                
            </tr>
        <?php } ?>    
            <tr class="wptm_handle_multiple_tables" style="display: none">
                <td>
                    <label for="wptm_table_relations"><span><?php _e('Choose how to handle relations between multiple tables', 'wptm'); ?></span></label>
                </td>
                <td>
                    <div class="uploader">
                        <label for="wptm_table_relations_join"><input type="radio" name="wptm_table_relations" id="wptm_table_relations_join" value="parent_child" /> <?php _e('Define relations (joining rules) between MySQL tables', 'wptm'); ?></label><br/>
                        <label for="wptm_table_relations_outer_join"><input type="radio" name="wptm_table_relations" id="wptm_table_relations_outer_join" value="union" /> <?php _e('Do not define relations between MySQL tables - do a full outer join', 'wptm'); ?></label><br/>
                    </div>
                </td>
                <td></td>
            </tr>
            
            <tr class="wptm_define_mysql_relations" style="<?php if(empty($this->join_rules)) { echo 'display: none';}?>">
                <td>
                    <label for="wptm_mysql_relations"><span><?php _e('Define the relations between tables', 'wptm'); ?></span></label>
                </td>
                <td colspan="2">
                    <div class="uploader mysqlRelationsContainer">
               <?php if(!empty($this->join_rules)) :
                  
                   foreach ($this->join_rules as $join_rule) { ?>
                       <div class="mysql_table_blocks">
                        <span class="relationInitiatorTable"><?php echo $join_rule->initiator_table;?>.</span>
                        <select class="relationInitiatorColumn" data-table="<?php echo $join_rule->initiator_table;?>">
                           <option value=""></option>
                           <?php foreach ($this->sorted_columns[$join_rule->initiator_table] as $key => $column) { 
                               $col = str_replace($join_rule->initiator_table.".","", $column);  ?>
                           <option value="<?php echo $col;?>"  <?php if($join_rule->initiator_column==$col){ echo 'selected';}?> >
                               <?php echo $col;?></option>
                           <?php } ?>
                        
                        </select> 
                         =
                        <select class="relationConnectedColumn" data-table="<?php echo $join_rule->initiator_table;?>">
                           <option value=""></option>
                           <?php foreach ($this->sorted_columns as $tbl => $columns) { 
                               if($tbl !=  $join_rule->initiator_table) {
                                   foreach ($this->sorted_columns[$tbl] as  $column) { ?>
                                       <option value="<?php echo $column;?>" <?php if($join_rule->connected_column==$column){ echo 'selected';}?>  ><?php echo $column;?></option>
                                   <?php
                                   }
                               }                                    
                            }?>
                                                
                        </select>
                        <input type="checkbox" <?php if($join_rule->type!='left') { echo 'checked';}?> title="<?php _e('Check to have an inner join, uncheck to have left join','wptm'); ?>" class="innerjoin" />
                       </div>
                <?php
                  }
               endif; ?>    
                        
                        
                    </div>
                </td>
             
            </tr>
            <tr class="wptm_define_mysql_conditions">
                <td>
                    <label for="wptm_mysql_conditions"><span><?php _e('Data display conditions', 'wptm'); ?></span></label>
                </td>
                <td colspan="2">
                    <div class="uploader mysqlConditionsContainer">
                    </div>
                    <button class="btn button-secondary" id="wptm_mysql_add_where_condition">+</button>
                </td>
               
            </tr>
            <tr class="wptm_define_mysql_grouping">
                <td>
                    <label for="wptm_posts_grouping"><span><?php _e('Data group rules', 'wptm'); ?></span></label>
                </td>
                <td colspan="2">
                    <div class="uploader mysqlGroupingContainer">
                    </div>
                    <button class="btn button-secondary" id="wptm_mysql_add_grouping_rule">+</button>
                </td>
              
            </tr>                                                                                        

        </table>
        <br/>
        <input type="button" id="btn_preview" class="button button-primary" value="<?php _e('Preview', 'wptm'); ?>" />
        <?php if($this->id_table) { ?>
             <input type="button" id="btn_tableUpdate" class="button button-primary" value="<?php _e('Update table', 'wptm'); ?>" />
        <?php 
        }else { ?>
            <input type="button" id="btn_tableCreate" disabled class="button button-primary" value="<?php _e('Create table', 'wptm'); ?>" />
        <?php }?>
                
         <div class="uploader wptm_previewTable">
                                                                                                            
         </div>
    </div>
</div>    

<script type="text/javascript">
    ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    wptm_ajaxurl = "<?php echo Factory::getApplication('wptm')->getAjaxUrl(); ?>";
    wptm_dir = "<?php echo Factory::getApplication('wptm')->getBaseUrl(); ?>";
 <?php if(!empty($this->selected_tables)) { ?>
     constructedTableData.id_table = <?php echo $this->id_table ;?>;
     constructedTableData.tables =<?php echo json_encode($this->selected_tables );?>;
     constructedTableData.enable_pagination = <?php echo (isset($this->params->enable_pagination)?$this->params->enable_pagination: 1) ;?>;
     constructedTableData.limit_rows = <?php echo (isset($this->params->limit_rows)?$this->params->limit_rows : 20)  ;?>;
    <?php    
    if(!empty($this->selected_columns)) { ?>
      constructedTableData.mysql_columns =<?php echo json_encode($this->selected_columns );?>;    
   <?php   
    }
 }?>
</script>

<script type="text/x-handlebars-template" id="wptm-template-mysqlRelationBlock">
     <div class="mysql_table_blocks">
     <span class="relationInitiatorTable">{{table}}.</span>
     <select class="relationInitiatorColumn" data-table="{{table}}">
        <option value=""></option>
        {{#each columns}}
        <option value="{{this}}">{{this}}</option>
        {{/each}}
     </select> 
      =
     <select class="relationConnectedColumn" data-table="{{table}}">
        <option value=""></option>
        {{#each other_table_columns}}
        <option value="{{this}}">{{this}}</option>
        {{/each}}
     </select>
     <input type="checkbox" title="<?php _e('Check to have an inner join, uncheck to have left join','wptm'); ?>" class="innerjoin" />
    </div>


</script>

<script type="text/x-handlebars-template" id="whereConditionTemplate" >
    <div class="post_where_blocks">
    <select class="whereConditionColumn">
        <option value=""></option>
        {{#each post_type_columns}}
        <option value="{{this}}">{{this}}</option>
        {{/each}}
     </select>
     <select class="whereOperator">
           <option value="eq">=</option>
           <option value="gt">&gt;</option>
           <option value="gtoreq">&gt;=</option>
           <option value="lt">&lt;</option>
           <option value="ltoreq">&lt;=</option>
           <option value="neq">&lt;&gt;</option>
           <option value="like">LIKE</option>
           <option value="plikep">%LIKE%</option>
           <option value="in">IN</option>
     </select>
     
    <input type="text" />
                
    <button class="button-secondary deleteConditionPosts" style="line-height: 26px; font-size: 26px"><span class="dashicons dashicons-no"></span></button>
    </div>
</script>


<script type="text/x-handlebars-template"  id="groupingRuleTemplate" >
    <div class="post_grouping_rule_blocks">
    <?php _e('Group by ', 'wptm'); ?>
        
    <select class="groupingRuleColumn">
        <option value=""></option>
        {{#each post_type_columns}}
        <option value="{{this}}">{{this}}</option>
        {{/each}}
     </select>
     
    <button class="button-secondary deleteGroupingRulePosts" style="line-height: 26px; font-size: 26px"><span class="dashicons dashicons-no"></span></button>
    </div>
</script>