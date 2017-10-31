<?php
/**
 * WP Framework
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

namespace Joomunited\WPFramework\v1_0_2;

use Joomunited\WPFramework\v1_0_2\Fields;

defined('ABSPATH') || die();


/*
 * HTML5 Form class with support of xml form config files
 */

class Form
{
    public $_form;
    private $_content = '';
    private $_datas = null;
    private $_errors = array();


    /**
     * Load an xml form file
     * @param type $form xml file to load without path and extension
     */
    public function load($form, $datas = null)
    {
        $this->_datas = $datas;

        if (file_exists($form)) {
            $file = $form;
        } else {
            $form = preg_replace('/[^A-Z0-9_-]/i', '', $form);
            $file = Factory::getApplication()->getPath() . DIRECTORY_SEPARATOR . Factory::getApplication()->getType() . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . $form . '.xml';
        }

        if (!file_exists($file)) {
            return false;
        }
        //load file
//            $xml = simplexml_load_file($file);
//            if($xml== false){
//                return false;
//            }

        $sxi = new \SimpleXmlIterator($file, null, true);
        $this->_form = self::loadChildrenRecusirve($sxi);
        return true;
    }


    /**
     * Load an xml object and convert it to recursive array
     * @param type $sxi
     * @param type $recursloop
     * @return type
     */
    protected function loadChildrenRecusirve($sxi, $recursloop = false)
    {
        $a = array();
        if ($recursloop == false) {
            $a = (array)$sxi->attributes();
        }

        for ($sxi->rewind(); $sxi->valid(); $sxi->next()) {
            $acnt = (array)$sxi->current()->attributes();
            if ($sxi->hasChildren()) {
                $acnt[] = self::loadChildrenRecusirve($sxi->current(), true);
            } else {
                if ($val = strval($sxi->current())) {
                    $acnt[] = strval($sxi->current());
                }
            }
            $a[][$sxi->key()] = $acnt;
        }
        return $a;
    }

    /**
     * Load the xml obj
     * @param type $xmlObj
     * @param type $depth
     * @return type
     */
//        protected function loadChildrenRecursive($xmlObj,$depth=0) {            
//            $attribs = (array)$xmlObj->attributes();
//            if(!empty($attribs)){
//                $array=array($xmlObj->getName()=>$attribs);
//            }else{
//                $array=array($xmlObj->getName()=>array('@attributes'=>array()));
//            }
//            foreach($xmlObj->children() as $child) {
//                $array[$xmlObj->getName()][] = self::loadChildrenRecursive($child,$depth+1);
//            }
//            return $array;            
//        }

    /**
     * Render a form to html
     * @param type $link the form url
     */
    public function render($link = null)
    {
        $this->_content = $this->start($this->_form['@attributes']);
        $this->_content .= $this->contentRender($this->_form);
        $this->_content .= $this->close();
        return $this->_content;
    }

    /**
     * Render the content of the form whitout the forms tags
     * @param array $fields an array with the form fields
     */
    private function contentRender($fields)
    {
        $content = "";
        foreach ($fields as $key => $value) {
            if ($key !== "@attributes") {
                $field = array_keys($value);
                if ($field[0] == 'fieldset') {
                    $content .= $this->fieldset($value);
                } else {
                    $content .= $this->input($value);
                }
            }
        }
        return $content;
    }

    /**
     * Render an input type
     * @param array input array with the input to render
     *
     */
    public function input($input)
    {
        $field = array_keys($input);

        if (!empty($input[${'field'}[0]]['@attributes']['type'])) {
            $class = ucfirst($input[${'field'}[0]]['@attributes']['type']);
        } else {
            $class = ucfirst($field[0]);
        }
        if (isset($this->_datas) && !empty($input[${'field'}[0]]['@attributes']['name']) && isset($this->_datas[$input[${'field'}[0]]['@attributes']['name']])) {
            $input[${'field'}[0]]['@attributes']['value'] = $this->_datas[$input[${'field'}[0]]['@attributes']['name']];
        }
        if (!empty($input[${'field'}[0]]['@attributes']['namespace'])) {
            $class = $input[${'field'}[0]]['@attributes']['namespace'] . $class;
        } else {
            $class = '\Joomunited\WPFramework\v1_0_2\Fields\\' . $class;
        }
        if (class_exists($class, true)) {
            $c = new $class;
            return $c->getfield($input[$field[0]]);
        }
    }

    public function fieldset($input)
    {
        $content = self::contentRender($input['fieldset']);
        return $this->fieldsetField($input['fieldset']['@attributes'], $content);
    }

    /*
     * Render <form> opening tag
     * Attributes are action method id class enctype
     *
     * @param mixte[] $attributes list of form attributes
     *
     */
    private function start(array $attributes = array())
    {
        $html = '<form';
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('action', 'method', 'id', 'class', 'enctype')) and !empty($value)) {
                    // assign default value to 'method' attribute
                    if ($attribute === 'method' AND ($value !== 'post' OR $value !== 'get')) {
                        $value = 'post';
                    }
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        return $html . '>';
    }

    /**
     * render a fieldset
     * @param array $attributes the attributes of the fieldset
     * @param string $content the content of the fieldset
     * @return string in html
     */
    public static function fieldsetField(array $attributes, $contentbase)
    {
        $html = '<fieldset';
        $content = '';
        if (!empty($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if (in_array($attribute, array('id', 'class', 'name', 'legend')) and !empty($value)) {
                    if ($attribute === 'legend') {
                        $content = '<legend>' . $value . '</legend>';
                        continue;
                    }
                    $html .= ' ' . $attribute . '="' . $value . '"';
                }
            }
        }
        return $html . '>' . $content . $contentbase . '</fieldset>';
    }


    /*
     *  render </form> closing tag
     *  return string
     */
    public static function close()
    {
        return '</form>';
    }


    /**
     * Validate form datas
     */
    public function validate()
    {
        foreach ($this->_form as $key => $value) {
            if ($key !== "@attributes") {
                $field = array_keys($value);
                if ($field != 'fieldset') {
                    $field = array_keys($value);
                    if (!empty($value[${'field'}[0]]['@attributes']['type'])) {
                        $class = ucfirst($value[${'field'}[0]]['@attributes']['type']);
                    } else {
                        $class = ucfirst('Field' . $field[0]);
                    }
                    if (!empty($value[${'field'}[0]]['@attributes']['namespace'])) {
                        $class = $value[${'field'}[0]]['@attributes']['namespace'] . $class;
                    } else {
                        $class = '\Joomunited\WPFramework\v1_0_2\Fields\\' . $class;
                    }
                    if (class_exists($class)) {
                        $c = new $class;
                        if ($c->validate($value[$field[0]]['@attributes']) == false) {
                            $this->_errors[] = $value[$field[0]]['@attributes']['name'];
                        }
                    }
                }
            }
        }
        if (!empty($this->_errors)) {
            return false;
        }
        return true;
    }


    /**
     * Sanitize form datas
     */
    public function sanitize()
    {
        foreach ($this->_form as $key => $value) {
            if ($key !== "@attributes") {
                $field = array_keys($value);
                if (!empty($value[${'field'}[0]]['@attributes']['name'])) {
                    $field = array_keys($value);
                    if (!empty($value[${'field'}[0]]['@attributes']['type'])) {
                        $class = ucfirst($value[${'field'}[0]]['@attributes']['type']);
                    } else {
                        $class = ucfirst('Field' . $field[0]);
                    }
                    if (!empty($value[${'field'}[0]]['@attributes']['namespace'])) {
                        $class = $value[${'field'}[0]]['@attributes']['namespace'] . $class;
                    } else {
                        $class = '\Joomunited\WPFramework\v1_0_2\Fields\\' . $class;
                    }
                    if (class_exists($class)) {
                        $c = new $class;
                        $this->_datas[$value[$field[0]]['@attributes']['name']] = $c->sanitize($value[$field[0]]['@attributes']);
                    }
                }
            }
        }
        return $this->_datas;
    }
}

?>