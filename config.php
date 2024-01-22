<?php
/* Full-Text RSS config */

// ......IMPORTANT......................................
// .....................................................
// Please do not change this file (config.php) directly.
// Save a copy as custom_config.php and make your
// changes to that instead. It will automatically
// override anything in config.php. Because config.php
// always gets loaded anyway, you can simply specify
// options you'd like to override in custom_config.php.
// .....................................................

// Create config object
if (!isset($options)) $options = new stdClass();

// Enable service
// ----------------------
// Set this to false if you want to disable the service.
// If set to false, no feed is produced and users will 
// be told that the service is disabled.
$options->enabled = true;

// Debug mode
// ----------------------
// Enable or disable debugging. When enabled debugging works by passing
// &debug to the makefulltextfeed.php querystring.
// Valid values:
// true or 'user' (default) - let user decide
// 'admin' - debug works only for logged in admin users
// false - disabled
$options->debug = true;

// Default entries (without access key)
// ----------------------
// The number of feed items to process when no API key is supplied
// and no &max=x value is supplied in the querystring.
$options->default_entries = 20;

// Max entries (without access key)
// ----------------------
// The maximum number of feed items to process when no access key is supplied.
// This limits the user-supplied &max=x value. For example, if the user
// asks for 20 items to be processed (&max=20), if max_entries is set to 
// 10, only 10 will be processed.
$options->max_entries = 20;

// Full content
// ----------------------
// By default Full-Text RSS includes the extracted content in the output.
// You can exclude this from the output by passing '&content=0' in the querystring.
// 
// Possible values...
// Always include: true
// Never include: false
// Include unless user overrides (&content=0): 'user' (default)
//
// Note: currently this does not disable full content extraction. It simply omits it
// from the output.
$options->content = 'user';

// Excerpts
// ----------------------
// By default Full-Text RSS does not include excerpts in the output.
// You can enable this by passing '&summary=1' in the querystring.
// This will include a plain text excerpt from the extracted content.
// 
// Possible values...
// Always include: true (recommended for new users)
// Never include: false
// Don't include unless user overrides (&summary=1): 'user' (default)
//
// Important: if both content and excerpts are requested, the excerpt will be
// placed in the description element and the full content inside content:encoded.
// If excerpts are not requested, the full content will go inside the description element.
// 
// Why are we not returning both excerpts and content by default?
// Mainly for backward compatibility.
// Excerpts should appear in the feed item's description element. Previous versions 
// of Full-Text RSS did not return excerpts, so the description element was always 
// used for the full content (as recommended by the RSS advisory). When returning both, 
// we need somewhere else to place the content (content:encoded). 
// Having both enabled should not create any problems for news readers, but it may create
// problems for developers upgrading from one of our earlier versions who may now find
// their applications are returning excerpts instead of the full content they were
// expecting. To avoid such surprises for users who are upgrading Full-Text RSS, 
// excerpts must be explicitly requested in the querystring by default.
// 
// Why not use a different element name for excerpts?
// According to the RSS advisory: 
// "Publishers who employ summaries should store the summary in description and 
// the full content in content:encoded, ordering description first within the item. 
// On items with no summary, the full content should be stored in description."
// See: http://www.rssboard.org/rss-profile#namespace-elements-content-encoded
// 
// For more consistent element naming, we recommend new users set this option to true.
// The full content can still be excluded via the querystring, but the element names
// will not change: when $options->summary = true, the description element will always
// be reserved for the excerpt and content:encoded always for full content.
$options->summary = 'user';

// Rewrite relative URLs
// ----------------------
// With this enabled relative URLs found in the extracted content
// block are automatically rewritten as absolute URLs.
$options->rewrite_relative_urls = true;

// Exclude items if extraction fails
// ---------------------------------
// Excludes items from the resulting feed
// if we cannot extract any content from the
// item URL.
// Possible values...
// Enable: true
// Disable: false (default)
// User decides: 'user' (this option will appear on the form)
$options->exclude_items_on_fail = 'user';

// Enable single-page support
// -------------------------
// If enabled, we will try to follow single page links (e.g. print view) on multi-page articles.
// Currently this only happens for sites where single_page_link has been defined 
// in a site config file.
$options->singlepage = true;

// Enable multi-page support
// -------------------------
// If enabled, we will try to follow next page links on multi-page articles.
// Currently this only happens for sites where next_page_link has been defined 
// in a site config file.
$options->multipage = true;

// Enable disk caching
// ----------------------
// Enable this if you'd like to cache results on disk.
// Possible options:
//  * Disable caching: false (default)
//  * Enable caching: true
// Cache files are written to disk (in cache/ subfolders - which must be writable).
// Initially it's best to keep this disabled to make sure everything works
// as expected. If you have APC enabled, please also see smart_cache in the
// advanced section.
$options->caching = false;

// Cache time (minutes)
// ----------------------
// How long should a response be cached?
// Note: this affects both disk caching and the cache headers
// sent in the Full-Text RSS HTTP response.
// So even if you disable disk caching, this value will still
// affect the cache headers in the HTTP response.
$options->cache_time = 10;

// Cache directory
// ----------------------
// Only used if caching is true
$options->cache_dir = dirname(__FILE__).'/cache';

// Message to prepend (without access key)
// ----------------------
// HTML to insert at the beginning of each feed item when no access key is supplied.
// Substitution tags:
// {url} - Feed item URL
// {effective-url} - Feed item URL after we've followed all redirects
$options->message_to_prepend = '';

// Message to append (without access key)
// ----------------------
// HTML to insert at the end of each feed item when no access key is supplied.
// Substitution tags:
// {url} - Feed item URL
// {effective-url} - Feed item URL after we've followed all redirects
$options->message_to_append = '';

// Error message when content extraction fails (without access key)
// ----------------------
$options->error_message = '[unable to retrieve full-text content]';

// Keep enclosure in feed items
// If enabled, we will try to preserve enclosures if present.
// ----------------------
$options->keep_enclosures = true;

// Detect language
// ---------------
// Should we try and find/guess the language of the article being processed?
// Values will be placed inside the <dc:language> element inside each <item> element
// Possible values:
// * Ignore language: 0
// * Use article/feed metadata (e.g. HTML lang attribute): 1
// * As above, but guess if not present: 2
// * Always guess: 3
// * User decides: 'user' (value of 0-3 can be passed in querystring: e.g. &lang=2, &lang=1 will be default if nothing supplied)
$options->detect_language = 'user';

// Allow user-submitted site config in request
// ---------------
// If enabled, a user can submit site config rules directly in the request
// using the siteconfig request parameter. Disabled (false) by default.
$options->user_submitted_config = false;

// Remove items identified as native ads?
// ---------------
// Many news sites now carry native advertising - articles which have been
// paid for by a corporation to promote their brand or product.
// Full-Text RSS can identify such articles in certain sites. If an article
// is identified as being a native ad, we'll add a <dc:type>Native Ad</dc:type>
// element to the item. But you can also request that such ads be removed from
// the output altogether. To do so, set the option below to true.
// Note: this only has effect when the input URL is a feed, not a web page.
$options->remove_native_ads = false;

/////////////////////////////////////////////////
/// RESTRICT ACCESS /////////////////////////////
/////////////////////////////////////////////////

// Admin credentials
// ----------------------
// Certain pages/actions, e.g. updating site patterns with our online tool, will require admin credentials.
// To use these pages, enter a password here and you'll be prompted for it when you try to access those pages.
// If no password or username is set, pages requiring admin privelages will be inaccessible. 
// The default username is 'admin'.
// If overriding with an environment variable, separate username and password with a colon, e.g.:
// ftr_admin_credentials: admin:my-secret-password
// Example: $options->admin_credentials = array('username'=>'admin', 'password'=>'my-secret-password');
$options->admin_credentials = array('username'=>'admin', 'password'=>'');

// URLs to allow
// ----------------------
// List of URLs (or parts of a URL) which the service will accept.
// If the list is empty, all URLs (except those specified in the blocked list below)
// will be permitted.
// Note: for feeds, this option applies to both feed URLs and item URLs within those feeds.
// Empty: array();
// Non-empty example: array('example.com', 'anothersite.org');
$options->allowed_urls = array();

// URLs to block
// ----------------------
// List of URLs (or parts of a URL) which the service will not accept.
// Note: this list is ignored if allowed_urls is not empty.
// Note: for feeds, this option applies to both feed URLs and item URLs within those feeds.
$options->blocked_urls = array();

// Key holder(s) only?
// ----------------------
// Set this to true if you want to restrict access only to
// those with a key (see below to specify key(s)).
// If set to true, no feed is produced unless a valid
// key is provided.
$options->key_required = false;

// Access keys (password protected access)
// ------------------------------------
// NOTE: You do not need an API key from fivefilters.org to run your own 
// copy of the code. This is here if you'd like to restrict access to
// _your_ copy.
// Keys let you group users - those with a key and those without - and
// restrict access to the service to those without a key.
// If you want everyone to access the service in the same way, you can
// leave the array below empty and ignore the access key options further down.
// The options further down let you control how the service should behave 
// in each mode.
// Note: Explicitly including the index number (1 and 2 in the examples below) 
// is highly recommended (when generating feeds, we encode the key and 
// refer to it by index number and hash).
$options->api_keys = array();
// Example:
// $options->api_keys[1] = 'secret-key-1';
// $options->api_keys[2] = 'secret-key-2';

// Default entries (with access key)
// ----------------------
// The number of feed items to process when a valid access key is supplied.
$options->default_entries_with_key = 5;

// Max entries (with access key)
// ----------------------
// The maximum number of feed items to process when a valid access key is supplied.
$options->max_entries_with_key = 10;

/////////////////////////////////////////////////
/// ADVANCED OPTIONS ////////////////////////////
/////////////////////////////////////////////////

// Enable XSS filter?
// ----------------------
// We have not enabled this by default because we assume the majority of
// our users do not display the HTML retrieved by Full-Text RSS
// in a web page without further processing. If you subscribe to our generated
// feeds in your news reader application, it should, if it's good software, already
// filter the resulting HTML for XSS attacks, making it redundant for
// Full-Text RSS do the same. Similarly with frameworks/CMS which display
// feed content - the content should be treated like any other user-submitted content.
// 
// If you are writing an application yourself which is processing feeds generated by
// Full-Text RSS, you can either filter the HTML yourself to remove potential XSS attacks
// or enable this option. This might be useful if you are processing our generated
// feeds with JavaScript on the client side - although there's client side xss
// filtering available too, e.g. https://code.google.com/p/google-caja/wiki/JsHtmlSanitizer
// 
// If enabled, we'll pass retrieved HTML content through htmLawed with
// safe flag on and style attributes denied, see
// http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/htmLawed_README.htm#s3.6
// Note: if enabled this will also remove certain elements you may want to preserve, such as iframes.
//
// Valid values:
// true - enabled, all content will be filtered
// 'user' (default) - user must pass &xss=1 in makefulltextfeed.php querystring to enable
// false - disabled
$options->xss_filter = 'user';

// Favour item titles in feed
// ----------------------
// By default, when processing feeds, we assume item titles in the feed
// have not been truncated. So after processing web pages, the extracted titles
// are not used in the generated feed. If you prefer to have extracted titles in 
// the feed you can either set this to false, in which case we will always favour 
// extracted titles. Alternatively, if set to 'user' (default) we'll use the 
// extracted title if you pass '&use_extracted_title' in the querystring.
// Possible values:
// * Favour feed titles: true 
// * Favour extracted titles: false
// * Favour feed titles with user override: 'user' (default)
// Note: this has no effect when the input URL is to a web page - in these cases
// we always use the extracted title in the generated feed.
$options->favour_feed_titles = 'user';

// Allowed HTML parsers
// ----------------------
// Full-Text RSS attempts to use PHP's libxml extension to process HTML.
// While fast, on some sites it may not always produce good results. 
// For these sites, you can specify an alternative HTML parser: 
// parser: html5php
// The html5php parser is bundled with Full-Text RSS.
// see https://github.com/Masterminds/html5-php
//
// To disable HTML parsing with html5php, remove it from this list.
// By default we allow both libxml and html5php.
// Note: html5php requires PHP 5.3 or higher. If you're running PHP 5.2, 
// we'll always use libxml.
$options->allowed_parsers = array('libxml', 'html5php');
//$options->allowed_parsers = array('libxml'); //disable html5php - forcing libxml in all cases

// Parser override in querystring
// ---------------------
// If enabled, user can pass &parser=html5php to override default parser.
// Possible values:
// * false: Don't allow override in querystring
// * true: Allow (default)
$options->allow_parser_override = true;

// Enable Cross-Origin Resource Sharing (CORS)
// ----------------------
// If enabled we'll send the following HTTP header
// Access-Control-Allow-Origin: *
// see http://en.wikipedia.org/wiki/Cross-origin_resource_sharing
$options->cors = false;

// Proxy server(s)
// ----------------------
// You can specify proxy servers here and ask Full-Text RSS to 
// route HTTP requests through these servers.
// If no proxy server is listed, all requests will be made directly.
// A proxy server should be given a unique name (key in the array)
// and as its value another array with key 'host' and, if required, 'auth'.
//
// Note: if you're listing proxies so Full-Text RSS randomly chooses one
// for each request, you can also specify 'direct' as a value to make
// sure direct requests are randomly made as well.
$options->proxy_servers = array();
// For example:
//$options->proxy_servers = array('example1'=>array('host'=>'127.0.0.1:8888'), 'example2'=>array('host'=>'127.0.0.1:8888', 'auth'=>'user:pass'), 'direct'=>array());
// If Polipo is installed and you want to use it as a caching proxy, uncomment the following line.
//$options->proxy_servers = array('polipo'=>array('host'=>'127.0.0.1:8123'));

// Proxy mode
// ----------------------
// How the proxy servers above should be used:
// Possible options:
// * Disable: false (no proxy will be used)
// * Named: specify which server should be used (e.g. 'example1')
// * Random: true (default) a random one from the set above will be used each time Full-Text RSS is called.
// Note: if no proxy servers are entered in $options->proxy_servers, no proxies will be used.
$options->proxy = true;

// Proxy override in querystring
// ----------------------
// If enabled, user can disable or change the proxy server used.
// Possible values:
// * false: Don't allow override in querystring
// * true: Allow user to disable or choose a proxy through a request parameter, like so...
//    &proxy=0 to disable
//    &proxy=1 for default behaviour (see $options->proxy) (default)
//    &proxy=example1 to specify one of the proxies listed in $options->proxy_servers
// Note: Only proxy servers listed in the config file can be used.
$options->allow_proxy_override = true;

// Use APC user cache?
// ----------------------
// If enabled we will store site config files (when requested 
// for the first time) in APC's user cache. Keys prefixed with 'sc.'
// This improves performance by reducing disk access.
// Note: this has no effect if APC is unavailable on your server.
$options->apc = true;

// Smart cache (experimental)
// ----------------------
// With this option enabled we will not cache to disk immediately.
// We will store the cache key in APC and if it's requested again
// we will cache results to disk. Keys prefixed with 'cache.'
// This improves performance by reducing disk access.
// Note: this has no effect if APC is disabled or unavailable on your server,
// or if you have caching disabled.
$options->smart_cache = true;

// Fingerprints
// ----------------------
// key is fingerprint (fragment to find in HTML)
// value is host name to use for site config lookup if fingerprint matches
$options->fingerprints = array(
	// Posterous
	'<meta name="generator" content="Posterous"' => array('hostname'=>'fingerprint.posterous.com', 'head'=>true),
	// Blogger
	'<meta content=\'blogger\' name=\'generator\'' => array('hostname'=>'fingerprint.blogspot.com', 'head'=>true),
	'<meta name="generator" content="Blogger"' => array('hostname'=>'fingerprint.blogspot.com', 'head'=>true),
	// WordPress (hosted)
	// '<meta name="generator" content="WordPress.com"' => array('hostname'=>'fingerprint.wordpress.com', 'head'=>true),
	// WordPress (self-hosted and hosted)
	'<meta name="generator" content="WordPress' => array('hostname'=>'fingerprint.wordpress.com', 'head'=>true)
);

// URL Rewriting
// ----------------------
// Currently allows simple string replace of URLs.
// Useful for rewriting certain URLs to point to a single page
// or HTML view. Although using the single_page_link site config
// instruction is the preferred way to do this, sometimes, as
// with Google Docs URLs, it's not possible.
// Note: this might move to the site config file at some point.
$options->rewrite_url = array(
	// Rewrite public Google Docs URLs to point to HTML view:
	// if a URL contains docs.google.com, replace /Doc? with /View?
	'docs.google.com' => array('/Doc?' => '/View?'),
	'tnr.com' => array('tnr.com/article/' => 'tnr.com/print/article/'),
	'.m.wikipedia.org' => array('.m.wikipedia.org' => '.wikipedia.org'),
	'm.vanityfair.com' => array('m.vanityfair.com' => 'www.vanityfair.com')
);

// Content-Type exceptions
// -----------------------
// Here you can define different actions based
// on the Content-Type header returned by server.
// MIME type as key, action as value.
// Valid actions:
// * 'exclude' - exclude this item from the result
// * 'link' - create HTML link to the item
$options->content_type_exc = array( 
							   'application/pdf' => array('action'=>'link', 'name'=>'PDF'),
							   'image' => array('action'=>'link', 'name'=>'Image'),
							   'audio' => array('action'=>'link', 'name'=>'Audio'),
							   'video' => array('action'=>'link', 'name'=>'Video')
							  );

// Cache directory level
// ----------------------
// Spread cache files over different directories (only used if caching is enabled).
// Used to prevent large number of files in one directory.
// This corresponds to Zend_Cache's hashed_directory_level
// see http://framework.zend.com/manual/en/zend.cache.backends.html
// It's best not to change this if you're unsure.
$options->cache_directory_level = 0;

// Cache cleanup
// -------------
// 0 = script will not clean cache (rename cachecleanup.php and use it for scheduled (e.g. cron) cache cleanup)
// 1 = clean cache everytime the script runs (not recommended)
// 100 = clean cache roughly once every 100 script runs
// x = clean cache roughly once every x script runs
// ...you get the idea :)
$options->cache_cleanup = 100;

// Open Graph properties
// ---------------------
// By default Full-Text RSS includes Open Graph properties in the output.
// Set this to false if you do not want to include Open Graph properties.
$options->opengraph = true;

/////////////////////////////////////////////////
/// DO NOT CHANGE ANYTHING BELOW THIS ///////////
/////////////////////////////////////////////////

if (!defined('_FF_FTR_VERSION')) define('_FF_FTR_VERSION', '3.5');

if (basename(__FILE__) == 'config.php') {
	if (file_exists(dirname(__FILE__).'/custom_config.php')) {
		require_once dirname(__FILE__).'/custom_config.php';
	}
	
	// check for environment variables - often used on cloud platforms
	// environment variables should be prefixed with 'ftr_', e.g.
	// ftr_max_entries: 1
	// will set the max_entries value to 1.
	foreach ($options as $_key=>&$_val) {
		$_key = "ftr_$_key";
		if (($_env = getenv($_key)) !== false) {
			if (is_array($_val)) {
				if ($_key === 'ftr_admin_credentials') {
					$_val = array_combine(array('username', 'password'), array_map('trim', explode(':', $_env, 2)));
					if ($_val === false) $_val = array('username'=>'admin', 'password'=>'');
				}
			} elseif ($_env === 'true' || $_env === 'false') {
				$_val = ($_env === 'true');
			} elseif (is_numeric($_env)) {
				$_val = (int)$_env;
			} else { // string
				$_val = $_env;
			}
		}
	}
	unset($_key, $_val, $_env);
}
