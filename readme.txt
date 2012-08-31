RSS & Atom Feeds for PHP (c) David Grudl, 2008 (http://davidgrudl.com)


Introduction
------------

RSS & Atom Feeds for PHP is a very small and easy-to-use library for consuming an RSS and Atom feed


Project at GitHub: https://github.com/dg/rss-php
My PHP blog: http://phpfashion.com


Requirements
------------
- PHP (version 5 or better)
- enabled directive allow_url_fopen or cURL extension


Usage
-----

Download RSS feed from URL:

	$rss = Feed::loadRss($url);

The returned properties are SimpleXMLElement objects. Extracting
the information from the channel is easy:

	echo 'Title: ', $rss->title;
	echo 'Description: ', $rss->description;
	echo 'Link: ', $rss->link;

	foreach ($rss->item as $item) {
		echo 'Title: ', $item->title;
		echo 'Link: ', $item->link;
		echo 'Timestamp: ', $item->timestamp;
		echo 'Description ', $item->description;
		echo 'HTML encoded content: ', $item->{'content:encoded'};
	}

Download Atom feed from URL:

	$atom = Feed::loadAtom($url);



Files
-----
readme.txt        - This file.
license.txt       - The license for this software (New BSD License).
feed.class.php    - The core RSS feed class source.
load-rss.php      - Example loading RSS feed.
load-atom.php     - Example loading Atom feed.
