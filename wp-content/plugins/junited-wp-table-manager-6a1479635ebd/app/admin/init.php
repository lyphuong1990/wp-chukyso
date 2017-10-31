<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Model;

defined( 'ABSPATH' ) || die();

$app = Application::getInstance('wptm');
require_once $app->getPath().DIRECTORY_SEPARATOR.$app->getType().DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'wptmBase.php';

add_action( 'admin_menu', 'wptm_menu' );
add_action( 'wp_ajax_wptm', 'wptm_ajax' );
add_action( 'media_buttons_context', 'wptm_button');
add_action( 'load-dashboard_page_wptm-foldertree', 'wptm_foldertree_thickbox' ); 
add_action('wp_ajax_wptm_getFolders', 'wptm_getFolders' );

// Load the heartbeat JS
function wptm_heartbeat_enqueue( $hook_suffix ) {
    // Make sure the JS part of the Heartbeat API is loaded.
    wp_enqueue_script( 'heartbeat' );
    add_action( 'admin_print_footer_scripts', 'wptm_heartbeat_footer_js', 20 );
}
add_action( 'admin_enqueue_scripts', 'wptm_heartbeat_enqueue' );
// Inject our JS into the admin footer
function wptm_heartbeat_footer_js() {
    global $pagenow;
    
?>
    <script>
    (function($){
 
        // Hook into the heartbeat-send
        $(document).on('heartbeat-send', function(e, data) {
            data['wptm_heartbeat'] = 'rendering';
        });
 
        // Listen for the custom event "heartbeat-tick" on $(document).
        $(document).on( 'heartbeat-tick', function(e, data) {
              // Only proceed if our EDD data is present
            if ( ! data['wptm-result'] )
                return;
                        
        });
    }(jQuery));
    </script>
 <?php
}

// Modify the data that goes back with the heartbeat-tick
function wptm_heartbeat_received( $response, $data ) {
 
    // Make sure we only run our query if the edd_heartbeat key is present
    if( $data['wptm_heartbeat'] == 'rendering' ) {
        $app = Application::getInstance('wptm');
        require_once dirname(WPTM_PLUGIN_FILE).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR. 'wptmHelper.php';
        $wptmHelper = new WptmHelper();
        $modelTables = Model::getInstance('tables');
        $tables = $modelTables->getItems();    
        if(count($tables)) {
            foreach ($tables as $table) {
                 $wptmHelper->styleRender($table);
                 $wptmHelper->htmlRender($table);
            }
        }
       
        $modelConfig = Model::getInstance('config'); 
        $params = $modelConfig->getConfig();
        if(isset($params['sync_periodicity']) && $params['sync_periodicity'] != '0'):
             if(isset($params['last_sync']) && $params['last_sync'] != '0' ) {
                 $last_sync  =   $params['last_sync'];
             }else {
                 $last_sync = 0; 
             }
             $time_now=(int)strtotime(date('Y-m-d H:i:s'));
             if( ($time_now - $last_sync)/3600 >= $params['sync_periodicity'] ) {
                 //do sync                 
                 $app->execute('excel.syncSpreadsheet');
                 
                 $params['last_sync'] = $time_now ;
                 $modelConfig->save($params);
             }
             
        endif;
        // Send back the number of complete payments
        $response['wptm-result'] = time();
 
    }
    return $response;
}

add_filter( 'heartbeat_received', 'wptm_heartbeat_received', 10, 2 );

function wptm_menu() {
        $app = Application::getInstance('wptm');
        add_menu_page( 'WP Table Manager', 'Table Manager', 'edit_posts', 'wptm', 'wptm_call', 'dashicons-screenoptions');
        add_submenu_page( 'wptm', 'Database tables', 'Database tables','manage_options', 'wptm-db-table', 'wptm_call_dbtable' );
        add_submenu_page( 'wptm', 'WP Table Manager config', 'Configuration','manage_options', 'wptm-config', 'wptm_call_config' );
        add_submenu_page( null, 'Folder tree', 'Folder tree', 'manage_options', 'wptm-foldertree', 'wptm_folderTree' );
}

function wptm_ajax(){
    define( 'WPTM_AJAX', 'true');
    wptm_call();
}

function wptm_call($ref=null,$default_task='wptm.display') {

	if ( !current_user_can( 'edit_posts' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.','wptm' ) );
	}
	
        $application = Application::getInstance('wptm');

        wptm_init();

        $application->execute($default_task);
}

function wptm_call_config() {

        wptm_call(null,'config.display');
}

function wptm_call_dbtable() {

        wptm_call(null,'dbtable.display');
}

function wptm_init(){
     
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
        $application = Application::getInstance('wptm');
        load_plugin_textdomain( 'wptm', null,$application->getPath(true).DIRECTORY_SEPARATOR.'languages');
        load_plugin_textdomain( 'wptm', null, dirname( plugin_basename( WPTM_PLUGIN_FILE ) ).DIRECTORY_SEPARATOR. 'app' .DIRECTORY_SEPARATOR.'languages');

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-migrate');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-sortable');        	
        
        wp_enqueue_script('wptm-iris',  plugins_url('assets/js/iris.min.js',__FILE__) , array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false,1 );
        wp_enqueue_script('wptm-color-picker',   admin_url('js/color-picker.min.js'), array( 'wptm-iris'), false,1 );        
        wp_localize_script( 'wptm-color-picker', 'wpColorPickerL10n', array(
			'clear' => __( 'Clear', 'wptm' ),
			'defaultString' => __( 'Default', 'wptm'),
			'pick' => __( 'Select Color' , 'wptm'),
			'current' => __( 'Current Color' , 'wptm'),
		) );
        wp_enqueue_style( 'wp-color-picker' );         
        
        wp_enqueue_script('wptm-bootstrap',plugins_url('assets/js/bootstrap.min.js',__FILE__), array(), WPTM_VERSION);
        wp_enqueue_style('wptm-bootstrap',plugins_url('assets/css/bootstrap.min.css',__FILE__), array(), WPTM_VERSION);

        wp_enqueue_script('wptm-touch-punch',plugins_url('assets/js/jquery.ui.touch-punch.min.js',__FILE__), array(), WPTM_VERSION);
        
        wp_enqueue_style('buttons');
        wp_enqueue_style('wp-admin');
        wp_enqueue_style('colors-fresh');
      
        wp_enqueue_script( 'thickbox' ); 
        wp_enqueue_style( 'thickbox' );         
        
        wp_enqueue_style('wptm-style',plugins_url('assets/css/style.css',__FILE__), array(), WPTM_VERSION);
        wp_enqueue_style('wptm-table-sprites',plugins_url('assets/css/table-sprites.css',__FILE__, array(), WPTM_VERSION));
        wp_enqueue_style('wptm-handsontable',plugins_url('assets/css/jquery.handsontable.full.css',__FILE__), array(), WPTM_VERSION);
        wp_enqueue_style('wptm-codemirror',plugins_url('assets/codemirror/codemirror.css',__FILE__));
        wp_enqueue_style('wptm-codemirror3024-night',plugins_url('assets/codemirror/3024-night.css',__FILE__));
        wp_enqueue_style('wptm-modal',plugins_url('assets/css/leanmodal.css',__FILE__));
        wp_enqueue_script('wptm-modal',plugins_url('assets/js/jquery.leanModal.min.js',__FILE__));       
        wp_enqueue_script('less',plugins_url('assets/js/less.js',__FILE__), array(), WPTM_VERSION);
        wp_enqueue_script('handsontable',plugins_url('assets/js/jquery.handsontable.full.js',__FILE__), array(), WPTM_VERSION);        
        wp_enqueue_script('jquery-textselect',plugins_url('assets/js/jquery.textselect.min.js',__FILE__), array(), WPTM_VERSION);
        if(!Utilities::getInput('noheader', 'GET', 'bool')){
            wp_enqueue_script('jquery-nestable',plugins_url('assets/js/jquery.nestable.js',__FILE__), array(), WPTM_VERSION);
        }
        wp_enqueue_script('wptm-codemirror',plugins_url('assets/codemirror/codemirror.js',__FILE__), array(), WPTM_VERSION );
        wp_enqueue_script('wptm-codemirror-css',plugins_url('assets/codemirror/mode/css/css.js',__FILE__), array(), WPTM_VERSION );
        wp_enqueue_script('wptm-bootbox',plugins_url('assets/js/bootbox.js',__FILE__), array(), WPTM_VERSION );
        if($page=='wptm') {
            wp_enqueue_script('wptm-main',plugins_url('assets/js/wptm.js',__FILE__), array(), WPTM_VERSION);
        }
        //if($page=='wptm-db-table') {
            wp_enqueue_script('wptm-handlebars', plugins_url( 'assets/js/handlebars-1.0.0-rc.3.js' , __FILE__ ), array(), WPTM_VERSION);
            wp_enqueue_script('wptm-db-table',plugins_url('assets/js/wptm_dbtable.js',__FILE__), array(), WPTM_VERSION);
       // }
        
        wp_enqueue_script('chart',plugins_url('assets/js/chart.min.js',__FILE__), array(), WPTM_VERSION);
        
        wp_enqueue_script('dropzone',plugins_url('assets/js/dropzone.min.js',__FILE__), array(), WPTM_VERSION);
        wp_enqueue_script('jquery-fileDownload',plugins_url('assets/js/jquery.fileDownload.js',__FILE__), array(), WPTM_VERSION);        
       
	if(Utilities::getInput('noheader', 'GET', 'bool')){
	    //remove script loaded in bottom of page
	    wp_dequeue_script( 'sitepress-scripts' );
	    wp_dequeue_script( 'wpml-tm-scripts' );
	}
        
        wp_enqueue_media();
        add_filter('tiny_mce_before_init', 'wptm_tiny_mce_before_init');  // Before tinymce initialization
        // Build extra plugins array
	add_filter('mce_external_plugins', 'wptm_mce_external_plugins' );
        add_editor_style(  WP_TABLE_MANAGER_PLUGIN_URL . 'app/admin/assets/css/wptm-editor-style.css' );
}

function wptm_button($context){
    wp_enqueue_style('wptm-modal',plugins_url('assets/css/leanmodal.css',__FILE__));
    wp_enqueue_script('wptm-modal',plugins_url('assets/js/jquery.leanModal.min.js',__FILE__));
    wp_enqueue_script('wptm-modal-init',plugins_url('assets/js/leanmodal.init.js',__FILE__));

    $context .= "<a href='#wptmmodal' class='button wptmlaunch' id='wptmlaunch' title='WP Table Manager'>"
                . " <span class='dashicons dashicons-screenoptions' style='line-height: inherit;'></span>WP Table Manager</a>";

    return $context;
}

function wptm_mce_external_plugins($plugins) {
    	$plugins['code'] = WP_TABLE_MANAGER_PLUGIN_URL . 'app/admin/assets/plugins/code/plugin.min.js';
        $plugins['wpmedia'] = WP_TABLE_MANAGER_PLUGIN_URL . 'app/admin/assets/plugins/wpmedia/plugin.js';
        return $plugins;
}

function wptm_tiny_mce_before_init($init) {
    // Initialize table ability
		if (isset($init['tools'])) {
			$init['tools'] = $init['tools'].',inserttable';
		} else {
			$init['tools'] = 'inserttable';
		}

                if (isset($init['toolbar2'])) {
                    $init['toolbar2'] = $init['toolbar2'].',code,wpmedia';
                }else {
                     $init['toolbar1'] = $init['toolbar1'].',code,wpmedia';
                }
                $init['height'] = "500";
                
                return $init;
}

function wptm_folderTree() {
       /* Do nothing */
}

function wptm_foldertree_thickbox() {
        if(!defined('IFRAME_REQUEST')) {
            define('IFRAME_REQUEST',true);
        }
        iframe_header(); 
        global $wp_scripts, $wp_styles;        
        
        wp_enqueue_script('wptm-jaofiletree',plugins_url('assets/js/jaofiletree.js',__FILE__), array(), WPTM_VERSION );
        wp_enqueue_style('wptm-jaofiletree',plugins_url('assets/css/jaofiletree.css',__FILE__), array(), WPTM_VERSION);
        
        //$include_folders =  'wp-content/uploads';
        //$selected_folders = explode(',',$include_folders );
       ?>
<div style="padding-top: 10px;">
    <div class="pull-left" style="float: left">  
      <div id="wptm_foldertree"></div>
    </div>
    <div class="pull-right" style="float: right;margin-right: 10px;">	
            <button class="button button-primary" type="button" onclick="selectFile()"><?php echo  __('OK', 'wptm')  ?></button>
            <button class="button" type="button" onclick="window.parent.tb_remove();"><?php echo __('Cancel', 'wptm')  ?></button>
    </div>
</div>  
<style>

#wptm_foldertree input[type="checkbox"] {
    width: 16px;
    height:16px;
}        
#wptm_foldertree input[type="checkbox"]:checked:before {
    font-size: 20px;
    line-height: 20px;
}
</style>
<script>

jQuery(document).ready(function($) {
    wptm_site_url ='<?php echo get_site_url();?>';
    selectFile = function() {  
        selected_file = "";
        $('#wptm_foldertree').find('input:checked + a').each(function(){         
            selected_file = $(this).attr('data-file');
        })
        
       window.parent.document.getElementById('jform_spreadsheet_url').value = wptm_site_url + selected_file; 
       window.parent.jQuery("#jform_spreadsheet_url").change();     
       window.parent.tb_remove();
    }    

        
   $('#wptm_foldertree').jaofiletreewptm({
            script  : ajaxurl ,
            usecheckboxes : 'files',
            showroot : '/',
            oncheck: function(elem,checked,type,file){                                  
                
            }
    });
     
})
</script>   
<?php
        iframe_footer(); 
        exit; //Die to prevent the page continueing loading and adding the admin menu's etc. 
}
    function wptm_getFolders() {
            
        //$include_folders = 'wp-content/uploads';
       // $selected_folders = explode(',', $include_folders);      
        $path = ABSPATH.DIRECTORY_SEPARATOR;
        $dir = $_REQUEST['dir'];
        $allowed_ext = array('xls','xlsx');
        $return = $dirs =  $fi = array();
        if( file_exists($path.$dir) ) {            
                $files = scandir($path.$dir);

                natcasesort($files);
                if( count($files) > 2 ) { // The 2 counts for . and ..
                    // All dirs
                    $baseDir = ltrim(rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $dir),'/'),'/'); 
                    if($baseDir != '') $baseDir .= '/';                    
                    foreach( $files as $file ) {			
                            if( file_exists($path . $dir . DIRECTORY_SEPARATOR . $file) && $file != '.' && $file != '..' && is_dir($path . $dir. DIRECTORY_SEPARATOR . $file) ) {                                                                    
                                    $dirs[] = array('type'=>'dir','dir'=>$dir,'file'=>$file);                                    
                            }elseif( file_exists($path . $dir . DIRECTORY_SEPARATOR . $file) && $file != '.' && $file != '..' && !is_dir($path . $dir . DIRECTORY_SEPARATOR . $file) ) {
                                $dot = strrpos($file, '.') + 1;
                                $file_ext = strtolower( substr($file, $dot) ); //var_dump($file_ext);
                                if(  in_array($file_ext, $allowed_ext) ) {
                                    $fi[] = array('type'=>'file','dir'=>$dir,'file'=>$file,'ext'=>$file_ext);
                                }
                            }
                    }
                    $return = array_merge($dirs,$fi);
                }
        }
        echo json_encode( $return );      
        die();
    }    