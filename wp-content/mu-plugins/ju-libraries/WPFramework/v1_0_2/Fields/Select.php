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
use Joomunited\WPFramework\v1_0_2\Application;

defined('ABSPATH') || die();

class Select extends Field
{

    public function getfield($field)
    {
        $attributes = $field['@attributes'];
        $html = '';
        if (empty($attributes['hidden']) || (!empty($attributes['hidden']) && $attributes['hidden'] != 'true')) {
            $html .= '<div class="control-group">';
            if (!empty($attributes['label']) && $attributes['label'] != '' && !empty($attributes['name']) && $attributes['name'] != '') {
                $html .= '<label class="control-label" for="' . $attributes['name'] . '">' . __($attributes['label'], Factory::getApplication()->getName()) . '</label>';
            }
            $html .= '<div class="controls">';
        }
        $html .= '<select';
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('id', 'class', 'onchange', 'name')) and isset($value)) {
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        $html .= ' >';

        $cleanfield = $field;
        unset($cleanfield['@attributes']);
        if (!empty($cleanfield[0])) {
            foreach ($cleanfield[0] as $child) {
                if (!empty($child['option']['@attributes'])) {
                    $html .= '<option ';
                    foreach ($child['option']['@attributes'] as $childAttribute => $childValue) {
                        if (in_array($childAttribute, array('type', 'id', 'class', 'name', 'onchange', 'value')) and isset($childValue)) {
                            $html .= ' ' . $childAttribute . '="' . $childValue . '"';
                            if ($childAttribute == 'value' && isset($attributes['value']) && $attributes['value'] == $childValue) {
                                $html .= ' selected="selected"';
                            }
                        }
                    }
                    $html .= '>';
                    $html .= __($child['option'][0], strtolower(Application::getInstance()->getName()));
                    $html .= '</option>';
                }
            }
        }

        $html .= '</select>';
        if (!empty($attributes['help']) && $attributes['help'] != '') {
            $html .= '<p class="help-block">' . $attributes['help'] . '</p>';
        }
        if (!empty($attributes['type']) || (!empty($attributes['hidden']) && $attributes['hidden'] != 'true')) {
            $html .= '</div></div>';
        }
        return $html;
    }
}