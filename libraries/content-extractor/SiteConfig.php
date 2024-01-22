<?php
/**
 * Site Config
 * 
 * Each instance of this class should hold extraction patterns and other directives
 * for a website. See ContentExtractor class to see how it's used.
 * 
 * @version 1.0
 * @date 2015-06-09
 * @author Keyvan Minoukadeh
 * @copyright 2015 Keyvan Minoukadeh
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPL v3
 */

class SiteConfig
{
	// Use first matching element as title (0 or more xpath expressions)
	public $title = array();
	
	// Use first matching element as body (0 or more xpath expressions)
	public $body = array();
	
	// Use first matching element as author (0 or more xpath expressions)
	public $author = array();
	
	// Use first matching element as date (0 or more xpath expressions)
	public $date = array();
	
	// Strip elements matching these xpath expressions (0 or more)
	public $strip = array();
	
	// Strip elements which contain these strings (0 or more) in the id or class attribute 
	public $strip_id_or_class = array();
	
	// Strip images which contain these strings (0 or more) in the src attribute 
	public $strip_image_src = array();

	// Mark article as a native ad if any of these expressions match (0 or more xpath expressions)
	public $native_ad_clue = array();
	
	// Additional HTTP headers to send (associative array)
	public $http_header = array();
	
	// Process HTML with tidy before creating DOM (bool or null if undeclared)
	public $tidy = null;
	
	protected $default_tidy = true; // used if undeclared
	
	// Autodetect title/body if xpath expressions fail to produce results.
	// Note that this applies to title and body separately, ie. 
	//   * if we get a body match but no title match, this option will determine whether we autodetect title 
	//   * if neither match, this determines whether we autodetect title and body.
	// Also note that this only applies when there is at least one xpath expression in title or body, ie.
	//   * if title and body are both empty (no xpath expressions), this option has no effect (both title and body will be auto-detected)
	//   * if there's an xpath expression for title and none for body, body will be auto-detected and this option will determine whether we auto-detect title if the xpath expression for it fails to produce results.
	// Usage scenario: you want to extract something specific from a set of URLs, e.g. a table, and if the table is not found, you want to ignore the entry completely. Auto-detection is unlikely to succeed here, so you construct your patterns and set this option to false. Another scenario may be a site where auto-detection has proven to fail (or worse, picked up the wrong content).
	// bool or null if undeclared
	public $autodetect_on_failure = null;
	protected $default_autodetect_on_failure = true; // used if undeclared
	
	// Clean up content block - attempt to remove elements that appear to be superfluous
	// bool or null if undeclared
	public $prune = null;
	protected $default_prune = true; // used if undeclared
	
	// Test URL - if present, can be used to test the config above
	public $test_url = array();

	// Test URL contains - one or more snippets of text from the article body.
	// Used to determine if the extraction rules for the site are still valid (ie. still extracting relevant content)
	// Keys should be one or more of the test URLs supplied, and value an array of strings to look for.
	public $test_contains = array();

	// If page contains - XPath expression. Used to determine if the preceding rule gets evaluated or not.
	// Currently only works with single_page_link.
	public $if_page_contains = array();	
	
	// Single-page link - should identify a link element or URL pointing to the page holding the entire article
	// This is useful for sites which split their articles across multiple pages. Links to such pages tend to 
	// display the first page with links to the other pages at the bottom. Often there is also a link to a page
	// which displays the entire article on one page (e.g. 'print view').
	// This should be an XPath expression identifying the link to that page. If present and we find a match,
	// we will retrieve that page and the rest of the options in this config will be applied to the new page.
	public $single_page_link = array();
	
	public $next_page_link = array();
	
	// Single-page link in feed? - same as above, but patterns applied to item description HTML taken from feed
	public $single_page_link_in_feed = array();
	
	// Which parser to use for turning raw HTML into a DOMDocument (either 'libxml' or 'html5lib')
	// string or null if undeclared
	public $parser = null;
	protected $default_parser = 'libxml'; // used if undeclared
	
	// Strings to search for in HTML before processing begins (used with $replace_string)
	public $find_string = array();
	// Strings to replace those found in $find_string before HTML processing begins
	public $replace_string = array();
	
	// Regular expressions to search for in HTML before processing begins (used with $replace_regex)
	public $find_regex = array();
	// Strings to replace those found in $find_regex before HTML processing begins
	public $replace_regex = array();
	
	// the options below cannot be set in the config files which this class represents
	
	//public $cache_in_apc = false; // used to decide if we should cache in apc or not
	public $cache_key = null;
	public static $debug = false;
	protected static $apc = false;
	protected static $config_path;
	protected static $config_path_fallback;
	protected static $config_cache = array();
	const HOSTNAME_REGEX = '/^(([a-zA-Z0-9-]*[a-zA-Z0-9])\.)*([A-Za-z0-9-]*[A-Za-z0-9])$/';
	
	protected static function debug($msg) {
		if (self::$debug) {
			//$mem = round(memory_get_usage()/1024, 2);
			//$memPeak = round(memory_get_peak_usage()/1024, 2);
			echo '* ',$msg;
			//echo ' - mem used: ',$mem," (peak: $memPeak)\n";
			echo "\n";
			ob_flush();
			flush();
		}
	}
	
	// enable APC caching of certain site config files?
	// If enabled the following site config files will be 
	// cached in APC cache (when requested for first time):
	// * anything in site_config/custom/ and its corresponding file in site_config/standard/
	// * the site config files associated with HTML fingerprints
	// * the global site config file
	// returns true if enabled, false otherwise
	public static function use_apc($apc=true) {
		if (!function_exists('apc_add')) {
			if ($apc) self::debug('APC will not be used (function apc_add does not exist)');
			return false;
		}
		self::$apc = $apc;
		return $apc;
	}
	
	// return bool or null
	public function tidy($use_default=true) {
		if ($use_default) return (isset($this->tidy)) ? $this->tidy : $this->default_tidy;
		return $this->tidy;
	}
	
	// return bool or null
	public function prune($use_default=true) {
		if ($use_default) return (isset($this->prune)) ? $this->prune : $this->default_prune;
		return $this->prune;
	}
	
	// return string or null
	public function parser($use_default=true) {
		if ($use_default) return (isset($this->parser)) ? $this->parser : $this->default_parser;
		return $this->parser;
	}

	// return bool or null
	public function autodetect_on_failure($use_default=true) {
		if ($use_default) return (isset($this->autodetect_on_failure)) ? $this->autodetect_on_failure : $this->default_autodetect_on_failure;
		return $this->autodetect_on_failure;
	}
	
	public static function set_config_path($path, $fallback=null) {
		self::$config_path = $path;
		self::$config_path_fallback = $fallback;
	}
	
	public static function add_to_cache($key, SiteConfig $config, $use_apc=true) {
		$key = strtolower($key);
		if (substr($key, 0, 4) == 'www.') $key = substr($key, 4);
		if ($config->cache_key) $key = $config->cache_key;
		$key .= '.'.self::get_key_suffix();		
		self::$config_cache[$key] = $config;
		if (self::$apc && $use_apc) {
			self::debug("Adding site config to APC cache with key sc.$key");
			apc_add("sc.$key", $config);
		}
		self::debug("Cached site config with key $key");
	}
	
	public static function is_cached($key) {
		$key = strtolower($key);
		$key .= '.'.self::get_key_suffix();
		if (substr($key, 0, 4) == 'www.') $key = substr($key, 4);
		if (array_key_exists($key, self::$config_cache)) {
			return true;
		} elseif (self::$apc && (bool)apc_fetch("sc.$key")) {
			return true;
		}
		return false;
	}
	
	public function append(SiteConfig $newconfig) {
		// check for commands where we accept multiple statements (no test_url)
		foreach (array('title', 'body', 'author', 'date', 'strip', 'strip_id_or_class', 'strip_image_src', 'single_page_link', 'single_page_link_in_feed', 'next_page_link', 'native_ad_clue') as $var) {
			// append array elements for this config variable from $newconfig to this config
			//$this->$var = $this->$var + $newconfig->$var;
			$this->$var = array_unique(array_merge($this->$var, $newconfig->$var));
		}
		// special handling of commands where key is important and config values being appended should not overwrite existing ones
		foreach (array('http_header') as $var) {
			$this->$var = array_merge($newconfig->$var, $this->$var);
		}
		// special handling of if_page_contains directive
		foreach (array('single_page_link') as $var) {
			if (isset($this->if_page_contains[$var]) && isset($newconfig->if_page_contains[$var])) {
				$this->if_page_contains[$var] = array_merge($newconfig->if_page_contains[$var], $this->if_page_contains[$var]);
			} elseif (isset($newconfig->if_page_contains[$var])) {
				$this->if_page_contains[$var] = $newconfig->if_page_contains[$var];
			}
		}
		// check for single statement commands
		// we do not overwrite existing non null values
		foreach (array('tidy', 'prune', 'parser', 'autodetect_on_failure') as $var) {
			if ($this->$var === null) $this->$var = $newconfig->$var;
		}
		// treat find_string and replace_string separately (don't apply array_unique) (thanks fabrizio!)
		foreach (array('find_string', 'replace_string', 'find_regex', 'replace_regex') as $var) {
			// append array elements for this config variable from $newconfig to this config
			//$this->$var = $this->$var + $newconfig->$var;
			$this->$var = array_merge($this->$var, $newconfig->$var);
		}
	}
	
	// This is used to make sure that when a different primary folder is chosen
	// The key for the cached result includes that folder choice.
	// Otherwise, a subsequent request choosing a different folder
	// could return the wrong cached config.
	public static function get_key_suffix() {
		$key_suffix = basename(self::$config_path);
		if ($key_suffix === 'custom') $key_suffix = '';
		return $key_suffix;
	}

	// Add test_contains to last test_url
	public function add_test_contains($test_contains) {
		if (!empty($this->test_url)) {
			$test_contains = (string) $test_contains;
			$key = end($this->test_url);
			reset($this->test_url);
			if (isset($this->test_contains[$key])) {
				$this->test_contains[$key][] = $test_contains;
			} else {
				$this->test_contains[$key] = array($test_contains);
			}
		}
	}

	// Add if_page_page_contains
	// TODO: Expand so it can be used with other rules too
	public function add_if_page_contains_condition($if_page_contains) {
		if (!empty($this->single_page_link)) {
			$if_page_contains = (string) $if_page_contains;
			$key = end($this->single_page_link);
			reset($this->single_page_link);
			$this->if_page_contains['single_page_link'][$key] = $if_page_contains;
		}
	}

	public function get_if_page_contains_condition($directive_name, $directive_value) {
		if (isset($this->if_page_contains[$directive_name])) {
			if (isset($this->if_page_contains[$directive_name][$directive_value])) {
				return $this->if_page_contains[$directive_name][$directive_value];
			}
		}
		return null;
	}

	// returns SiteConfig instance if an appropriate one is found, false otherwise
	// if $exact_host_match is true, we will not look for wildcard config matches
	// by default if host is 'test.example.org' we will look for and load '.example.org.txt' if it exists
	public static function build($host, $exact_host_match=false) {
		$host = strtolower($host);
		if (substr($host, 0, 4) == 'www.') $host = substr($host, 4);
		if (!$host || (strlen($host) > 200) || !preg_match(self::HOSTNAME_REGEX, ltrim($host, '.'))) return false;
		// check for site configuration
		$try = array($host);
		// should we look for wildcard matches 
		if (!$exact_host_match) {
			$split = explode('.', $host);
			if (count($split) > 1) {
				array_shift($split);
				$try[] = '.'.implode('.', $split);
			}
		}
		
		// Which primary folder should we look inside?
		// If it's not the default ('custom'), we need
		// a key suffix to distinguish site config fules
		// held in this folder from those in other folders.
		$key_suffix = self::get_key_suffix();

		// look for site config file in primary folder
		self::debug(". looking for site config for $host in primary folder");
		foreach ($try as $h) {
			$h_key = "$h.$key_suffix";
			if (array_key_exists($h_key, self::$config_cache)) {
				self::debug("... site config for $h already loaded in this request");
				return self::$config_cache[$h_key];
			} elseif (self::$apc && ($sconfig = apc_fetch("sc.$h_key"))) {
				self::debug("... site config for $h in APC cache");
				return $sconfig;
			} elseif (file_exists(self::$config_path."/$h.txt")) {
				self::debug("... found site config ($h.txt)");
				$file_primary = self::$config_path."/$h.txt";
				$matched_name = $h;
				break;
			}
		}
		
		// if we found site config, process it
		if (isset($file_primary)) {
			$config_lines = file($file_primary, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if (!$config_lines || !is_array($config_lines)) return false;
			$config = self::build_from_array($config_lines);
			// if APC caching is available and enabled, mark this for cache
			//$config->cache_in_apc = true;
			$config->cache_key = $matched_name;
			
			// if autodetec on failure is off (on by default) we do not need to look
			// in secondary folder
			if (!$config->autodetect_on_failure()) {
				self::debug('... autodetect on failure is disabled (no other site config files will be loaded)');
				return $config;
			}
		}
		
		// look for site config file in secondary folder
		if (isset(self::$config_path_fallback)) {
			self::debug(". looking for site config for $host in secondary folder");
			foreach ($try as $h) {
				if (file_exists(self::$config_path_fallback."/$h.txt")) {
					self::debug("... found site config in secondary folder ($h.txt)");
					$file_secondary = self::$config_path_fallback."/$h.txt";
					$matched_name = $h;
					break;
				}
			}
			if (!isset($file_secondary)) {
				self::debug("... no site config match in secondary folder");
			}
		}
		
		// return false if no config file found
		if (!isset($file_primary) && !isset($file_secondary)) {
			self::debug("... no site config match for $host");
			return false;
		}
		
		// return primary config if secondary not found
		if (!isset($file_secondary) && isset($config)) {
			return $config;
		}
		
		// process secondary config file
		$config_lines = file($file_secondary, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (!$config_lines || !is_array($config_lines)) {
			// failed to process secondary
			if (isset($config)) {
				// return primary config
				return $config;
			} else {
				return false;
			}
		}
		
		// merge with primary and return
		if (isset($config)) {
			self::debug('. merging config files');
			$config->append(self::build_from_array($config_lines));
			return $config;
		} else {
			// return just secondary
			$config = self::build_from_array($config_lines);
			// if APC caching is available and enabled, mark this for cache
			//$config->cache_in_apc = true;
			$config->cache_key = $matched_name;
			return $config;
		}
	}
	
	public static function build_from_string($string) {
		$config_lines = explode("\n", $string);
		return self::build_from_array($config_lines);
	}

	public static function build_from_array(array $lines) {
		$config = new SiteConfig();
		foreach ($lines as $line) {
			$line = trim($line);
			
			// skip comments, empty lines
			if ($line == '' || $line[0] == '#') continue;
			
			// get command
			$command = explode(':', $line, 2);
			// if there's no colon ':', skip this line
			if (count($command) != 2) continue;
			$val = trim($command[1]);
			$command = trim($command[0]);
			if ($command == '' || $val == '') continue;
			
			// check for commands where we accept multiple statements
			if (in_array($command, array('title', 'body', 'author', 'date', 'strip', 'strip_id_or_class', 'strip_image_src', 'single_page_link', 'single_page_link_in_feed', 'next_page_link', 'native_ad_clue', 'http_header', 'test_url', 'find_string', 'replace_string', 'find_regex', 'replace_regex'))) {
				array_push($config->$command, $val);
			// check for single statement commands that evaluate to true or false
			} elseif (in_array($command, array('tidy', 'prune', 'autodetect_on_failure'))) {
				$config->$command = ($val == 'yes');
			// check for single statement commands stored as strings
			} elseif (in_array($command, array('parser'))) {
				$config->$command = $val;
			// special treatment for test_contains
			} elseif (in_array($command, array('test_contains'))) {
				$config->add_test_contains($val);
			// special treatment for if_page_contains
			} elseif (in_array($command, array('if_page_contains'))) {
				$config->add_if_page_contains_condition($val);
			// check for replace_string(find): replace
			} elseif ((substr($command, -1) == ')') && preg_match('!^([a-z0-9_]+)\((.*?)\)$!i', $command, $match)) {
				if (in_array($match[1], array('replace_string'))) {
					array_push($config->find_string, str_replace('&colon;', ':', $match[2]));
					array_push($config->replace_string, str_replace('&colon;', ':', $val));
				} elseif (in_array($match[1], array('replace_regex'))) {
					array_push($config->find_regex, '/' . str_replace('&colon;', ':', $match[2]) . '/');
					array_push($config->replace_regex, str_replace('&colon;', ':', $val));
				} elseif (in_array($match[1], array('http_header'))) {
					$_header = strtolower(trim($match[2]));
					$config->http_header[$_header] = $val;
				}
			}
		}
		return $config;
	}
}