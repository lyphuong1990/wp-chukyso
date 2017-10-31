<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Application;

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if( !function_exists('wptmAutoload') ) {
	function wptmAutoload( $className ) {
		$className = ltrim( $className, '\\' );
		//Return if it's not a Joomunited's class
		if ( strpos( $className, 'Joomunited\WP_Table_Manager\Admin\Fields' ) === 0 ) {
			$fileName  = '';
			$namespace = '';
			if ( $lastNsPos = strripos( $className, '\\' ) ) {
				$namespace = substr( $className, 0, $lastNsPos );
				$className = substr( $className, $lastNsPos + 1 );
				$fileName  = str_replace( '\\', DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
			}
			$fileName .= str_replace( '_', DIRECTORY_SEPARATOR, $className ) . '.php';

			$folder   = 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR;
			$fileName = '' . DIRECTORY_SEPARATOR . substr( $fileName, 41 );
			if ( file_exists( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $folder . $fileName ) ) {
				require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $folder . $fileName;
			}

			return;
		}

		//don't load any namespace class
		if ( strpos( $className, '\\' ) !== false ) {
			return;
		}
		$fileName = basename( $className ) . '.php';
		$app      = Application::getInstance( 'wptm' );
		if ( $app->isAdmin() ) {
			$file = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $fileName;
		} else {
			$file = $app->getPath() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $fileName;
		}
		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}
}
spl_autoload_register('wptmAutoload');