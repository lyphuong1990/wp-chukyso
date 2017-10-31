function wptm_frameload() {    
       jQuery("#wptm_loader").hide();
       jQuery("#wptmmodalframe").css('visibility',"visible");
       jQuery("#wptmmodalframe").show();
}
jQuery(document).ready(function($){
    $('.wptmlaunch').wptm_leanModal({ top : 20, beforeShow: function(){ $("#wptmmodal").css("height","90%"); $("#wptmmodalframe").css('visibility','hidden'); $("#wptmmodalframe").attr('src', $("#wptmmodalframe").attr('src')); $("#wptm_loader").show(); }  });
    $('body').append('<div id="wptmmodal"><img src="images/spinner-2x.gif" width="32" id="wptm_loader" /> <iframe id="wptmmodalframe" onload="wptm_frameload()"  width="100%" height="100%" marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto" src="admin.php?page=wptm&noheader=1&caninsert=1" /><button id="wptm-close-modal" onclick="jQuery(\'#lean_overlay\',window.parent.document).fadeOut(300);jQuery(\'#wptmmodal\',window.parent.document).fadeOut(300);" style="position: absolute; right: -23px;">x</button></div>');
   
    $('body').on("click", ".wptmlaunch", function (e) {
        $("#wptmmodal").css("height", "90%");
        $("#wptmmodalframe").css('visibility', 'hidden');
        $("#wptmmodalframe").attr('src', $("#wptmmodalframe").attr('src'));
        $("#wptm_loader").show();

        var modal_id = $(this).attr("href");
        //var modal_height=$(modal_id).outerHeight();
        var modal_width = $(modal_id).outerWidth();
        $("#lean_overlay").css({"display": "block", opacity: 0});
        $("#lean_overlay").fadeTo(200, 0.5);
        $(modal_id).css({"visibility": "visible", "display": "block", "text-align": "center", "position": "fixed", "opacity": 0, "z-index": 100102, "left": 50 + "%", "margin-left": -(modal_width / 2) + "px", "top": "20px"});
        $(modal_id).fadeTo(200, 1);
            
    });

   return false;
});
