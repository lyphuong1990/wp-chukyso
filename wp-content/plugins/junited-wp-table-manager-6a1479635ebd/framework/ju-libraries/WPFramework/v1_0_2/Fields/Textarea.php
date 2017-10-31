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
use Joomunited\WPFramework\v1_0_2\Factory;

defined('ABSPATH') || die();

class Textarea extends Field
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

        $html .= '<textarea ';
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('id', 'class', 'placeholder', 'name', 'rows', 'cols')) and isset($value)) {
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        $html .= ' >';
        if (!empty($attributes['value'])) {
            $html .= $attributes['value'];
        }
        $html .= ' </textarea>';
        if (!empty($attributes['help']) && $attributes['help'] != '') {
            $html .= '<p class="help-block">' . $attributes['help'] . '</p>';
        }
        if (!empty($attributes['type']) || (!empty($attributes['hidden']) && $attributes['hidden'] != 'true')) {
            $html .= '</div></div>';
        }
        return $html;
    }

}