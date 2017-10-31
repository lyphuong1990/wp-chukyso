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

class Model
{

    protected $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = &$wpdb;
    }

    public static function getInstance($name = '', $type = null)
    {
        $app = Application::getInstance();

        $name = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
        if ($type === null) {
            $type = $app->getType();
        } elseif ($type !== 'admin' || $type !== 'site') {
            return false;
        }

        $className = $app->getName() . 'Model' . ucfirst(strtolower($name));
        $classFile = $app->getPath() . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . strtolower($name) . '.php';
        if (!file_exists($classFile)) {
            return false;
        }
        require_once $classFile;
        if (class_exists($className)) {
            return new $className();
        }
        return false;
    }

    public function getPrefix()
    {
        return $this->db->prefix;
    }

    public function update($table, $data, $where, $format = null, $where_format = null)
    {
        return $this->db->update($table, $data, $where, $format = null, $where_format = null);
    }

}