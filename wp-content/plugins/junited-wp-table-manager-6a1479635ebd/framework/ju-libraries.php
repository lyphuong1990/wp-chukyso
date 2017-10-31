<?php
/*
JUPlugin Name: Joomunited WP Framework
Description: WP Framework for Joomunited extensions
Author: Joomunited
Version: 1.0.2
Author URI: http://www.joomunited.com
*/
defined('ABSPATH') || die();

function juLibrariesCheck($version)
{
    $version = str_replace('.', '_', $version);
    if (file_exists(WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'ju-libraries' . DIRECTORY_SEPARATOR . 'WPFramework' . DIRECTORY_SEPARATOR . 'v' . $version)) {
        return true;
    }
    return false;
}

function juLibrariesAutoload($className)
{
    $className = ltrim($className, '\\');

    //Return if it's not a Joomunited's class
    if (strpos($className, 'Joomunited') !== 0) {
        return;
    }

    //Change Joomunited to the mu-plugin junited-libraries directory

    $fileName = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName = 'ju-libraries' . substr($fileName, 10);
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . $fileName)) {
        require dirname(__FILE__) . DIRECTORY_SEPARATOR . $fileName;
    }
}

spl_autoload_register('juLibrariesAutoload');