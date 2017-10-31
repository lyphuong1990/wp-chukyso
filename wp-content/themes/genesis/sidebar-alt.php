<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Genesis\Templates
 * @author  StudioPress
 * @license GPL-2.0+
 * @link    http://my.studiopress.com/themes/genesis/
 */

// Output secondary sidebar structure.
genesis_markup( array(
	'open'    => '<aside %s>' . genesis_sidebar_title( 'sidebar-alt' ),
	'context' => 'sidebar-secondary',
) );

do_action( 'genesis_before_sidebar_alt_widget_area' );
do_action( 'genesis_sidebar_alt' );
do_action( 'genesis_after_sidebar_alt_widget_area' );

// End .sidebar-secondary.
genesis_markup( array(
	'close'   => '</aside>',
	'context' => 'sidebar-secondary',
) );
