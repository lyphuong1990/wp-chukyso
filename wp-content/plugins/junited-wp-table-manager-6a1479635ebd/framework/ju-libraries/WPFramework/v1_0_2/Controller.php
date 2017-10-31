<?php
/**
 * WP Framework
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

namespace Joomunited\WPFramework\v1_0_2;

defined('ABSPATH') || die();

class Controller
{

    public $default_task = 'display';

    protected $_redirect;


    public function display()
    {
        $view = $this->loadView();
        $view->render();
    }

    protected function loadView()
    {
        $viewname = Utilities::getInput('view');
        if (empty($viewname)) {
            $viewname = get_class($this);
            $viewname = strtolower(str_replace(Factory::getApplication()->getName() . 'Controller', '', $viewname));
        }
        $viewname = preg_replace('/[^A-Z0-9_-]/i', '', $viewname);
        $filepath = Factory::getApplication()->getPath() . DIRECTORY_SEPARATOR . Factory::getApplication()->getType() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $viewname . DIRECTORY_SEPARATOR;

        if (file_exists($filepath . 'view.php')) {
            require_once $filepath . 'view.php';
        } else {
            Error::raiseError('404', 'View file not found');
        }

        $class = Factory::getApplication()->getname() . 'View' . ucfirst($viewname);
        $view = new $class();
        $view->setPath($filepath);
        return $view;

    }

    public function getModel($modelname = null)
    {

        if (empty($modelname)) {
            $modelname = get_class($this);
            $modelname = str_replace(Factory::getApplication()->getName() . 'Controller', '', $modelname);
        }
        $modelname = preg_replace('/[^A-Z0-9_-]/i', '', $modelname);
        $filepath = Factory::getApplication()->getPath() . DIRECTORY_SEPARATOR . Factory::getApplication()->getType() . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . strtolower($modelname) . '.php';
        if (!file_exists($filepath)) {
            return false;
        }
        include_once $filepath;
        $class = Factory::getApplication()->getName() . 'Model' . $modelname;
        $model = new $class();
        return $model;
    }

    public function redirect($location)
    {
        if (!headers_sent()) {
            wp_safe_redirect($location, 303);
        } else {
            echo "<script>document.location.href='" . str_replace("'", "&apos;", $location) . "';</script>\n";
        }
    }

    protected function exit_status($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        die();
    }
}