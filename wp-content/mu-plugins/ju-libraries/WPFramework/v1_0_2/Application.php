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

class Application
{


    protected $_type = 'site';

    protected $default_task = 'display';

    protected static $instances = array();

    protected $dbo;

    protected $_name = '';

    protected $_path = '';

    protected $_url = '';

    protected $isinit = false;

    protected static $lastUse = '';

    public static function getInstance($name = null, $path = __FILE__, $type = null)
    {
        if ($name === null) {
            $name = self::$lastUse;
        }
        if (!array_key_exists($name, self::$instances)) {
            self::$instances[$name] = new Application();
            self::$instances[$name]->_name = $name;
            self::$instances[$name]->_path = plugin_dir_path($path);
            self::$instances[$name]->_url = plugins_url('', $path);

            if ($type !== null) {
                self::$instances[$name]->_type = $type;
            } elseif (is_admin()) {
                if (defined('DOING_AJAX')) {
                    if (Utilities::getInput('juwpfisadmin', 'GET', 'string', '')) {
                        self::$instances[$name]->_type = 'site';
                    } else {
                        self::$instances[$name]->_type = 'admin';
                    }
                } else {
                    self::$instances[$name]->_type = 'admin';
                }
            } else {
                self::$instances[$name]->_type = 'site';
            }
        }
        self::$lastUse = $name;
        return self::$instances[$name];
    }

    public function init()
    {
        if ($this->isinit === true) {
            return;
        }
        //call app init file
        $file = $this->getPath() . DIRECTORY_SEPARATOR . $this->getType() . DIRECTORY_SEPARATOR . 'init.php';
        if (file_exists($file)) {
            include($file);
        }
        //call filters
        $file = $this->getPath() . DIRECTORY_SEPARATOR . $this->getType() . DIRECTORY_SEPARATOR . 'filters.php';
        if (file_exists($file)) {
            include($file);
            $class = $this->getName() . 'Filter';
            if (method_exists($class, 'load')) {
                $filter = new $class();
                $filter->load();
            }
        }
        $this->isinit = true;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getPath($rel = false)
    {
        if ($rel) {
            return str_replace(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR, '', $this->_path . 'app');
        } else {
            return $this->_path . DIRECTORY_SEPARATOR . 'app';
        }
    }


    public function execute($default_task = null)
    {

        $task = Utilities::getInput('task');

        if (empty($task) && $default_task != null) {
            $task = $default_task;
        } else {
            $task = strtolower($task);
        }
        $task = strtolower($task);
        $split = explode('.', $task);

        $controllerName = $split[0];
        $taskName = count($split) > 1 ? $split[1] : '';

        $fileController = Factory::getApplication()->getPath() . DIRECTORY_SEPARATOR . $this->_type . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controllerName . '.php';

        if (!file_exists($fileController)) {
            Error::raiseError('404', 'Controller not found');
        }
        include_once $fileController;

        $controllerClass = strtolower(Factory::getApplication()->getName()) . 'Controller' . ucfirst($controllerName);
        $controller = new $controllerClass();

        if (method_exists($controller, $taskName)) {
            $controller->$taskName();
        } elseif (property_exists($controller, 'default_task') && method_exists($controller, $controller->default_task)) {
            $defaultTask = $controller->default_task;
            $controller->$defaultTask();
        } else {
            Error::raiseError('404', 'Task not found');
        }
    }

    public function isAdmin()
    {
        return $this->_type == 'admin';
    }

    public function isSite()
    {
        return $this->_type == 'site';
    }

    public function getType()
    {
        return $this->_type;
    }

    public function isAjax()
    {
        if (DOING_AJAX == true) {
            return true;
        }
        return false;
    }

    public function getAjaxUrl()
    {
        if ($this->isAdmin()) {
            return admin_url('admin-ajax.php?action=' . $this->getName() . '&');
        } else {
            return admin_url('admin-ajax.php?juwpfisadmin=false&action=' . $this->getName() . '&');
        }
    }

    public function getBaseUrl()
    {
        return $this->_url;
    }

}

?>
