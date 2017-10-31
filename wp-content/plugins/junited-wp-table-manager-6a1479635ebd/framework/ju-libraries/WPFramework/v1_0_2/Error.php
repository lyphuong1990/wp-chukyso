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

class Error
{

    public static function raiseError($type = '404', $message = 'An error occurs', $title = 'Error')
    {
        wp_die($message, $title, array('response' => $type));
    }

}

?>
