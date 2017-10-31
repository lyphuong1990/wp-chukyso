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

class Button extends Field
{

    public function getfield($field)
    {
        $attributes = $field['@attributes'];
        $html = '<div class="control-group">';
        $html .= '<button';
        $content = '';
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('id', 'class', 'name', 'value', 'type')) and !empty($value)) {
                    if ($attribute === 'value') {
                        $content = __($value, Factory::getApplication()->getName());
                        continue;
                    }
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        $html .= '>' . $content . '</button>';
        $html .= '</div>';
        return $html;
    }
}