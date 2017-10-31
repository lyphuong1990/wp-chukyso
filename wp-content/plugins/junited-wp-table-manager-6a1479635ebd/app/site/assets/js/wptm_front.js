(function($) {
    function posRestable(id_table) {
        var tblWidth =  $("#wptmTbl"+id_table).width();
        var resTableWidth = $("#wptmtable" + id_table + " .restableOverflow").width();               
        var tblPos =  $("#wptmTbl" + id_table).offset();                                                 
        if(resTableWidth > tblWidth) {           
            $("#wptmtable" + id_table+ " .restableMenu").offset( {left: tblPos.left + tblWidth - 20} );
            $("#wptmtable" + id_table+ " .restableMenu").css("right", "auto" );    
        } else {
            $("#wptmtable" + id_table+ " .restableMenu").css("left", "auto" );
            $("#wptmtable" + id_table+ " .restableMenu").css("right", "0" );                      
        } 
    }
    
     $(document).ready(function(){
        $(".wptmresponsive.wptmtable table").each(function( index ) {
            var id_table = $(this).data('id'); 
            var hideCols= parseInt($(this).data('hidecols')); 
            if(hideCols) {
              
                 $("#wptmTbl" + id_table ).restable({  
                    type: "hideCols", 
                    priority: $(this).data('priority')
                 });               
                 posRestable(id_table) ;  

                $(window).on("resize", function() {     
                       posRestable(id_table) ;                  
                });
            }else {
                $("#wptmTbl" + id_table).restable();      
            }
            
        });
        
        $(".wptmtable .fxdHdrCol").each(function( index ) {

            if($(this).parent().hasClass('ft_scroller') ) { return true; }

            fixedRows = parseInt($(this).data('freeze-rows'));
            fixedCols =  parseInt($(this).data('freeze-cols'));

            if($(this).width() <= $(this).parent().width()) {
                fixedCols = 0;
            }
            tblHeight = $(this).height() + 20;
            confgiHeight = parseInt($(this).data('table-height'));
            if(!confgiHeight) confgiHeight = 500;
            if(tblHeight > confgiHeight) {
                tblHeight = confgiHeight;
            }
            tblSort = ( $(this).hasClass('sortable'))? true: false;
            $(this).fxdHdrCol({
                fixedCols: fixedCols,
                fixedRows: fixedRows,
                width:     "100%",
                height:    tblHeight,
                sort: tblSort
            }); 
        });
       
        $(".wptmtable .filterable").each(function( index ) {
                $(this).tablesorter({
                    theme: "bootstrap",
                    widthFixed: true,
                    headerTemplate: '{content} {icon}',
                    widgets: ["uitheme", "filter", "zebra"],
                });
                if(!$(this).hasClass('disablePager')) {
                    $(this).tablesorterPager({
                        container: $(".ts-pager"),
                        cssGoto: ".pagenum",
                        output: '{startRow} - {endRow} / {filteredRows} ({totalRows})'
                    });
                };
        });
        
        $(".wptmtable .enablePager").each(function( index ) {
           
             $(this).tablesorter({
                    theme: "bootstrap",
                    widthFixed: true,
                    headerTemplate: '{content} {icon}',
                    widgets: ["uitheme", "zebra"],
                })
               .tablesorterPager({
                        container: $(".ts-pager"),
                        cssGoto: ".pagenum",
                        size: $(this).data('pagesize'),
                        savePages: false,
                        output: '{startRow} - {endRow} / {filteredRows} ({totalRows})'
                    });
        });


         $(".wptm_tooltip").mouseenter(function() {  
            if($(this).parent().find(".wptm_tooltipcontent_show").length ==0) {              
                var curPos = $(this).position(); 
                $(this).parent().prepend('<span class="wptm_tooltipcontent_show" style="">' + $(this).find(".wptm_tooltipcontent").html() +'</span>'); 
                $curTT = $(this).parent().find(".wptm_tooltipcontent_show");
                curTT_left = ( curPos.left -  $curTT.width()/2 + $(this).width()/2); 
                $curTT.stop(true, true).css("margin-top", "-"+ ( $curTT.height()+ 15)+"px" ).css("left", curTT_left+"px" );
            };  
            $(this).parent().find(".wptm_tooltipcontent_show").fadeIn();
        });
        $(".wptm_tooltip").mouseleave(function() {                 
            $(this).siblings("span.wptm_tooltipcontent_show").fadeOut(); 
         });
     })
    
})( jQuery );