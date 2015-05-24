<?php
/*
* Project: Microcute
* Author: Matthew MacGregor
* Copyright: 2015 by Matthew MacGregor
* License: MIT (see LICENSE.txt for full details).
*/

require_once PLUGIN_EXT_DIR . "ApiKeyAuthenticator.php";

class MicroCache {

    private $md5;
    private $cache_dir;
    private $is_enabled = true;

    public function on_load() {
        $this->is_enabled = Config::get_key("microcache_is_enabled");
        if( $this->is_enabled ) {
            $this->cache_dir = CONTENT_DIR . "_cache";
            if( file_exists( $this->cache_dir ) == false ) {
                mkdir( $this->cache_dir );
            }
        }
    }

    public function before_routing( &$route ) {
        if( $this->is_enabled ) {
            if( $route == 'api/clear-microcache' ) {
                ApiKeyAuthenticator::auto_auth( 'id', 'auth' );
                $files = glob( $this->cache_dir . "/*" ); // get all file names
                foreach($files as $file){ // iterate files
                    if(is_file($file)) {
                        unlink($file); // delete file
                    }
                }
                echo "Cache was cleared";
                Plugins::stopPropagation();
            }
        }
    }

    public function choose_format( &$format, &$baseroute ) {
        if( $this->is_enabled ) {
            if( $format == null ) {
                $baseroute = "error";
            }

            $this->md5 = md5($baseroute);
            if( file_exists( CONTENT_DIR . "_cache/{$this->md5}" ) ) {
                $html = file_get_contents( CONTENT_DIR . "_cache/{$this->md5}" );
                echo $html;
                Plugins::stopPropagation();
            }
        }
    }

    public function after_render_html( &$contentpath, &$html ) {
        if( $this->is_enabled ) {
            file_put_contents( CONTENT_DIR . "_cache/{$this->md5}", $html );
        }
    }

}
