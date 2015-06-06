<?php
/*
* Project: Microcute
* Author: Matthew MacGregor
* Copyright: 2015 by Matthew MacGregor
* License: MIT (see LICENSE.txt for full details).
*/
class MicroCute {

    /**
    * Main entrypoint.
    */
    public function process_request( ) {
        $baseroute = $this->do_routing();
        $format = $this->choose_format( $baseroute );

        // If neither md or html was found, we'll use the error page instead
        if( $format == null ) {
            $baseroute = CONTENT_DIR . "error";
            $format = $this->choose_format( $baseroute );
        }

        if( $format == "html" ) {
            echo $this->render_html( $baseroute );
        } else if( $format == "md") {
            $this->render_markdown( $baseroute );
            echo $this->render_html( $baseroute );
        }
    }

    /**
    * Fetch content, handle replacements, and return the html.
    */
    public function render_html( $contentpath ){
        Plugins::trigger("before_render_html", [ &$contentpath ]);
        $config = Config::get();

        $content = $this->fetch_content($contentpath . ".html");
        Config::set_key('content', $content);

        $theme = $config['theme'];
        $html = $this->fetch_content( THEME_DIR . "$theme/index.html");
        Plugins::trigger("after_render_html", [ &$contentpath, &$html ]);
        return $html;
    }

    /**
    * Fetch markdown content, do replacements, parse markdown, return the html.
    */
    public function render_markdown( $contentpath ) {
        Plugins::trigger("before_render_markdown", [ &$contentpath ]);
        $config = Config::get();

        $parsedown = new Parsedown();
        $md = file_get_contents($contentpath . ".md");
        $md = $this->do_replacements($md);
        $html = $parsedown->text($md);

        Plugins::trigger("after_render_markdown", [ &$contentpath, &$html ]);

        file_put_contents($contentpath . ".html", $html);
    }

    /**
    * Fetch content from file and process replacements.
    */
    public function fetch_content( $path ) {
        Plugins::trigger("before_fetch_content", [ &$path ]);
        $config = Config::get();

        if( !file_exists($path) ) {
            die( "Error:: File $path does not exist." );
        }

        $content = file_get_contents($path);
        $content = $this->do_replacements( $content );

        Plugins::trigger("after_fetch_content", [ &$content ]);
        return $content;
    }

    /**
    * Process replacements in content. Content can be any text.
    */
    public function do_replacements(  $content ) {
        $replacements = $this->prepare_replacements( );
        return strtr( $content, $replacements );
    }

    /**
    * Returns a string to denote whether to work with markdown, html, or neither.
    * If markdown but no html exists, use markdown. If html exists, use html.
    * If markdown and html exist, use html unless markdown is newer than html.
    * @return string 'html', 'md' or null.
    */
    private function choose_format( $baseroute ) {
        $format = null;
        $html_route = $baseroute . ".html";
        $md_route = $baseroute . ".md";

        if( file_exists( $html_route ) && file_exists( $md_route ) ) {
            $htmltm = filemtime ( $html_route );
            $mdtm = filemtime ( $md_route );
            $format = ( $htmltm > $mdtm ) ? "html" : "md";
        } else if( file_exists( $html_route ) ) {
            $format = "html";
        } else if( file_exists( $md_route ) ) {
            $format = "md";
        } else {
            // neither exist
        }

        Plugins::trigger("choose_format", [ &$format, &$baseroute ]);
        return $format;
    }

    /**
    * Determines how to route the request to a file. If the route matches a
    * directory, use the index file. If the route matches a file, use the file.
    * @return string The filesystem path to the content.
    */
    private function do_routing() {
        $config = Config::get();
        $route = $this->remove_basepath_from_uri( $config['uri'], $config['basepath'] );
        Plugins::trigger("before_routing", [ &$route ]);

        if( is_dir(CONTENT_DIR . "$route") || empty($route) ) {
            $route = ( empty($route) ) ? "" : "$route/";
            $contentpath = CONTENT_DIR . $route . "index";
        } else {
            $contentpath = CONTENT_DIR . "$route";
        }

        Plugins::trigger("after_routing", [ &$route, &$contentpath ]);
        return $contentpath;
    }

    /**
    * In situations where the site is in a subdirectory, the framework needs to
    * remove this from the request uri. For example, if the site is in the
    * kitty/ subdirectory, and the route is kitty/meowser/, then we need to
    * remove kitty/ from the route. The result is the route for the request.
    */
    private function remove_basepath_from_uri( $uri, $basepath ) {

        // basepath: meow/kitty
        // uri: meow/kitty/tokyo
        // result: tokyo
        $count = 1;
        $path = ($basepath) ? str_replace($basepath, '', $uri, $count) : $uri;
        return trim($path, "/");
    }

    /**
    * Transform normal config values with replacement-style values {{ replacement }}.
    */
    private function prepare_replacements( ) {
        Plugins::trigger("before_prepare_replacements", []);
        $config = Config::get();

        $replacements = array();
        foreach( $config as $key => $value ) {
            $replacements["{{ " . $key ." }}"] = $value;
        }

        return $replacements;
    }

}
