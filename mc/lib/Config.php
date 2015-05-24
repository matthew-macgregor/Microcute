<?php

/**
* The Config is a central pillar of the Microcute framework, and as such it
* is globally accessible through this static object. Behind the scenes, the
* config is simply an associative array. It stores configuration data, feeds
* replacement values used in templates, and can be used to provide custom
* replacements as well as settings for plugins.
*
* You may access the file by calling Config::get(). Please note that this is
* read-only copy. If you make changes and want them to persist, you must call
* Config::set($config).
*/
class Config {

    private static $config = array();

    /**
    * Returns the config, which is an associative array.
    */
    public static function get() {
        return self::$config;
    }

    /**
    * Sets the config by overwriting the old with the incoming array. The param
    * should be an associative array.
    */
    public static function set($config) {
        self::$config = $config;
    }

    /**
    * Adds a key/value pair to the existing config array.
    */
    public static function set_key( $key, $value ) {
        self::$config[$key] = $value;
    }

    public static function get_key( $key ) {
        if( isset( self::$config[$key])) {
            return self::$config[$key];
        } else {
            return null;
        }
    }

    public static function has_key( $key ) {
        return isset( self::$config[$key ] );
    }

    /**
    * Loads a file called config.php located in the ROOT_DIR of the project.
    * config.php should be used to initialize the config values using
    * Config::set().
    */
    public static function load() {
        require_once ROOT_DIR . "config.php";
        self::prepare_config();
    }

    /**
    * Sets a bunch of default values in the config.
    */
    private function prepare_config( ) {
        Plugins::trigger("before_prepare_config", [ ]);
        $config = self::$config;

        $config['uri'] = strtok($_SERVER['REQUEST_URI'],'?');

        if( isset( $config['domain'] ) == false ) {
            $config['domain'] = "http://" . $_SERVER['HTTP_HOST'];
        }

        if( isset($config['theme']) == false ) {
            $config['theme'] = 'default';
        }

        // Remove trailing slashes from the domain
        $config['domain'] = rtrim($config['domain'], '/');
        $config['basepath'] = rtrim($config['basepath'], '/');
        $config['site_url'] = ($config['basepath']) ? "{$config['domain']}/{$config['basepath']}" : $config['domain'];
        $config['theme_url'] = $config['site_url'] . THEME_BASE . $config['theme'];
        $config['year'] = date("Y");


        Plugins::trigger("after_prepare_config", []);
        self::$config = $config;
    }
}
