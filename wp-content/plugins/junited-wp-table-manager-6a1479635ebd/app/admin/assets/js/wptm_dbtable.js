/** 
 * Wptm
 * 
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 * @package Wptm
 * @copyright Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2014 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

var constructedTableData = {
	name: '',
	method: '',
	columnCount: 0,
	columns: []
};

jQuery(document).ready(function ($) {
    
    var availableTableColumns;
    $(document).on('change', '#wptm_mysql_tables', function (e) {   
        selected_tables = $(this).val();      
        constructedTableData.tables = selected_tables;
        jsonVar = {
            'tables': selected_tables
        };
        $.ajax({
            url: wptm_ajaxurl + "task=dbtable.changeTables",
            type: "POST",
            data: jsonVar
        }).done(function (data) {
            result = jQuery.parseJSON(data);            
            if (result.response === true) {
                availableTableColumns = result.datas.columns;

                $("#wptm_mysql_tables_columns").html('');
                for (i = 0; i < availableTableColumns.all_columns.length; i++) {
                    col = availableTableColumns.all_columns[i];
                    $("#wptm_mysql_tables_columns").append('<option value="' + col + '" >' + col + '</option>');
                }

                $('.wptm_define_mysql_relations div.mysqlRelationsContainer').html('');
                $(".wptm_define_mysql_relations").hide();
                if (selected_tables.length > 1) {
                    // Generate HTML block for relations constructor
                    for (var i in availableTableColumns.sorted_columns) {
                        var mysql_table_block = {table: i, columns: [], other_table_columns: []};
                        for (var j in availableTableColumns.sorted_columns) {
                            if (i == j) {
                                for (var k in availableTableColumns.sorted_columns[i]) {
                                    mysql_table_block.columns.push(availableTableColumns.sorted_columns[i][k].replace(i + '.', ''));
                                }
                                continue;
                            }
                            for (var k in availableTableColumns.sorted_columns[j]) {
                                mysql_table_block.other_table_columns.push(availableTableColumns.sorted_columns[j][k]);
                            }
                        }
                      
                        var mysqlRelationBlockTemplate = $("#wptm-template-mysqlRelationBlock").html();
                        var template = Handlebars.compile(mysqlRelationBlockTemplate);
                        var relationBlockHtml = template(mysql_table_block);

                        $('.wptm_define_mysql_relations div.mysqlRelationsContainer').append(relationBlockHtml);

                    }
                    $(".wptm_define_mysql_relations").show();
                }
            } else {
                bootbox.alert(result.response);
            }
        });

    });

      /**
     * Add the selected MySQL columns to the constructed table data
     */
    $(document).on('change', '#wptm_mysql_tables_columns', function (e) {    
        e.preventDefault();       
        constructedTableData.mysql_columns = $(this).val();
        constructedTableData.columnCount = constructedTableData.mysql_columns.length;
       
        $(".column_title").css('display','none');
        old_default_ordering = $("#wptm_mysql_default_ordering_column").val();       
        $("#wptm_mysql_default_ordering_column").empty();
       
        if(constructedTableData.columnCount) {
            for(i=0;i< constructedTableData.columnCount; i++ ) {
                column_id = constructedTableData.mysql_columns[i].replace(".","_");
                if($("#wptm_column_"+ column_id).length ==0 ) {
                    $(".columnsTitleContainer").append('<div class="wptm_row column_title" id="wptm_row_'+ column_id+'"><label>'+ constructedTableData.mysql_columns[i] +' custom title: </label><input type="text" name="" id="wptm_column_'+ column_id+'" class="" value=""  /></div>');
                }
                $("#wptm_row_"+ column_id).css('display','block');
                
                $("#wptm_mysql_default_ordering_column").append('<option value="'+constructedTableData.mysql_columns[i]+'" >'+constructedTableData.mysql_columns[i]+'</option>') ; 
            }
            
            $("#wptm_mysql_default_ordering_column").val(old_default_ordering);
        }
        
    });
    
    /**
     * Add a "WHERE" condition to the WP POSTS based table
     */
    $(document).on('click', '#wptm_mysql_add_where_condition', function (e) {
       
        e.preventDefault();

        var whereBlockTemplate = $("#whereConditionTemplate").html();
        var template = Handlebars.compile(whereBlockTemplate);

        var where_block = {post_type_columns: $("#wptm_mysql_tables_columns option").map(function () {
                return $(this).val();
            }).toArray()};
        var whereBlockHtml = template(where_block);
        $('.wptm_define_mysql_conditions div.mysqlConditionsContainer').append(whereBlockHtml);

    });

    /**
     * Delete a "WHERE" condition
     */
    $(document).on('click', 'button.deleteConditionPosts', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $(this).closest('div.post_where_blocks').remove();
    });

    /**
     * Add a grouping rule for MySQL based tables
     */
    $(document).on('click', '#wptm_mysql_add_grouping_rule', function (e) {    
        e.preventDefault();

        // Generate HTML block for the grouping rule constructor
        var grouping_rule_block = {post_type_columns: []};

        grouping_rule_block.post_type_columns = $('#wptm_mysql_tables_columns').val();

        var groupingRuleBlockTemplate = $("#groupingRuleTemplate").html();
        var template = Handlebars.compile(groupingRuleBlockTemplate);
        var groupingRuleHtml = template(grouping_rule_block);

        $('.wptm_define_mysql_grouping div.mysqlGroupingContainer').append(groupingRuleHtml);

    });
    /**
     * Delete a grouping rule
     */
    $(document).on('click', 'button.deleteGroupingRulePosts', function (e) {
        e.preventDefault();
        $(this).closest('div.post_grouping_rule_blocks').remove();
    });
    
    $(document).on('click', '#btn_preview', function (e) {    
        e.preventDefault();
        
        constructedTableData.join_rules = [];
        constructedTableData.where_conditions = [];
        constructedTableData.grouping_rules = [];
        /*custom title */
        constructedTableData.custom_titles = [];
        for(i=0; i<constructedTableData.mysql_columns.length; i++) {
            column_id = constructedTableData.mysql_columns[i].replace(".","_");
            if($("#wptm_column_"+ column_id).val() ) {
                constructedTableData.custom_titles.push( $("#wptm_column_"+ column_id).val() );
            }else {
                constructedTableData.custom_titles.push( constructedTableData.mysql_columns[i]);
            }            
        }
       
        constructedTableData.default_ordering = $("#wptm_mysql_default_ordering_column").val();
        constructedTableData.default_ordering_dir =   $("#wptm_mysql_default_ordering_dir").val();
        constructedTableData.enable_pagination = $("#wptm_mysql_table_pagination").val();
        constructedTableData.limit_rows = $("#wptm_mysql_number_of_rows").val();
        
        /**
         * Join rules
         */
        $('div.mysqlRelationsContainer div.mysql_table_blocks').each(function(){
            var join_rule = {};
            join_rule.initiator_table = $(this).find('select.relationInitiatorColumn').data('table');
            join_rule.initiator_column = $(this).find('select.relationInitiatorColumn').val();
            join_rule.connected_column = $(this).find('select.relationConnectedColumn').val();
            join_rule.type = $(this).find('input[type="checkbox"]').is(':checked') ? 'inner' : 'left';
            constructedTableData.join_rules.push( join_rule );
        });
        
        /**
         * Where block
         */
        $('div.mysqlConditionsContainer div.post_where_blocks').each(function(){
            var where_condition = {};
            where_condition.column = $(this).find('select.whereConditionColumn').val();
            where_condition.operator = $(this).find('select.whereOperator').val();
            where_condition.value = $(this).find('input[type="text"]').val();
            constructedTableData.where_conditions.push(where_condition);
        });
        
         /**
         * Grouping rules
         */
        $('div.mysqlGroupingContainer div.post_grouping_rule_blocks select').each(function(){
            constructedTableData.grouping_rules.push( $(this).val() ); 
        });
      
        $.ajax({
            url: wptm_ajaxurl + "task=dbtable.generateQueryAndPreviewdata",         
            data: {             
                table_data: constructedTableData
            },
            type: 'post',
            dataType: 'json',
            success: function(result){
              
               if(result.response) {
                 data = result.datas;
                 constructedTableData.query = data.query;
                 $('div.wptm_previewTable').html(data.preview);
                 $("#btn_tableCreate").prop('disabled',false);
                 $("#btn_tableUpdate").prop('disabled',false);
                }
             
            }
        })
    });
    
    $(document).on('click', '#btn_tableCreate', function (e) {           
         $.ajax({
            url: wptm_ajaxurl + "task=dbtable.createTable",         
            data: {             
                table_data: constructedTableData
            },
            type: 'post',
            dataType: 'json',
            success: function(result){
             
               if(result.response) {
                    window.location.href = 'admin.php?page=wptm&id_table='+ result.datas;                 
                }
             
            }
        })
    });
    
    $(document).on('click', '#btn_tableUpdate', function (e) {     
           /*custom title */
        constructedTableData.custom_titles = [];
        for(i=0; i<constructedTableData.mysql_columns.length; i++) {
            column_id = constructedTableData.mysql_columns[i].replace(".","_");
            if($("#wptm_column_"+ column_id).val() ) {
                constructedTableData.custom_titles.push( $("#wptm_column_"+ column_id).val() );
            }else {
                constructedTableData.custom_titles.push( constructedTableData.mysql_columns[i]);
            }            
        }
       
        constructedTableData.default_ordering = $("#wptm_mysql_default_ordering_column").val();
        constructedTableData.default_ordering_dir =   $("#wptm_mysql_default_ordering_dir").val();
        constructedTableData.enable_pagination = $("#wptm_mysql_table_pagination").val();
        constructedTableData.limit_rows = $("#wptm_mysql_number_of_rows").val();
        
         $.ajax({
            url: wptm_ajaxurl + "task=dbtable.updateTable",         
            data: {                
                table_data: constructedTableData
            },
            type: 'post',
            dataType: 'json',
            success: function(result){
              
               if(result.response) {
                    $('li.wptmtable.active a:not(".newTable,.trash,.edit,.copy")').click();                               
                }             
            }
        })
    });
    
})