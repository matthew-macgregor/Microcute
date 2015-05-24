### Microcute: The cutest web framework

**Why Microcute?** Does the world really need another web framework?

The short answer is an unequivocal **no**. There are plenty of microframeworks
and static site generators to keep you busy and happy for years to come. That
being said, this project was inspired by a few specific needs:

* Create content in Markdown (or html)
* No database
* Easy site backups (just zip it up)
* Insanely simple core
* Extensible via plugins

There are a few alternatives, but I wasn't fully happy with any of them. In
addition, Microcute was a good weekend project for me.

One point I want to make is that Microcute is not intended as a blogging
framework. If you want to build a blog with Microcute, you'll have extra work
to do.

### Installation

Installing **Microcute** is very simple. Download the source code from github.
Inside the main microcute directory run:

php -S localhost:8080

This will start a development server. Begin adding pages in the `site/content/`
directory.

That's it.

### Page Resolution

* Look for a directory matching the route.
* If a directory matches, use the index file in that directory.
* If there is no index file, show the 404 page.
* If no directory matches, look for a file that matches.
* If neither, show the 404 page.

Note: a directory will override a file. In other words:

/meow/kitty/

Will take precedence over:

/meow/kitty.html

You can see an example of a sub directory with index file at [sub]({{ site_url }}/sub).

### 404 Page

**Microcute** uses the `error.html` file in the content directory to generate the
error message. This may also be an error.md, and like any other file the html
will be auto-generated from the markdown.

### Markdown & HTML

**Microcute** treats markdown and html as equals when it comes to content. Here
are the rules:

* If an html file matches the route, use it.
* If a markdown file matches the route, and no html file exists:
* Parse the markdown, create the matching html file, and then serve it.
* If a markdown file and html file both exist:
* Check to see if the markdown file is fresher than the html file.
* If markdown is fresher, re-render and **overwrite** the html file.

### Plugins

A plugin extends the features and capabilities of the framework. Microcute is
built with plugins in mind. The core framework provides numerous hooks that
allow plugin developers to inject behavior. The events are as follows:

| Available Plugin Events                          |
| -----------------------                          |
| before_prepare_config( )                         |
| after_prepare_config( )                          |
| on_load()                                        |
| before_routing( &$route )                        |
| after_routing( &$contentpath )                   |
| choose_format( &$format, &$baseroute )           |
| before_render_markdown( &$contentpath )          |
| after_render_markdown( &$contentpath, &$html )   |
| before_render_html( &$contentpath )              |
| after_render_html( &$contentpath, &$html )       |
| before_prepare_replacements( )                   |


The config may be accessed in any of these callbacks using `Config::get()`.
Remember to update the config with `Config::set()` if you make any changes.

You may also get and set individual keys using `Config::set_key()` and
`Config::get_key()`. See `Config.php` for more details.

For a plugin to work, it must implement a class that has the same name as the
php file. For example, class `MicroCache` must be in `MicroCache.php`. Two
examples are provided: `ExamplePlugin.php` and `MicroCache.php`.

### Plugin Extensions

The `plugins/ext` directory provides a site for extension libraries that will be
used or shared by plugins. These libraries don't hook in directly to the request
lifecycle, but provide useful utilities that can be leveraged by plugins. For
example, the sample site contains the `ApiKeyAuthenticator` class.

You can require these files using the `PLUGIN_EXT_DIR` constant:

require_once PLUGIN_EXT_DIR . "ApiKeyAuthenticator.php";
