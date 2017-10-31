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

class Field
{

    /**
     *  render <input> tag
     */
    public function getfield($field)
    {
        $attributes = $field['@attributes'];
        $html = '';
        if (!empty($attributes['type']) || (!empty($attributes['hidden']) && $attributes['hidden'] != 'true')) {
            $html .= '<div class="control-group">';
            if (!empty($attributes['label']) && $attributes['label'] != '' && !empty($attributes['name']) && $attributes['name'] != '') {
                $html .= '<label class="control-label" for="' . $attributes['name'] . '">' . __($attributes['label'], Factory::getApplication()->getName()) . '</label>';
            }
            $html .= '<div class="controls">';
        }
        if (empty($attributes['hidden']) || (!empty($attributes['hidden']) && $attributes['hidden'] != 'true')) {
            $html .= '<input';
        } else {
            $html .= '<hidden';
        }

        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('type', 'id', 'class', 'placeholder', 'name', 'value')) and isset($value)) {
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        $html .= ' />';
        if (!empty($attributes['help']) && $attributes['help'] != '') {
            $html .= '<p class="help-block">' . $attributes['help'] . '</p>';
        }
        if (!empty($attributes['type']) || (!empty($attributes['hidden']) && $attributes['hidden'] != 'true')) {
            $html .= '</div></div>';
        }
        return $html;
    }

    public function validate($field)
    {
        if (!empty($field['name'])) {
            if (isset($_POST[$field['name']])) {
                $value = $_POST[$field['name']];
            } elseif (isset($_GET[$field['name']])) {
                $value = $_GET[$field['name']];
            }
        }

        if (!empty($field['required']) && $field['required'] == 'true') {
            if (trim($value) == '') {
                return false;
            }
        }

        if (!isset($this->validate)) {
            return true;
        }

        if (empty($value) && isset($field['required']) && $field['required'] == 'true') {
            return false;
        }

        return preg_match($this->validate, $value);
    }

    public function sanitize($field)
    {
        $value = null;
        if (isset($_POST[$field['name']])) {
            $value = $_POST[$field['name']];
        } elseif (isset($_GET[$field['name']])) {
            $value = $_GET[$field['name']];
        }
        return filter_var($value, FILTER_SANITIZE_STRING);
    }
}