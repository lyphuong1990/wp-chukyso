/*!
 * resTable
 * jQuery responsive Tables Plugin
 * author: Damien Barrère
 * company : Joomunited
 * version : 1.0.4
 * licence : MIT license
 */


;(function ( $, window, document, undefined ) {
    // Create the defaults once
    var pluginName = "restable",
        defaults = {
            type        :   'default',
            priority    :   {}, //{0:1,1:2,2:'persistent'} col0 with priority 0, col with priority 2 and col 2 always shown
            selectCol   :   true
        };
        
    var instancesCount = 0;

    // The actual plugin constructor
    function Plugin( element, options ) {
        this.element = element;

        // jQuery has an extend method that merges the
        this.options = $.extend( {}, defaults, options) ;

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype = {

        init: function() {
            this._checkWindowResize(); //to initialize vars            
            if(navigator.userAgent.match(/(Macintosh|iPhone|iPad|iPod)/i)){
                browserClass = 'resTableSafari'
            }else{
                browserClass = '';
            }
            $(this.element).wrap('<div class="restableOverflow restable'+this.options.type.capitalize()+' '+browserClass+'">');
            this.wrapper = $(this.element).parent();
            this.enable();
        },

        yourOtherFunction: function() {            
            $(this).css(arguments[0],arguments[1]);
        },
        
        enable : function(){
            switch (this.options.type){
                case 'hideCols': 
                    this._enableHideCols();
                    break;
                default:
                    this._enableDefault();
                    break;
            }            
        },
        
        /** 
         * Enable the default type
         * The table is wrapped and a scrollbar is shown
         */
        _enableDefault : function(){
            var checkO = function(element,wrapper){
                if(checkOverflow(element,wrapper)){
                    $(wrapper).addClass('restableOverflowShow');
                }else{
                    $(wrapper).removeClass('restableOverflowShow');
                }
            };
            var that = this;
            $( window ).resize(function() {                
                checkO(that.element,that.wrapper);
            });
            checkO(this.element,this.wrapper);
        },
        
        /**
        * Enable the show/hide cols type
        * Columns will be shown depending on their priority
        */
       _enableHideCols : function(){
           var priorities = [];
           $.each(this.options.priority,function(index,value){
                if(typeof(priorities[value])==='undefined'){
                    priorities[value]=[];
                }
                priorities[value].push(index);
           });
           if(typeof(priorities[0])==='undefined'){
                priorities[0]=[];
           }
           that = this;
           $.each($(this.element).find('tr,th').first().find('td,th'),function(index){
               if(typeof(that.options.priority[index])==='undefined'){                    
                    priorities[0].push(index);
               }
           });

           //init columns selection box
           if(this.options.selectCol===true){
                   colHtml = '<div class="restableMenu restableMenuClosed" id="restableMenu'+instancesCount+'"><a class="restableMenuButton" href="#">Cols</a><ul>';
                   cols = $(this.element).find('tr:first-child th');
                   if(cols.length===0){
                       cols = $(this.element).find('tr:first-child td');
                   }
                   cols.each(function(index){
                       colHtml += '<li>';
                       colHtml += '<input type="checkbox" name="restable-toggle-cols" id="restable-toggle-col-'+index+'-'+instancesCount+'" data-col="'+index+'" checked="checked">';
                       colHtml += $(this).text();
                       colHtml += '</li>';
                   });
                   colHtml += '</ul></div>';
                   this.wrapper.prepend(colHtml);
           }

           //check overflow and hide cols
           var checkO = function(element,wrapper){
               $(element).find('th,td').css('display','');
               $(wrapper).find('.restableMenu ul input').prop('checked',true);
               if(checkOverflow(element,wrapper)){
                   $(element).css('width','auto');
                   var doBreak = false;
                   for(ip in priorities){
                       if(ip==='persistent'){
                           //we never hide persistent cols
                           break;
                       }
                        for(ij=0;ij<priorities[ip].length;ij++){
                           $(element).find('th:nth-child('+(parseInt(priorities[ip][ij])+1)+'),td:nth-child('+(parseInt(priorities[ip][ij])+1)+')').css('display','none');
                           $(wrapper).find('.restableMenu ul li:nth-child('+(parseInt(priorities[ip][ij])+1)+') input').prop('checked',false);
                           if(!checkOverflow(element,wrapper)){
                               doBreak = true;
                               return false;
                           }
                       }
                       if(doBreak===true){
                           break;
                       }
                    }
                }else{
                    $(element).css('width','');
                }
           };
           var that = this;
           $( window ).resize(function() {
                if(that._checkWindowResize()){
                    checkO(that.element,that.wrapper);
                }
           });
           checkO(this.element,this.wrapper);
           
           //Open the cols selection box
           $(this.wrapper).find('.restableMenuButton').click(function() {
               if($(this).parents('.restableMenu').hasClass('restableMenuClosed')){
                   $(this).parents('.restableMenu').removeClass('restableMenuClosed');
               }else{
                   $(this).parents('.restableMenu').addClass('restableMenuClosed');
               }
               return false;
           });
           $(document).click(function(event){
                if(!$(event.target).parents('.restableMenu').length){
                   $(that.wrapper).find('.restableMenu').addClass('restableMenuClosed');
                }
           });
           
           //Select a column to see or not
           $(this.wrapper).find('.restableMenu ul li').click(function(event){
               if($(event.target).is('input')){
                   $(event.target).prop('checked',!$(event.target).prop('checked'));
               }
               var input = $(this).find('input');
               
               var col = input.data('col')+1;
               if(input.prop('checked')){
                   $(that.element).find('th:nth-child('+col+'),td:nth-child('+col+')').css('display','none');
                   input.prop('checked',false);
               }else{
                   $(that.element).find('th:nth-child('+col+'),td:nth-child('+col+')').css('display','');
                   input.prop('checked',true);
               }
           });
       },
       
       /**
        * Check mobile resized
        * Mobiles throw a resize event after scrolling we need to check id it has been thrown by a real resize or a scroll
        * @returns true if it was a true resize event, false otherwise
        */
       _checkWindowResize : function(el,wrapper){
           var currentWindowWidth = $(window).width();
           if(typeof(savedWindowWidth)!=='undefined' && savedWindowWidth === currentWindowWidth){
               return false;
           }else{
               savedWindowWidth = currentWindowWidth;
               return true;
           }
       }
       
    };
    
    /**
     * Check if the table overflow
     * @returns true if table is overflow, false otherwise
     */
    checkOverflow = function(el,wrapper){
        if($(el).outerWidth() > $(wrapper).width()){
            return true;
        }else{
            return false;
        }
    };
    
    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( method ) {
        args = arguments
        
        return this.each(function () {            
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                new Plugin( this, method ));
            }else{
                 var plugin = $.data(this, "plugin_" + pluginName);
                 if ( plugin[method] ) {
                    return plugin[method].apply( this, Array.prototype.slice.call(args,1));
                } 
            }
        });
    };

})( jQuery, window, document );

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
};