Full-Text RSS
=============

### NOTE

This is a our public version of Full-Text RSS available to download for free from <http://code.fivefilters.org>.

To sustain the project we sell copies of the most up-to-date version at <http://fivefilters.org/content-only/#download> - so if you like this, please consider supporting us by purchasing the latest release. We also accept donations via [Gittip](https://www.gittip.com/fivefilters/).

### About

See <http://fivefilters.org/content-only/> for a description of the code.

### Installation

1. Extract the files in this ZIP archive to a folder on your computer.

2. FTP the files up to your server

3. Access index.php through your browser. E.g. http://example.org/full-text-rss/index.php

4. Enter a URL in the form field to test the code

5. If you get an RSS feed with full-text content, all is working well. :)

### Configuration (optional)

1. Save a copy of config.php as custom_config.php and edit custom_config.php

2. If you decide to enable caching, make sure the cache folder (and its 2 sub folders) is writable. (You might need to change the permissions of these folders to 777 through your FTP client.)

### Code example

	<?php
	// $ftr should be URL where you installed this application
	$ftr = 'http://example.org/full-text-rss/';
	$article = 'http://www.bbc.co.uk/news/world-europe-21936308';

	$request = $ftr.'makefulltextfeed.php?format=json&url='.urlencode($article);

	// Send HTTP request and get response
	$result = @file_get_contents($request);

	if (!$result) die('Failed to fetch content');

	$json = @json_decode($result);

	if (!$json) die('Failed to parse JSON');

	// What do we have?
	// var_dump($json);
	
	// Items?
	// var_dump($json->rss->channel->item);

	$title = $json->rss->channel->item->title;
	// Note: this works when you're processing an article.
	// If the input URL is a feed, ->item will be an array.

	echo $title;