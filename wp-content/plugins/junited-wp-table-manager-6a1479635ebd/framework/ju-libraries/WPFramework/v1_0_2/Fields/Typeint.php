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

class Typeint extends Field
{

    public function getfield($field)
    {
        $attributes = &$field['@attributes'];
        $attributes['value'] = (int)$attributes['value'];
        $attributes['type'] = 'text';
        return parent::getfield($field);
    }


    protected $validate = '/[0-9]+/';

    public function sanitize($field)
    {
        $value = null;
        if (!empty($_POST[$field['name']])) {
            $value = $_POST[$field['name']];
        } elseif (!empty($_GET[$field['name']])) {
            $value = $_GET[$field['name']];
        }
        return (int)$value;
    }

}