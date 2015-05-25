<?php
/*
* Project: Microcute
* Author: Matthew MacGregor
* Copyright: 2015 by Matthew MacGregor
* License: MIT (see LICENSE.txt for full details).
*/

class Plugins {

    private static $classes = array();

    /**
    * Loads all files from the plugins directory, and instantiates objects.
    */
    public static function load() {
        $plugins = scandir( PLUGIN_DIR );
        $plugin_classes = array();

        foreach( $plugins as $plugin ) {
            if( $plugin == "." || $plugin == ".." || is_dir(PLUGIN_DIR . $plugin) ) continue;
            if( self::endsWith( $plugin, '.php') ) {
                require_once PLUGIN_DIR . $plugin;
                $plugin_classes[] = str_replace( ".php", "", $plugin );
            }
        }

        foreach( $plugin_classes as $class ) {
            $instance = new $class();
            if( method_exists( $instance, "on_load" )) {
                $instance->on_load();
            }
            self::$classes[]  = $instance;
        }
    }

    /**
    * Triggering an event will call that method on every plugin object, if the
    * method exists.
    */
    public static function trigger( $event_name, $args ) {
        foreach( self::$classes as $class ) {
            if( method_exists( $class, $event_name )) {

                // Using pass-by-reference in a few cases. It's possible that
                // this may cause issues with some versions of php?
                call_user_func_array( [$class, $event_name], $args );
            }
        }

    }

    /**
    * Stops processing this request. This is the way to signal to the framework
    * that your plugin has handled the request.
    */
    public static function stopPropagation() {
        // Plugin has taken over...
        exit(0);
    }

    private static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

}
