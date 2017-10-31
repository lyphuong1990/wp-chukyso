<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Genesis\Menus
 * @author  StudioPress
 * @license GPL-2.0+
 * @link    http://my.studiopress.com/themes/genesis/
 */

/**
 * Determine if a child theme supports a particular Genesis nav menu.
 *
 * @since 1.8.0
 *
 * @param string $menu Name of the menu to check support for.
 * @return bool `true` if menu supported, `false` otherwise.
 */
function genesis_nav_menu_supported($menu)
{

    if (!current_theme_supports('genesis-menus')) {
        return false;
    }

    $menus = get_theme_support('genesis-menus');

    if (array_key_exists($menu, (array)$menus[0])) {
        return true;
    }

    return false;

}

/**
 * Determine if the Superfish script is enabled.
 *
 * If child theme supports HTML5 and the Load Superfish Script theme setting is checked, or if the
 * `genesis_superfish_enabled` filter is true, then this function returns true. False otherwise.
 *
 * @since 1.9.0
 *
 * @return bool `true` if Superfish is enabled, `false` otherwise.
 */
function genesis_superfish_enabled()
{

    return (!genesis_html5() && genesis_get_option('superfish')) || genesis_a11y('drop-down-menu') || apply_filters('genesis_superfish_enabled', false);

}

/**
 * Return the markup to display a menu consistent with the Genesis format.
 *
 * Applies the `genesis_$location_nav` filter e.g. `genesis_header_nav`. For primary and secondary menu locations, it
 * applies the `genesis_do_nav` and `genesis_do_subnav` filters instead for backwards compatibility.
 *
 * @since 2.1.0
 *
 * @param string|array $args Menu arguments.
 * @return string|null Navigation menu markup, or `null` if menu is not assigned to theme location, there is
 *                     no menu, or there are no menu items in the menu.
 */
function genesis_get_nav_menu($args = array())
{

    $args = wp_parse_args($args, array(
        'theme_location' => '',
        'container' => '',
        'menu_class' => 'menu genesis-nav-menu',
        'link_before' => genesis_markup(array(
            'open' => '<span %s>',
            'context' => 'nav-link-wrap',
            'echo' => false,
        )),
        'link_after' => genesis_markup(array(
            'close' => '</span>',
            'context' => 'nav-link-wrap',
            'echo' => false,
        )),
        'echo' => 0,
    ));

    // If a menu is not assigned to theme location, abort.
    if (!has_nav_menu($args['theme_location'])) {
        return null;
    }

    // If genesis-accessibility for 'drop-down-menu' is enabled and the menu doesn't already have the superfish class, add it.
    if (genesis_superfish_enabled() && false === strpos($args['menu_class'], 'js-superfish')) {
        $args['menu_class'] .= ' js-superfish';
    }

    $sanitized_location = sanitize_key($args['theme_location']);

    $nav = wp_nav_menu($args);

    // Do nothing if there is nothing to show.
    if (!$nav) {
        return null;
    }

    $nav_markup_open = genesis_structural_wrap('menu-' . $sanitized_location, 'open', 0);
    $nav_markup_close = genesis_structural_wrap('menu-' . $sanitized_location, 'close', 0);
    $params = array(
        'theme_location' => $args['theme_location'],
    );

    $nav_output = genesis_markup(array(
        'open' => '<nav %s>',
        'close' => '</nav>',
        'context' => 'nav-' . $sanitized_location,
        'content' => $nav_markup_open . $nav . $nav_markup_close,
        'echo' => false,
        'params' => $params,
    ));

    $filter_location = 'genesis_' . $sanitized_location . '_nav';

    // Handle back-compat for primary and secondary nav filters.
    if ('primary' === $args['theme_location']) {
        $filter_location = 'genesis_do_nav';
    } elseif ('secondary' === $args['theme_location']) {
        $filter_location = 'genesis_do_subnav';
    }

    /**
     * Filter the navigation markup.
     *
     * @since 2.1.0
     *
     * @param string $nav_output Opening container markup, nav, closing container markup.
     * @param string $nav Navigation list (`<ul>`).
     * @param array $args {
     *     Arguments for `wp_nav_menu()`.
     *
     * @type string $theme_location Menu location ID.
     * @type string $container Container markup.
     * @type string $menu_class Class(es) applied to the `<ul>`.
     * @type bool $echo 0 to indicate `wp_nav_menu()` should return not echo.
     * }
     */
    return apply_filters($filter_location, $nav_output, $nav, $args);
}

/**
 * Echo the output from `genesis_get_nav_menu()`.
 *
 * @since 2.1.0
 *
 * @param string $args Menu arguments.
 */
function genesis_nav_menu($args)
{
    echo genesis_get_nav_menu($args);
}
