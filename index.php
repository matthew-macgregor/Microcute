<?php

define( "ROOT_DIR", 		dirname(__FILE__) . "/" );
define( "MC_DIR", 			"mc/" );
define( "THEME_BASE", 		"/site/themes/" );
define( "CONTENT_DIR", 		ROOT_DIR . "site/content/" );
define( "THEME_DIR", 		ROOT_DIR . ltrim( THEME_BASE, "/" ));
define( "LIB_DIR", 			ROOT_DIR . MC_DIR . "lib/" );
define( "PLUGIN_DIR", 		ROOT_DIR . MC_DIR . "plugins/");
define( "PLUGIN_EXT_DIR", 	PLUGIN_DIR . "ext/" );

require_once LIB_DIR. "Parsedown.php";
require_once LIB_DIR. "Plugins.php";
require_once LIB_DIR. "Config.php";
require_once LIB_DIR. "MicroCute.php";

main();

function main() {
	Config::load();
	Plugins::load();
	$mc = new MicroCute();
	$mc->process_request();
}
