<?php
/**
 * WP Framework
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

namespace Joomunited\WPFramework\v1_0_2\Fields;

use Joomunited\WPFramework\v1_0_2\Field;

defined('ABSPATH') || die();

class Input extends Field
{

    public function sanitize($value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }
}