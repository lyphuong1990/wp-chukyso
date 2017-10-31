<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */
use Joomunited\WPFramework\v1_0_2\Application;
?>
<div class="wrap wptm-config">
    <div id="icon-options-general" class="icon32"></div>
    <h2><?php _e("WP Table Manager Configuration",'wptm'); ?></h2>
    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder columns-2">
            <div id="wptm-container-config" class="">
                <div class="tab-header">
                    <ul class="nav-tab-wrapper" id="wptm-tabs-config">
                        <?php
                        $this->tabs = array(
                            'main' => __('Main settings','wptm'),
                        );
                        $cont_first = 0;
                        foreach ($this->tabs as $tab => $val) {
                            $active_class = $cont_first == 0 ? ' active' : '';
                            $cont_first++;
                            ?>
                            <a id="tab-<?php echo $tab;?>" class="nav-tab<?php echo $active_class;?>" data-tab-id="<?php echo $tab;?>" href="#<?php echo $tab;?>"><?php echo ucfirst($val);?></a>
                        <?php } ?>
                        <a id="tab-jutranslation" class="nav-tab" data-tab-id="jutranslation" href="#jutranslation"><?php echo ucfirst(__('Translation', 'wptm'));?></a>
                    </ul>
                </div>
                <div class="tab-content" id="wptm-tabs-content-config">
                    <div id="wptm-main-config" class="tab-pane active"> <?php echo $this->configform; ?></div>
                    <div id="wptm-jutranslation-config" class="tab-pane "> <?php echo \Joomunited\WPTableManager\Jutranslation\Jutranslation::getInput(); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #wptm-tabs,#wptm-tabs-config { margin-bottom: 1px;}
    .wptmparams { }
    #wptm-tabs .nav-tab.active,
    #wptm-tabs-config .nav-tab.active {
        background-color: #FFF;
        color: #464646;
    }
    #wptm-tabs-content,#wptm-tabs-content-config { background: #fff; border-left:1px solid #CCC; padding: 10px 10px 30px 10px}
    #wptm-tabs-content .tab-pane { display: none}
    #wptm-tabs-content-config .tab-pane { display: none}
    #wptm-tabs-content .tab-pane.active { display: block}
    #wptm-tabs-content-config .tab-pane.active { display: block}
    #wptm-tabs-content-config textarea {
        width: 100%;
    }
</style>
<script type="text/javascript">
    ajaxurl = "<?php echo Application::getInstance('wptm')->getAjaxUrl(); ?>";
    jQuery(document).ready(function($) {

        $("#wptm-tabs-config .nav-tab").click(function(e) {
            e.preventDefault();
            $("#wptm-tabs-config .nav-tab").removeClass('active');
            id_tab = $(this).data('tab-id');
            $("#tab-"+ id_tab).addClass('active');
            $("#wptm-tabs-content-config .tab-pane").removeClass('active');
            $("#wptm-"+ id_tab + '-config').addClass('active');
            $("#wptm-theme-"+ id_tab ).addClass('active');
            document.cookie = 'active_tab='+id_tab ;
        })

        function setTabFromCookie() {
            active_tab = getCookie('active_tab');
            if(active_tab != "") {
                $("#tab-"+ active_tab).click();
            }
        }

        function getCookie(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i<ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
            }
            return "";
        }

        setTabFromCookie();
        if($("input[name=dropboxKey]").val() != '' && $("input[name=dropboxSecret]").val() != '' ){
            $('#dropboxAuthor + .help-block').html('');
        }
        else{
            $("#dropboxAuthor").attr('type','hidden');
        }

    });
</script>