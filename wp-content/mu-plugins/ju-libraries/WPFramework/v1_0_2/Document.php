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

class Document
{

    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = &new Document();
        }
        return self::$instance;
    }


}

?>
